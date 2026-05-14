@extends('layouts.app')
@section('content')
<section class="panel">
  <div class="data-toolbar">
    <h1 class="text-lg font-semibold text-slate-800">Data Objek Pajak</h1>
    <div class="data-toolbar-row">
      <button class="btn" type="button" onclick="openObjModal()">+ Tambah</button>
      <input id="searchObj" class="search-input" placeholder="Cari NOP, lokasi, wajib pajak..." value="{{ $search }}">
    </div>
  </div>

  <div class="table-wrap">
    <table>
      <thead><tr><th>No</th><th>Wajib Pajak</th><th>NOP</th><th>Lokasi</th><th>Luas Tanah</th><th>Luas Bangunan</th><th>Aksi</th></tr></thead>
      <tbody id="tbodyObj">
        @foreach($items as $i)
        <tr>
          <td>{{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}</td>
          <td>{{ $i->wajibPajak->nama_wp ?? '-' }}</td>
          <td>{{ $i->nop }}</td>
          <td>{{ $i->lokasi }}</td>
          <td>{{ number_format((float) $i->luas_tanah, 0, ',', '.') }} m²</td>
          <td>{{ number_format((float) $i->luas_bangunan, 0, ',', '.') }} m²</td>
          <td class="flex gap-2">
            <button class="btn" type="button" onclick='editObj(@json($i))'>Ubah</button>
            <form method="post" action="{{ route('objek-pajak.destroy',$i->id_objek) }}" onsubmit="return confirm('Yakin ingin menghapus data objek pajak ini?')">
              @csrf @method('DELETE')<button class="btn danger">Hapus</button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div id="paginationObj" class="mt-3 flex justify-center gap-2"></div>
</section>

<div id="modalObj" class="modal-backdrop hidden">
  <div class="modal-card">
    <div class="modal-head"><h3 id="modalObjTitle" class="text-lg font-semibold">Tambah Objek Pajak</h3><button class="modal-close" onclick="closeModal('modalObj')">&times;</button></div>

    <form id="formObj" method="post" action="{{ route('objek-pajak.store') }}" class="form-grid">@csrf
      <input type="hidden" id="methodObj" name="_method" value="POST">
      <div class="field"><label>Wajib Pajak</label><select id="idWpObj" name="id_wp" required><option value="">Pilih</option>@foreach($wajibPajak as $w)<option value="{{ $w->id_wp }}" data-lokasi="{{ $w->alamat }}">{{ $w->nama_wp }}</option>@endforeach</select></div>
      <div class="field"><label>NOP (18 digit)</label><input id="nopObj" name="nop" required inputmode="numeric" maxlength="18" pattern="\d{18}"></div>
      <input type="hidden" id="lokasiObj" name="lokasi">
      <div class="field span-2"><label>Lokasi (Otomatis)</label><input id="lokasiObjPreview" type="text" readonly placeholder="Akan terisi dari nama wajib pajak"></div>
      <div class="field"><label>Luas Tanah (m²)</label><input id="luasTanahObj" type="number" step="0.01" name="luas_tanah" required></div>
      <div class="field"><label>Luas Bangunan (m²)</label><input id="luasBangunanObj" type="number" step="0.01" name="luas_bangunan" required></div>
      <div class="actions"><button class="btn" type="submit">Simpan</button></div>
    </form>
  </div>
</div>

<script>
function openModal(id){const m=document.getElementById(id);m.classList.remove('hidden');m.classList.add('flex')}
function closeModal(id){const m=document.getElementById(id);m.classList.add('hidden');m.classList.remove('flex')}

const tbodyObj=document.getElementById('tbodyObj');
const pagObj=document.getElementById('paginationObj');
const searchObj=document.getElementById('searchObj');
const formObj=document.getElementById('formObj');
const methodObj=document.getElementById('methodObj');
const modalObjTitle=document.getElementById('modalObjTitle');
const idWpObj=document.getElementById('idWpObj');
const nopObj=document.getElementById('nopObj');
const lokasiObj=document.getElementById('lokasiObj');
const lokasiObjPreview=document.getElementById('lokasiObjPreview');
const luasTanahObj=document.getElementById('luasTanahObj');
const luasBangunanObj=document.getElementById('luasBangunanObj');
let objSearchController = null;
let objRequestSeq = 0;
let objLastAppliedSeq = 0;
let objLiveTimer = null;

function filterRowsInstantObj(keyword) {
  const rows = tbodyObj.querySelectorAll('tr');
  const q = (keyword || '').trim().toLowerCase();
  rows.forEach((row) => {
    if (row.querySelector('.empty')) return;
    const txt = row.textContent.toLowerCase();
    row.style.display = (q === '' || txt.includes(q)) ? '' : 'none';
  });
}

function syncLokasiFromWp(){
  const lokasi = idWpObj.options[idWpObj.selectedIndex]?.dataset?.lokasi || '';
  lokasiObj.value = lokasi;
  lokasiObjPreview.value = lokasi;
}

function resetObjForm(){
  formObj.action="{{ route('objek-pajak.store') }}";
  methodObj.value='POST';
  modalObjTitle.textContent='Tambah Objek Pajak';
  idWpObj.value='';
  nopObj.value='';
  lokasiObj.value='';
  lokasiObjPreview.value='';
  luasTanahObj.value='';
  luasBangunanObj.value='';
}

function openObjModal(){ resetObjForm(); openModal('modalObj'); }
function editObj(r){
  formObj.action=`{{ url('/objek-pajak') }}/${r.id_objek}`;
  methodObj.value='PUT';
  modalObjTitle.textContent='Ubah Objek Pajak';
  idWpObj.value=String(r.id_wp);
  nopObj.value=r.nop;
  syncLokasiFromWp();
  luasTanahObj.value=r.luas_tanah;
  luasBangunanObj.value=r.luas_bangunan;
  openModal('modalObj');
}
function editObjFromEncoded(encoded){ editObj(JSON.parse(decodeURIComponent(encoded))); }

async function loadObj(page=1){
  const reqSeq = ++objRequestSeq;
  if (objSearchController) objSearchController.abort();
  objSearchController = new AbortController();
  const q=encodeURIComponent(searchObj.value||'');
  try {
    const res=await fetch(`{{ route('objek-pajak.index') }}?search=${q}&page=${page}`,{
      headers:{'X-Requested-With':'XMLHttpRequest'},
      signal: objSearchController.signal
    });
    const j=await res.json();
    if (reqSeq < objLastAppliedSeq) return;
    objLastAppliedSeq = reqSeq;

    tbodyObj.innerHTML=j.data.length?j.data.map((r,idx)=>{
      const encoded=encodeURIComponent(JSON.stringify(r));
      const luasTanah = Number(r.luas_tanah || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
      const luasBangunan = Number(r.luas_bangunan || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
      return `<tr><td>${((j.meta.current_page - 1) * j.meta.per_page) + (idx + 1)}</td><td>${r.wajib_pajak?.nama_wp??r.wajibPajak?.nama_wp??'-'}</td><td>${r.nop}</td><td>${r.lokasi}</td><td>${luasTanah} m²</td><td>${luasBangunan} m²</td><td class='flex gap-2'><button class='btn' type='button' onclick='editObjFromEncoded("${encoded}")'>Ubah</button><form method='post' action='{{ url('/objek-pajak') }}/${r.id_objek}' onsubmit='return confirm("Yakin ingin menghapus data objek pajak ini?")'><input type='hidden' name='_token' value='{{ csrf_token() }}'><input type='hidden' name='_method' value='DELETE'><button class='btn danger'>Hapus</button></form></td></tr>`;
    }).join(''):`<tr><td colspan='7' class='empty'>Data kosong</td></tr>`;
    pagObj.innerHTML='';
    for(let i=1;i<=j.meta.last_page;i++){pagObj.innerHTML+=`<button class='logout-btn ${i===j.meta.current_page?'bg-teal-50 text-teal-800':''}' onclick='loadObj(${i})'>${i}</button>`}
  } catch (err) {
    if (err.name !== 'AbortError') console.error('Live search objek pajak gagal:', err);
  }
}

searchObj.addEventListener('input',()=>{
  filterRowsInstantObj(searchObj.value || '');
  clearTimeout(objLiveTimer);
  objLiveTimer = setTimeout(()=>loadObj(1),120);
});
searchObj.addEventListener('keydown', (e) => {
  if (e.key === 'Enter') {
    e.preventDefault();
    loadObj(1);
  }
});
searchObj.addEventListener('search', ()=>loadObj(1));
searchObj.addEventListener('change', ()=>loadObj(1));
loadObj(1);

idWpObj.addEventListener('change', syncLokasiFromWp);
nopObj.addEventListener('input', ()=>{ nopObj.value = nopObj.value.replace(/\D/g, '').slice(0, 18); });

formObj.addEventListener('submit', (event) => {
  syncLokasiFromWp();
  if (!idWpObj.value || !/^\d{18}$/.test(nopObj.value.trim()) || !lokasiObj.value.trim() || !luasTanahObj.value || !luasBangunanObj.value) {
    event.preventDefault();
    if (window.uiDialog?.alert) window.uiDialog.alert('Data belum lengkap. NOP wajib angka tepat 18 digit.');
    else alert('Data belum lengkap. NOP wajib angka tepat 18 digit.');
    return;
  }
  if (Number(luasTanahObj.value) < 0 || Number(luasBangunanObj.value) < 0) {
    event.preventDefault();
    if (window.uiDialog?.alert) window.uiDialog.alert('Luas tanah dan luas bangunan tidak boleh kurang dari 0.');
    else alert('Luas tanah dan luas bangunan tidak boleh kurang dari 0.');
    return;
  }
  const isEdit = methodObj.value === 'PUT';
  if (!window.confirm(isEdit ? 'Yakin ingin menyimpan perubahan data objek pajak ini?' : 'Yakin ingin menambahkan data objek pajak ini?')) {
    event.preventDefault();
  }
});
</script>
@endsection
