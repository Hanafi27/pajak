<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Pbb;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    public function index(Request $request): View|\Illuminate\Http\JsonResponse
    {
        $search = trim((string) $request->query('search', ''));
        $perPage = 10;
        $pk = $this->laporanPkColumn();

        $this->syncLaporanFromPbb();

        $summaryQuery = Laporan::query()->orderByDesc('periode');
        if ($search !== '') {
            $summaryQuery->where('periode', 'like', "%{$search}%");
        }
        $summary = $summaryQuery->get([$pk, 'periode', 'total_penerimaan']);
        $summary->transform(function (Laporan $row) use ($pk): Laporan {
            $row->setAttribute('id_laporan', $row->{$pk});
            return $row;
        });

        $detailQuery = Pbb::query()
            ->with(['objekPajak' => function ($q): void {
                $q->select(['id', 'id_wp', 'nop'])->with('wajibPajak:id_wp,nama_wp');
            }])
            ->orderByDesc('tahun')
            ->orderByDesc('id');

        if ($search !== '') {
            $detailQuery->where(function ($q) use ($search): void {
                $q->where('tahun', 'like', "%{$search}%")
                    ->orWhereHas('objekPajak', function ($o) use ($search): void {
                        $o->where('nop', 'like', "%{$search}%")
                            ->orWhereHas('wajibPajak', function ($wp) use ($search): void {
                                $wp->where('nama_wp', 'like', "%{$search}%");
                            });
                    });
            });
        }
        $detail = $detailQuery->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'summary' => $summary->values()->all(),
                'data' => $detail->getCollection()->values()->all(),
                'meta' => [
                    'current_page' => $detail->currentPage(),
                    'last_page' => $detail->lastPage(),
                    'total' => $detail->total(),
                    'per_page' => $detail->perPage(),
                ],
            ]);
        }

        return view('laporan.index', [
            'laporan' => $summary,
            'detail' => $detail,
            'search' => $search,
        ]);
    }

    public function print(Request $request): View
    {
        $periode = trim((string) $request->query('periode', ''));
        $pk = $this->laporanPkColumn();
        $this->syncLaporanFromPbb();

        $query = Laporan::query()->orderByDesc('periode');
        if ($periode !== '') {
            $query->where('periode', $periode);
        }
        $laporan = $query->get([$pk, 'periode', 'total_penerimaan']);
        $laporan->transform(function (Laporan $row) use ($pk): Laporan {
            $row->setAttribute('id_laporan', $row->{$pk});
            return $row;
        });

        return view('laporan.print', ['laporan' => $laporan, 'periode' => $periode]);
    }

    public function exportPdf(Request $request): Response|StreamedResponse
    {
        $search = trim((string) $request->query('search', ''));
        $rows = $this->buildExportRows($search);
        $pdf = Pdf::loadView('laporan.export-pdf', [
            'rows' => $rows,
            'search' => $search,
            'printedAt' => now()->format('d-m-Y H:i:s'),
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('laporan-pbb.pdf');
    }

    public function exportExcel(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));
        $rows = $this->buildExportRows($search);
        $sheetData = [
            ['Periode', 'NOP', 'Wajib Pajak', 'NJOP (Rp)', 'Tarif (%)', 'Total Pajak (Rp)', 'Total Penerimaan Periode (Rp)'],
        ];
        foreach ($rows as $r) {
            $sheetData[] = [
                $r['periode'],
                $r['nop'],
                $r['nama_wp'],
                (float) $r['njop'],
                (float) $r['tarif_persen'],
                (float) $r['total_pajak'],
                (float) $r['total_penerimaan_periode'],
            ];
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan PBB');
        $sheet->fromArray($sheetData, null, 'A1');
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FFE6F4F1');
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle("A1:G{$highestRow}")->getBorders()->getAllBorders()->setBorderStyle('thin');
        $sheet->getStyle("D2:D{$highestRow}")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("E2:E{$highestRow}")->getNumberFormat()->setFormatCode('0');
        $sheet->getStyle("F2:G{$highestRow}")->getNumberFormat()->setFormatCode('#,##0');
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'laporan-pbb.xlsx';
        return response()->streamDownload(function () use ($writer): void {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function syncLaporanFromPbb(): void
    {
        $groups = Pbb::query()
            ->selectRaw('CAST(tahun AS CHAR) as periode, SUM(total_pajak) as total_penerimaan')
            ->groupBy('tahun')
            ->get();

        foreach ($groups as $row) {
            $periode = (string) $row->periode;
            $payload = [
                'total_penerimaan' => (float) $row->total_penerimaan,
                'updated_at' => now(),
            ];

            $exists = DB::table('laporan')->where('periode', $periode)->exists();
            if ($exists) {
                DB::table('laporan')->where('periode', $periode)->update($payload);
            } else {
                DB::table('laporan')->insert($payload + [
                    'periode' => $periode,
                    'created_at' => now(),
                ]);
            }
        }
    }

    private function laporanPkColumn(): string
    {
        return Schema::hasColumn('laporan', 'id_laporan') ? 'id_laporan' : 'id';
    }

    private function buildExportRows(string $search): \Illuminate\Support\Collection
    {
        $this->syncLaporanFromPbb();
        $summaryMap = Laporan::query()->pluck('total_penerimaan', 'periode');

        $query = Pbb::query()
            ->with(['objekPajak' => function ($q): void {
                $q->select(['id', 'id_wp', 'nop'])->with('wajibPajak:id_wp,nama_wp');
            }])
            ->orderByDesc('tahun')
            ->orderByDesc('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('tahun', 'like', "%{$search}%")
                    ->orWhereHas('objekPajak', function ($o) use ($search): void {
                        $o->where('nop', 'like', "%{$search}%")
                            ->orWhereHas('wajibPajak', function ($wp) use ($search): void {
                                $wp->where('nama_wp', 'like', "%{$search}%");
                            });
                    });
            });
        }

        return $query->get()->map(function (Pbb $pbb) use ($summaryMap): array {
            $periode = (string) $pbb->tahun;
            return [
                'periode' => $periode,
                'nop' => $pbb->objekPajak->nop ?? '-',
                'nama_wp' => $pbb->objekPajak->wajibPajak->nama_wp ?? '-',
                'njop' => (float) $pbb->njop,
                'tarif_persen' => (float) $pbb->tarif * 100,
                'total_pajak' => (float) $pbb->total_pajak,
                'total_penerimaan_periode' => (float) ($summaryMap[$periode] ?? 0),
            ];
        });
    }
}
