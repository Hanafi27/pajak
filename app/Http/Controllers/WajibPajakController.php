<?php

namespace App\Http\Controllers;

use App\Models\WajibPajak;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WajibPajakController extends Controller
{
    public function index(Request $request): View|\Illuminate\Http\JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = 10;

        $query = WajibPajak::query()->orderByDesc('id_wp');
        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('nama_wp', 'like', "%{$search}%")
                    ->orWhere('no_ktp', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%");
            });
        }
        $paginator = $query->paginate($perPage)->withQueryString();

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

        return view('wajib-pajak.index', [
            'items' => $paginator,
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_wp' => ['required', 'string', 'max:100'],
            'alamat' => ['required', 'string'],
            'no_ktp' => ['required', 'regex:/^\d{1,16}$/', 'unique:wajib_pajak,no_ktp'],
        ]);

        WajibPajak::create($validated);

        return back()->with('success', 'Data wajib pajak berhasil ditambahkan.');
    }

    public function update(Request $request, int $id_wp): RedirectResponse
    {
        $wajibPajak = WajibPajak::findOrFail($id_wp);

        $validated = $request->validate([
            'nama_wp' => ['required', 'string', 'max:100'],
            'alamat' => ['required', 'string'],
            'no_ktp' => ['required', 'regex:/^\d{1,16}$/', 'unique:wajib_pajak,no_ktp,' . $wajibPajak->id_wp . ',id_wp'],
        ]);

        $wajibPajak->update($validated);

        return back()->with('success', 'Data wajib pajak berhasil diubah.');
    }

    public function destroy(int $id_wp): RedirectResponse
    {
        $wajibPajak = WajibPajak::findOrFail($id_wp);
        $wajibPajak->delete();

        return back()->with('success', 'Data wajib pajak berhasil dihapus.');
    }
}
