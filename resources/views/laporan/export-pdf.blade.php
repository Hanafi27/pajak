<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <style>
    @page { margin: 20px 24px 34px 24px; }
    body { font-family: DejaVu Sans, Arial, sans-serif; color:#1e293b; font-size: 11px; }
    .banner { background:#0f766e; color:#fff; padding:8px 10px; border-radius:8px 8px 0 0; font-size:11px; }
    .head-wrap { border:1px solid #99f6e4; border-top:none; border-radius:0 0 8px 8px; padding:10px; margin-bottom:10px; background:#f0fdfa; }
    .head { display:flex; align-items:center; gap:12px; }
    .logo { width:52px; height:52px; object-fit:contain; }
    .org { font-size:12px; font-weight:700; letter-spacing:.2px; color:#115e59; }
    h1 { margin:2px 0 0 0; font-size:17px; }
    .meta { margin-top:4px; font-size:10px; color:#475569; }
    .subtitle { margin-top:2px; font-size:10px; color:#64748b; }
    .chip { display:inline-block; background:#ecfeff; border:1px solid #99f6e4; color:#0f766e; padding:2px 7px; border-radius:999px; font-size:10px; margin-right:6px; }
    table { width:100%; border-collapse: collapse; }
    th, td { border:1px solid #cbd5e1; padding:5px; text-align:left; vertical-align:top; }
    th { background:#ccfbf1; font-weight:700; color:#134e4a; }
    .num { text-align:right; }
    .empty { text-align:center; color:#64748b; }
  </style>
</head>
<body>
  <div class="banner">Preview Laporan PBB - Dokumen Resmi Internal Bapenda</div>
  <div class="head-wrap">
    <div class="head">
      <img src="{{ public_path('assets/logo.jpg') }}" class="logo" alt="Logo Bapenda">
      <div>
        <div class="org">BADAN PENDAPATAN DAERAH KABUPATEN BANDUNG</div>
        <h1>Laporan Pajak Bumi dan Bangunan (PBB)</h1>
        <div class="meta"><span class="chip">Filter: {{ $search !== '' ? $search : 'Semua Data' }}</span><span class="chip">Dicetak: {{ $printedAt }}</span></div>
        <div class="subtitle">Rekap penerimaan PBB periode berjalan dan detail objek pajak.</div>
      </div>
    </div>
  </div>

  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Periode</th>
        <th>NOP</th>
        <th>Wajib Pajak</th>
        <th class="num">NJOP (Rp)</th>
        <th class="num">Tarif (%)</th>
        <th class="num">Total Pajak (Rp)</th>
        <th class="num">Total Penerimaan Periode (Rp)</th>
      </tr>
    </thead>
    <tbody>
    @forelse($rows as $idx => $r)
      <tr>
        <td>{{ $idx + 1 }}</td>
        <td>{{ $r['periode'] }}</td>
        <td>{{ $r['nop'] }}</td>
        <td>{{ $r['nama_wp'] }}</td>
        <td class="num">{{ number_format($r['njop'], 0, ',', '.') }}</td>
        <td class="num">{{ number_format($r['tarif_persen'], 0, ',', '.') }}</td>
        <td class="num">{{ number_format($r['total_pajak'], 0, ',', '.') }}</td>
        <td class="num">{{ number_format($r['total_penerimaan_periode'], 0, ',', '.') }}</td>
      </tr>
    @empty
      <tr><td colspan="8" class="empty">Data laporan belum tersedia.</td></tr>
    @endforelse
    </tbody>
  </table>
</body>
</html>
