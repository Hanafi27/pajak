<!doctype html>
<html lang="id"><head><meta charset="utf-8"><title>Cetak Laporan PBB</title>
<style>body{font-family:Arial,sans-serif;color:#1e3034}h2{margin:0 0 10px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #cfd9dc;padding:8px;text-align:left}th{background:#eef5f6}</style>
</head><body onload="window.print()">
<h2>Laporan PBB Periode {{ $periode !== '' ? $periode : 'Semua' }}</h2>
<table><thead><tr><th>Periode</th><th>Total Penerimaan</th></tr></thead><tbody>
@forelse($laporan as $l)
<tr><td>{{ $l->periode }}</td><td>Rp {{ number_format($l->total_penerimaan,0,',','.') }}</td></tr>
@empty
<tr><td colspan="2">Data kosong</td></tr>
@endforelse
</tbody></table>
</body></html>
