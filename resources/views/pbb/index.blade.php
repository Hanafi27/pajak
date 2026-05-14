@extends('layouts.app')
@section('content')
<section class="panel">
  <div class="data-toolbar">
    <h1 class="text-lg font-semibold text-slate-800">Pengolahan PBB</h1>
    <p class="text-xs text-slate-500 mt-1">Rumus: PBB = Tarif × (NJOP - NJOPTKP), NJOPTKP saat ini Rp {{ number_format((float) $njoptkp, 0, ',', '.') }}</p>
    <div class="data-toolbar-row">
      <button class="btn" type="button" onclick="openPbbModal()">+ Tambah</button>
      <input id="searchPbb" class="search-input" placeholder="Cari tahun, NOP, wajib pajak..." value="{{ $search }}">
    </div>
  </div>

  <div class="table-wrap">
    <table>
      <thead><tr><th>No</th><th>NOP</th><th>Wajib Pajak</th><th>NJOP</th><th>Tarif</th><th>Total Pajak</th><th>Tahun</th><th>Aksi</th></tr></thead>
      <tbody id="tbodyPbb">
      @foreach($items as $i)
        <tr>
          <td>{{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}</td>
          <td>{{ $i->objekPajak->nop ?? '-' }}</td>
          <td>{{ $i->objekPajak->wajibPajak->nama_wp ?? '-' }}</td>
          <td>Rp {{ number_format((float) $i->njop, 0, ',', '.') }}</td>
          <td>{{ number_format((float) $i->tarif * 100, 0, ',', '.') }}%</td>
          <td>Rp {{ number_format((float) $i->total_pajak, 0, ',', '.') }}</td>
          <td>{{ $i->tahun }}</td>
          <td class="flex gap-2">
            <button class="btn" type="button" onclick='editPbb(@json($i))'>Ubah</button>
            <form method="post" action="{{ route('pbb.destroy', $i->id_pbb) }}" onsubmit="return confirm('Yakin ingin menghapus data PBB ini?')">
              @csrf @method('DELETE')
              <button class="btn danger">Hapus</button>
            </form>
          </td>
        </tr>
      @endforeach
      </tbody>
    </table>
  </div>
  <div id="paginationPbb" class="mt-3 flex justify-center gap-2"></div>
</section>

<div id="modalPbb" class="modal-backdrop hidden">
  <div class="modal-card">
    <div class="modal-head">
      <h3 id="modalPbbTitle" class="text-lg font-semibold">Tambah Pengolahan PBB</h3>
      <button class="modal-close" onclick="closeModal('modalPbb')">&times;</button>
    </div>
    <form id="formPbb" method="post" action="{{ route('pbb.store') }}" class="form-grid">@csrf
      <input type="hidden" id="methodPbb" name="_method" value="POST">
      <div class="field span-2">
        <label>Objek Pajak</label>
        <select id="idObjekPbb" name="id_objek" required>
          <option value="">Pilih Objek Pajak</option>
          @foreach($objekPajak as $o)
          <option value="{{ $o->id }}">{{ $o->nop }} - {{ $o->wajibPajak->nama_wp ?? '-' }}</option>
          @endforeach
        </select>
      </div>
      <div class="field">
        <label>NJOP (Rp)</label>
        <input id="njopPbb" type="text" name="njop" inputmode="numeric" placeholder="Contoh: 250.000.000" required>
      </div>
      <div class="field">
        <label>Tarif Pajak (%)</label>
        <input id="tarifPbb" type="text" name="tarif" inputmode="decimal" placeholder="Contoh: 5 atau 1" required>
      </div>
      <div class="field">
        <label>Tahun</label>
        <input id="tahunPbb" type="number" min="2000" max="2100" name="tahun" required>
      </div>
      <div class="field">
        <label>Total Pajak (Otomatis)</label>
        <input id="totalPbbPreview" type="text" readonly placeholder="Akan dihitung otomatis">
      </div>
      <div class="actions"><button class="btn" type="submit">Simpan</button></div>
    </form>
  </div>
</div>

<script>
const njoptkp = {{ (float) $njoptkp }};
const tbodyPbb=document.getElementById('tbodyPbb');
const pagPbb=document.getElementById('paginationPbb');
const searchPbb=document.getElementById('searchPbb');
const formPbb=document.getElementById('formPbb');
const methodPbb=document.getElementById('methodPbb');
const modalPbbTitle=document.getElementById('modalPbbTitle');
const idObjekPbb=document.getElementById('idObjekPbb');
const njopPbb=document.getElementById('njopPbb');
const tarifPbb=document.getElementById('tarifPbb');
const tahunPbb=document.getElementById('tahunPbb');
const totalPbbPreview=document.getElementById('totalPbbPreview');

function openModal(id){const m=document.getElementById(id);m.classList.remove('hidden');m.classList.add('flex')}
function closeModal(id){const m=document.getElementById(id);m.classList.add('hidden');m.classList.remove('flex')}

function hitungPajak(njop, tarif){
  const dasar = Math.max(0, (Number(njop) || 0) - njoptkp);
  return dasar * (Number(tarif) || 0);
}

function toDigits(value){
  return (String(value || '').replace(/[^\d]/g, ''));
}

function formatRupiahInput(value){
  const digits = toDigits(value);
  if (!digits) return '';
  return Number(digits).toLocaleString('id-ID');
}

function parseRupiahInput(value){
  const digits = toDigits(value);
  return digits ? Number(digits) : 0;
}

function parseTarifInput(value){
  const digits = String(value || '').replace(/[^\d]/g, '');
  const percent = Number(digits || 0);
  const clamped = Math.min(5, Math.max(0, percent));
  return clamped / 100;
}

function formatTarifInput(value){
  const percent = Math.round(parseTarifInput(value) * 100);
  return `${percent}%`;
}

function syncTotalPbbPreview(){
  const total = hitungPajak(parseRupiahInput(njopPbb.value), parseTarifInput(tarifPbb.value));
  totalPbbPreview.value = `Rp ${Number(total).toLocaleString('id-ID', { maximumFractionDigits: 0 })}`;
}

function resetPbbForm(){
  formPbb.action = "{{ route('pbb.store') }}";
  methodPbb.value = 'POST';
  modalPbbTitle.textContent = 'Tambah Pengolahan PBB';
  idObjekPbb.value = '';
  njopPbb.value = '';
  tarifPbb.value = '';
  tahunPbb.value = new Date().getFullYear();
  syncTotalPbbPreview();
}

function openPbbModal(){ resetPbbForm(); openModal('modalPbb'); }

function editPbb(row){
  formPbb.action = `{{ url('/pbb') }}/${row.id_pbb ?? row.id}`;
  methodPbb.value = 'PUT';
  modalPbbTitle.textContent = 'Ubah Pengolahan PBB';
  idObjekPbb.value = String(row.id_objek);
  njopPbb.value = formatRupiahInput(row.njop);
  tarifPbb.value = formatTarifInput(row.tarif);
  tahunPbb.value = row.tahun;
  syncTotalPbbPreview();
  openModal('modalPbb');
}
function editPbbFromEncoded(encoded){ editPbb(JSON.parse(decodeURIComponent(encoded))); }

async function loadPbb(page=1){
  const q=encodeURIComponent(searchPbb.value||'');
  const res=await fetch(`{{ route('pbb.index') }}?search=${q}&page=${page}`,{headers:{'X-Requested-With':'XMLHttpRequest'}});
  const j=await res.json();
  tbodyPbb.innerHTML=j.data.length?j.data.map((r,idx)=>{
    const encoded = encodeURIComponent(JSON.stringify(r));
    const nop = r.objek_pajak?.nop ?? r.objekPajak?.nop ?? '-';
    const namaWp = r.objek_pajak?.wajib_pajak?.nama_wp ?? r.objekPajak?.wajibPajak?.nama_wp ?? '-';
    return `<tr><td>${((j.meta.current_page - 1) * j.meta.per_page) + (idx + 1)}</td><td>${nop}</td><td>${namaWp}</td><td>Rp ${Number(r.njop||0).toLocaleString('id-ID')}</td><td>${Math.round((Number(r.tarif||0)*100)).toLocaleString('id-ID')}%</td><td>Rp ${Number(r.total_pajak||0).toLocaleString('id-ID')}</td><td>${r.tahun}</td><td class='flex gap-2'><button class='btn' type='button' onclick='editPbbFromEncoded("${encoded}")'>Ubah</button><form method='post' action='{{ url('/pbb') }}/${r.id_pbb ?? r.id}' onsubmit='return confirm("Yakin ingin menghapus data PBB ini?")'><input type='hidden' name='_token' value='{{ csrf_token() }}'><input type='hidden' name='_method' value='DELETE'><button class='btn danger'>Hapus</button></form></td></tr>`;
  }).join(''):`<tr><td colspan='8' class='empty'>Data kosong</td></tr>`;
  pagPbb.innerHTML='';
  for(let i=1;i<=j.meta.last_page;i++){pagPbb.innerHTML+=`<button class='logout-btn ${i===j.meta.current_page?'bg-teal-50 text-teal-800':''}' onclick='loadPbb(${i})'>${i}</button>`}
}

let tPbb;
searchPbb.addEventListener('input',()=>{clearTimeout(tPbb);tPbb=setTimeout(()=>loadPbb(1),120)});
njopPbb.addEventListener('input', () => {
  njopPbb.value = formatRupiahInput(njopPbb.value);
  syncTotalPbbPreview();
});
tarifPbb.addEventListener('input', () => {
  let raw = String(tarifPbb.value || '').replace(/[^\d]/g, '');
  if (raw.length > 1) raw = raw.slice(0, 1);
  const percent = Math.min(5, Number(raw || 0));
  tarifPbb.value = raw === '' ? '' : String(percent);
  syncTotalPbbPreview();
});
tarifPbb.addEventListener('blur', () => {
  tarifPbb.value = formatTarifInput(tarifPbb.value);
  syncTotalPbbPreview();
});

formPbb.addEventListener('submit', (event) => {
  syncTotalPbbPreview();
  if (!idObjekPbb.value || !njopPbb.value || !tarifPbb.value || !tahunPbb.value) {
    event.preventDefault();
    if (window.uiDialog?.alert) window.uiDialog.alert('Data tidak lengkap. Mohon lengkapi seluruh field wajib.');
    else alert('Data tidak lengkap. Mohon lengkapi seluruh field wajib.');
    return;
  }
  const tarifNumber = parseTarifInput(tarifPbb.value);
  if (tarifNumber > 0.05) {
    event.preventDefault();
    if (window.uiDialog?.alert) window.uiDialog.alert('Tarif PBB tidak boleh lebih dari 5%.');
    else alert('Tarif PBB tidak boleh lebih dari 5%.');
    return;
  }
  njopPbb.value = String(parseRupiahInput(njopPbb.value));
  tarifPbb.value = String(tarifNumber);
  const isEdit = methodPbb.value === 'PUT';
  if (!window.confirm(isEdit ? 'Yakin ingin menyimpan perubahan data PBB ini?' : 'Yakin ingin memproses dan menyimpan data PBB ini?')) {
    event.preventDefault();
  }
});

resetPbbForm();
loadPbb(1);
</script>
@endsection
