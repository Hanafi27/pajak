<?php

namespace App\Http\Controllers;

use App\Models\ObjekPajak;
use App\Models\WajibPajak;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ObjekPajakController extends Controller
{
    public function index(Request $request): View|\Illuminate\Http\JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = 10;
        $pk = $this->primaryKeyColumn();

        $wajibPajak = WajibPajak::query()->orderBy('nama_wp')->get(['id_wp', 'nama_wp', 'alamat']);

        $query = ObjekPajak::query()->with('wajibPajak:id_wp,nama_wp')->orderByDesc($pk);
        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('nop', 'like', "%{$search}%")
                    ->orWhere('lokasi', 'like', "%{$search}%")
                    ->orWhereHas('wajibPajak', function ($wp) use ($search): void {
                        $wp->where('nama_wp', 'like', "%{$search}%");
                    });
            });
        }
        $paginator = $query->paginate($perPage)->withQueryString();
        $paginator->getCollection()->transform(function (ObjekPajak $row) use ($pk) {
            $row->setAttribute('id_objek', $row->{$pk});
            return $row;
        });

        if ($request->ajax()) {
            return response()->json([
                'data' => $paginator->getCollection()->values()->all(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                ],
            ]);
        }

        return view('objek-pajak.index', [
            'items' => $paginator,
            'wajibPajak' => $wajibPajak,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_wp' => ['required', 'integer', 'exists:wajib_pajak,id_wp'],
            'nop' => ['required', 'regex:/^\d{18}$/', 'unique:objek_pajak,nop'],
            'luas_tanah' => ['required', 'numeric', 'min:0'],
            'luas_bangunan' => ['required', 'numeric', 'min:0'],
        ]);

        $validated['lokasi'] = $this->buildLokasiFromWp((int) $validated['id_wp']);
        ObjekPajak::create($validated);

        return redirect()->route('objek-pajak.index')->with('success', 'Data objek pajak berhasil ditambahkan.');
    }

    public function update(Request $request, int $id_objek): RedirectResponse
    {
        $pk = $this->primaryKeyColumn();
        $objekPajak = ObjekPajak::query()->where($pk, $id_objek)->firstOrFail();
        $validated = $request->validate([
            'id_wp' => ['required', 'integer', 'exists:wajib_pajak,id_wp'],
            'nop' => ['required', 'regex:/^\d{18}$/', 'unique:objek_pajak,nop,' . $objekPajak->{$pk} . ',' . $pk],
            'luas_tanah' => ['required', 'numeric', 'min:0'],
            'luas_bangunan' => ['required', 'numeric', 'min:0'],
        ]);
        $validated['lokasi'] = $this->buildLokasiFromWp((int) $validated['id_wp']);
        $objekPajak->update($validated);

        return redirect()->route('objek-pajak.index')->with('success', 'Data objek pajak berhasil diubah.');
    }

    public function destroy(int $id_objek): RedirectResponse
    {
        $pk = $this->primaryKeyColumn();
        $objekPajak = ObjekPajak::query()->where($pk, $id_objek)->firstOrFail();
        $objekPajak->delete();

        return redirect()->route('objek-pajak.index')->with('success', 'Data objek pajak berhasil dihapus.');
    }

    private function buildLokasiFromWp(int $idWp): string
    {
        $wp = WajibPajak::query()->findOrFail($idWp);
        return (string) $wp->alamat;
    }

    private function primaryKeyColumn(): string
    {
        return Schema::hasColumn('objek_pajak', 'id_objek') ? 'id_objek' : 'id';
    }
}
