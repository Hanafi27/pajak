<?php

namespace App\Http\Controllers;

use App\Models\ObjekPajak;
use App\Models\Pbb;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
