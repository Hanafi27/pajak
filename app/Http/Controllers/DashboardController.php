<?php

namespace App\Http\Controllers;

use App\Models\ObjekPajak;
use App\Models\Laporan;
use App\Models\Pbb;
use App\Models\WajibPajak;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $role = session('auth_user.role', 'petugas');
        $currentYear = (int) now()->format('Y');

        $summary = [
            'wajib_pajak' => WajibPajak::query()->count(),
            'objek_pajak' => ObjekPajak::query()->count(),
            'total_penerimaan' => (float) Pbb::query()->sum('total_pajak'),
            'total_penerimaan_tahun' => (float) Pbb::query()->where('tahun', $currentYear)->sum('total_pajak'),
        ];

        $monthlyRevenueRaw = Pbb::query()
            ->selectRaw('MONTH(created_at) as bulan, SUM(total_pajak) as total')
            ->where('tahun', $currentYear)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->pluck('total', 'bulan');

        $objectGrowthRaw = ObjekPajak::query()
            ->selectRaw('MONTH(created_at) as bulan, COUNT(*) as total')
            ->whereYear('created_at', $currentYear)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->pluck('total', 'bulan');

        $monthlyLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $monthlyRevenue = [];
        $objectGrowth = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyRevenue[] = round(((float) ($monthlyRevenueRaw[$m] ?? 0)) / 1000000000, 2);
            $objectGrowth[] = (int) ($objectGrowthRaw[$m] ?? 0);
        }

        $totalObjek = (int) $summary['objek_pajak'];
        $objekSudahBayar = (int) Pbb::query()
            ->where('tahun', $currentYear)
            ->distinct('id_objek')
            ->count('id_objek');
        $objekBelumBayar = max(0, $totalObjek - $objekSudahBayar);

        $objekTableKey = Schema::hasColumn('objek_pajak', 'id_objek') ? 'id_objek' : 'id';

        $regionRaw = Pbb::query()
            ->join('objek_pajak', 'objek_pajak.' . $objekTableKey, '=', 'pbb.id_objek')
            ->select('objek_pajak.lokasi', 'pbb.total_pajak')
            ->where('pbb.tahun', $currentYear)
            ->get();

        $regionAggregated = $regionRaw
            ->groupBy(function ($row) {
                $lokasi = (string) ($row->lokasi ?? '');
                if ($lokasi === '') {
                    return 'Wilayah Tidak Diketahui';
                }

                $parts = array_values(array_filter(array_map('trim', explode(',', $lokasi))));
                $last = strtolower((string) end($parts));

                if (str_contains($last, 'kabupaten') || str_contains($last, 'kota')) {
                    return count($parts) >= 2 ? $parts[count($parts) - 2] : $parts[0];
                }

                return $parts[count($parts) - 1] ?? 'Wilayah Tidak Diketahui';
            })
            ->map(fn ($rows) => (float) $rows->sum('total_pajak'))
            ->sortDesc()
            ->take(10);

        $regionLabels = $regionAggregated->keys()->values()->all();
        $regionValues = $regionAggregated->values()
            ->map(fn ($value) => round($value / 1000000000, 2))
            ->all();

        $dashboardData = [
            'monthlyLabels' => $monthlyLabels,
            'monthlyRevenue' => $monthlyRevenue,
            'objectGrowth' => $objectGrowth,
            'paymentStatusLabels' => ['Sudah Bayar', 'Belum Bayar'],
            'paymentStatusValues' => [$objekSudahBayar, $objekBelumBayar],
            'regionLabels' => $regionLabels,
            'regionValues' => $regionValues,
            'role' => $role,
            'currentYear' => $currentYear,
        ];

        return view('dashboard.index', compact('summary', 'dashboardData', 'role'));
    }
}
