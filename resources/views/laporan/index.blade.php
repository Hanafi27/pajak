@extends('layouts.app')

@section('content')
<style>
  .ai-report-panel {
    display: grid;
    gap: 16px;
    margin-bottom: 14px;
  }
  .ai-report-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
  }
  .ai-report-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
    margin-top: 8px;
  }
  .ai-report-filter {
    display: inline-flex;
    align-items: center;
    border: 1px solid #dfdfdf;
    border-radius: 9999px;
    background: #fff;
    padding: 3px 9px;
    color: #212121;
    font-size: 12px;
  }
  .ai-report-status {
    color: #707070;
    font-size: 12px;
  }
  .ai-report-box {
    min-height: 110px;
    white-space: pre-wrap;
    border: 1px solid #ededed;
    border-radius: 8px;
    background: #fafafa;
    padding: 16px;
    color: #212121;
    line-height: 1.55;
  }
  @media (max-width: 760px) {
    .ai-report-head {
      align-items: stretch;
      flex-direction: column;
    }
  }
</style>

<section class="panel ai-report-panel">
  <div class="ai-report-head">
    <div>
      <p class="eyebrow">AI Laporan</p>
      <h3>Ringkasan Narasi Laporan</h3>
      <p class="muted text-sm">Membuat catatan singkat dari data agregat laporan sesuai filter aktif.</p>
      <div class="ai-report-meta">
        <span id="reportAiFilterLabel" class="ai-report-filter">Filter narasi: {{ $search !== '' ? $search : 'Semua Data' }}</span>
        <span id="reportAiStatus" class="ai-report-status">Belum dibuat</span>
      </div>
    </div>
    <button id="generateReportAiBtn" class="btn" type="button">Buat Ringkasan AI</button>
  </div>
  <div id="reportAiBox" class="ai-report-box">Klik tombol untuk membuat narasi laporan yang bisa dipakai sebagai catatan pimpinan.</div>
</section>

<section class="panel">
  <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
    <h1 class="text-lg font-semibold text-slate-800">Laporan PBB</h1>
    <button class="btn" type="button" onclick="openModal('modalLap')">Filter & Cetak</button>
  </div>
  <input id="searchLap" class="mb-3 w-full rounded-xl border border-slate-200 px-3 py-2" placeholder="Cari tahun, NOP, wajib pajak..." value="{{ $search }}">
  <h3>Ringkasan Penerimaan</h3>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Periode</th><th>Total Penerimaan</th></tr></thead>
      <tbody id="tbodyLapSummary">
        @forelse($laporan as $l)
        <tr><td>{{ $l->periode }}</td><td>Rp {{ number_format($l->total_penerimaan,0,',','.') }}</td></tr>
        @empty
        <tr><td colspan="2" class="empty">Data kosong</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</section>

<section class="panel">
  <h3>Detail Data PBB</h3>
  <div class="table-wrap">
    <table>
      <thead><tr><th>Tahun</th><th>Wajib Pajak</th><th>NOP</th><th>Total Pajak</th></tr></thead>
      <tbody id="tbodyLapDetail">
        @forelse($detail as $d)
        <tr><td>{{ $d->tahun }}</td><td>{{ $d->objekPajak->wajibPajak->nama_wp ?? '-' }}</td><td>{{ $d->objekPajak->nop ?? '-' }}</td><td>Rp {{ number_format($d->total_pajak,0,',','.') }}</td></tr>
        @empty
        <tr><td colspan="4" class="empty">Data kosong</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div id="paginationLap" class="mt-3 flex justify-center gap-2"></div>
</section>

<div id="modalLap" class="fixed inset-0 z-40 hidden items-center justify-center bg-slate-900/30 p-4">
  <div class="w-full max-w-xl rounded-2xl bg-white p-5">
    <div class="mb-3 flex items-center justify-between">
      <h3 class="text-lg font-semibold">Filter & Cetak Laporan</h3>
      <button onclick="closeModal('modalLap')">Tutup</button>
    </div>
    <form method="get" action="{{ route('laporan.index') }}" class="form-grid">
      <div class="field"><label>Periode (Tahun)</label><input name="search" value="{{ $search }}"></div>
      <div class="actions" style="justify-content:flex-start;gap:8px">
        <button class="btn" type="submit">Terapkan</button>
        <a id="btnPdfLap" class="btn" href="{{ route('laporan.export.pdf') }}?search={{ $search }}" target="_blank">Preview PDF</a>
        <a id="btnExcelLap" class="btn" href="{{ route('laporan.export.excel') }}?search={{ $search }}">Export Excel (.xlsx)</a>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(id){document.getElementById(id).classList.remove('hidden');document.getElementById(id).classList.add('flex')}
function closeModal(id){document.getElementById(id).classList.add('hidden');document.getElementById(id).classList.remove('flex')}

const sum=document.getElementById('tbodyLapSummary'),det=document.getElementById('tbodyLapDetail'),pag=document.getElementById('paginationLap'),search=document.getElementById('searchLap');
const btnPdfLap=document.getElementById('btnPdfLap');
const btnExcelLap=document.getElementById('btnExcelLap');
const generateReportAiBtn = document.getElementById('generateReportAiBtn');
const reportAiBox = document.getElementById('reportAiBox');
const reportAiFilterLabel = document.getElementById('reportAiFilterLabel');
const reportAiStatus = document.getElementById('reportAiStatus');
let latestReportAiSummary = '';

function currentReportFilter(){
  const value = (search.value || '').trim();
  return value || 'Semua Data';
}

function syncReportAiContext(state = 'Belum dibuat'){
  reportAiFilterLabel.textContent = `Filter narasi: ${currentReportFilter()}`;
  reportAiStatus.textContent = state;
  generateReportAiBtn.textContent = `Buat Ringkasan AI (${currentReportFilter()})`;
}

function syncExportLinks(){
  const q=encodeURIComponent(search.value||'');
  const ai = latestReportAiSummary ? `&ai_summary=${encodeURIComponent(latestReportAiSummary)}` : '';
  btnPdfLap.href=`{{ route('laporan.export.pdf') }}?search=${q}${ai}`;
  btnExcelLap.href=`{{ route('laporan.export.excel') }}?search=${q}`;
}

async function loadLap(page=1){
  const q=encodeURIComponent(search.value||'');
  const res=await fetch(`{{ route('laporan.index') }}?search=${q}&page=${page}`,{headers:{'X-Requested-With':'XMLHttpRequest'}});
  const j=await res.json();
  sum.innerHTML=j.summary.length?j.summary.map(r=>`<tr><td>${r.periode}</td><td>Rp ${Number(r.total_penerimaan).toLocaleString('id-ID')}</td></tr>`).join(''):`<tr><td colspan='2' class='empty'>Data kosong</td></tr>`;
  det.innerHTML=j.data.length?j.data.map(r=>`<tr><td>${r.tahun}</td><td>${r.objek_pajak?.wajib_pajak?.nama_wp??r.objekPajak?.wajibPajak?.nama_wp??'-'}</td><td>${r.objek_pajak?.nop??r.objekPajak?.nop??'-'}</td><td>Rp ${Number(r.total_pajak).toLocaleString('id-ID')}</td></tr>`).join(''):`<tr><td colspan='4' class='empty'>Data kosong</td></tr>`;
  pag.innerHTML='';
  for(let i=1;i<=j.meta.last_page;i++){pag.innerHTML+=`<button class='logout-btn ${i===j.meta.current_page?'pager-active':''}' onclick='loadLap(${i})'>${i}</button>`}
}

generateReportAiBtn?.addEventListener('click', async () => {
  generateReportAiBtn.disabled = true;
  generateReportAiBtn.textContent = 'Menyusun...';
  syncReportAiContext('Sedang dibuat');
  reportAiBox.textContent = `AI sedang menyusun narasi laporan untuk filter ${currentReportFilter()}...`;

  try {
    const res = await fetch('{{ route('laporan.ai-summary') }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({ search: search.value || '' })
    });
    const json = await res.json();

    if (!res.ok) {
      throw new Error(json.detail || json.message || 'Gagal membuat ringkasan AI.');
    }

    latestReportAiSummary = json.summary || '';
    reportAiBox.textContent = latestReportAiSummary || 'Ringkasan kosong.';
    syncReportAiContext('Siap masuk PDF');
    syncExportLinks();
  } catch (err) {
    syncReportAiContext('Gagal dibuat');
    reportAiBox.textContent = `Ringkasan AI belum bisa dibuat. ${err.message}`;
  } finally {
    generateReportAiBtn.disabled = false;
    if (reportAiStatus.textContent !== 'Siap masuk PDF') syncReportAiContext(reportAiStatus.textContent || 'Belum dibuat');
  }
});

let t;
search.addEventListener('input',()=>{latestReportAiSummary='';syncReportAiContext('Filter berubah');reportAiBox.textContent=`Filter berubah ke ${currentReportFilter()}. Klik tombol untuk membuat narasi laporan baru.`;syncExportLinks();clearTimeout(t);t=setTimeout(()=>loadLap(1),300)});
syncReportAiContext();
syncExportLinks();
loadLap(1);
</script>
@endsection
