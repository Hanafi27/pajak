<?php

namespace App\Http\Controllers;

use App\Models\ObjekPajak;
use App\Models\Pbb;
use App\Services\GeminiInsightService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class PbbController extends Controller
{
    private const DEFAULT_NJOPTKP = 10000000;
    private const MAX_TARIF = 0.05; // 5%

    public function index(Request $request): View|\Illuminate\Http\JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = 10;
        $njoptkp = (float) env('PBB_NJOPTKP', self::DEFAULT_NJOPTKP);

        $objekPajak = ObjekPajak::query()
            ->with('wajibPajak:id_wp,nama_wp')
            ->orderByDesc('id')
            ->get(['id', 'id_wp', 'nop', 'lokasi']);

        $query = Pbb::query()
            ->with(['objekPajak' => function ($q): void {
                $q->select(['id', 'id_wp', 'nop', 'lokasi'])->with('wajibPajak:id_wp,nama_wp');
            }])
            ->orderByDesc('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('tahun', 'like', "%{$search}%")
                    ->orWhere('njop', 'like', "%{$search}%")
                    ->orWhereHas('objekPajak', function ($o) use ($search): void {
                        $o->where('nop', 'like', "%{$search}%")
                            ->orWhereHas('wajibPajak', function ($wp) use ($search): void {
                                $wp->where('nama_wp', 'like', "%{$search}%");
                            });
                    });
            });
        }

        $items = $query->paginate($perPage)->withQueryString();
        $items->getCollection()->transform(function (Pbb $row): Pbb {
            $row->setAttribute('id_pbb', $row->id);
            return $row;
        });

        if ($request->ajax()) {
            return response()->json([
                'data' => $items->getCollection()->values()->all(),
                'meta' => [
                    'current_page' => $items->currentPage(),
                    'last_page' => $items->lastPage(),
                    'total' => $items->total(),
                    'per_page' => $items->perPage(),
                ],
            ]);
        }

        return view('pbb.index', [
            'items' => $items,
            'objekPajak' => $objekPajak,
            'search' => $search,
            'njoptkp' => $njoptkp,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'njop' => $this->normalizeRupiah((string) $request->input('njop')),
            'tarif' => $this->normalizeTarif((string) $request->input('tarif')),
        ]);

        $validated = $request->validate([
            'id_objek' => ['required', 'integer', 'exists:objek_pajak,id'],
            'njop' => ['required', 'numeric', 'min:0'],
            'tarif' => ['required', 'numeric', 'min:0', 'max:' . self::MAX_TARIF],
            'tahun' => ['required', 'digits:4', 'integer', 'min:2000', 'max:2100'],
        ]);

        $validated['total_pajak'] = $this->hitungPajak((float) $validated['njop'], (float) $validated['tarif']);
        Pbb::create($validated);

        return redirect()->route('pbb.index')->with('success', 'Data PBB berhasil diproses dan disimpan.');
    }

    public function update(Request $request, int $id_pbb): RedirectResponse
    {
        $request->merge([
            'njop' => $this->normalizeRupiah((string) $request->input('njop')),
            'tarif' => $this->normalizeTarif((string) $request->input('tarif')),
        ]);

        $pbb = Pbb::findOrFail($id_pbb);
        $validated = $request->validate([
            'id_objek' => ['required', 'integer', 'exists:objek_pajak,id'],
            'njop' => ['required', 'numeric', 'min:0'],
            'tarif' => ['required', 'numeric', 'min:0', 'max:' . self::MAX_TARIF],
            'tahun' => ['required', 'digits:4', 'integer', 'min:2000', 'max:2100'],
        ]);
        $validated['total_pajak'] = $this->hitungPajak((float) $validated['njop'], (float) $validated['tarif']);
        $pbb->update($validated);

        return redirect()->route('pbb.index')->with('success', 'Data PBB berhasil diubah.');
    }

    public function aiDraft(int $id_pbb, GeminiInsightService $gemini): JsonResponse
    {
        $pbb = Pbb::query()
            ->with(['objekPajak' => function ($q): void {
                $q->select(['id', 'id_wp', 'nop'])->with('wajibPajak:id_wp,nama_wp');
            }])
            ->findOrFail($id_pbb);

        $payload = [
            'nama_wajib_pajak' => $pbb->objekPajak->wajibPajak->nama_wp ?? 'Wajib Pajak',
            'nop' => $pbb->objekPajak->nop ?? '-',
            'tahun' => (string) $pbb->tahun,
            'njop' => (float) $pbb->njop,
            'tarif_persen' => round((float) $pbb->tarif * 100, 2),
            'total_pajak' => (float) $pbb->total_pajak,
            'catatan' => 'Draft dibuat untuk membantu petugas menyiapkan pengingat administrasi PBB dan perlu diverifikasi sebelum dikirim.',
        ];

        try {
            return response()->json([
                'draft' => $gemini->generateBillingDraft($payload),
                'meta' => [
                    'nama_wajib_pajak' => $payload['nama_wajib_pajak'],
                    'nop' => $payload['nop'],
                    'tahun' => $payload['tahun'],
                    'njop' => 'Rp ' . number_format($payload['njop'], 0, ',', '.'),
                    'tarif_persen' => number_format($payload['tarif_persen'], 0, ',', '.') . '%',
                    'total_pajak' => 'Rp ' . number_format($payload['total_pajak'], 0, ',', '.'),
                    'dibuat_pada' => now()->format('d-m-Y H:i:s'),
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => 'Draft surat AI belum bisa dibuat.',
                'detail' => $e->getMessage(),
            ], 422);
        }
    }

    public function destroy(int $id_pbb): RedirectResponse
    {
        $pbb = Pbb::findOrFail($id_pbb);
        $pbb->delete();
        return redirect()->route('pbb.index')->with('success', 'Data PBB berhasil dihapus.');
    }

    private function hitungPajak(float $njop, float $tarif): float
    {
        $njoptkp = (float) env('PBB_NJOPTKP', self::DEFAULT_NJOPTKP);
        $dasar = max(0, $njop - $njoptkp);
        return $tarif * $dasar;
    }

    private function normalizeRupiah(string $value): float
    {
        $digits = preg_replace('/[^\d]/', '', $value) ?? '0';
        return (float) $digits;
    }

    private function normalizeTarif(string $value): float
    {
        $clean = trim(str_replace('%', '', $value));
        $clean = str_replace(',', '.', preg_replace('/[^0-9,.\-]/', '', $clean) ?? '0');
        $number = (float) $clean;
        if ($number > 1) {
            $number = $number / 100;
        }
        return $number;
    }
}

