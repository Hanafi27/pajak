@extends('layouts.app')

@section('content')
<section class="panel">
  <div class="data-toolbar">
    <h1 class="text-lg font-semibold text-slate-800">Data Wajib Pajak</h1>
    <div class="data-toolbar-row">
      <button class="btn" type="button" onclick="openWpModal()">+ Tambah</button>
      <input id="searchWp" class="search-input" placeholder="Cari nama, KTP, alamat..." value="{{ $search }}">
    </div>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>No</th>
          <th>Nama</th>
          <th>No KTP</th>
          <th>Alamat</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody id="tbodyWp">
        @foreach($items as $i)
        <tr>
          <td>{{ (($items->currentPage() - 1) * $items->perPage()) + $loop->iteration }}</td>
          <td>{{ $i->nama_wp }}</td>
          <td>{{ $i->no_ktp }}</td>
          <td>{{ $i->alamat }}</td>
          <td class="flex gap-2">
            <button class="btn" type="button" onclick='editWp(@json($i))'>Ubah</button>
            <form method="post" action="{{ route('wajib-pajak.destroy', $i->id_wp) }}" onsubmit="return confirm('Yakin ingin menghapus data wajib pajak ini?')">
              @csrf
              @method('DELETE')
              <button class="btn danger">Hapus</button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div id="paginationWp" class="mt-3 flex justify-center gap-2"></div>
</section>

<div id="modalWp" class="modal-backdrop hidden">
  <div class="modal-card">
    <div class="modal-head">
      <h3 id="modalWpTitle" class="text-lg font-semibold">Tambah Wajib Pajak</h3>
      <button class="modal-close" onclick="closeModal('modalWp')">&times;</button>
    </div>
    <form id="formWp" method="post" action="{{ route('wajib-pajak.store') }}" class="form-grid">
      @csrf
      <input type="hidden" id="methodWp" name="_method" value="POST">
      <div class="field"><label>Nama Wajib Pajak</label><input id="namaWp" name="nama_wp" required></div>
      <div class="field"><label>No KTP</label><input id="noKtpWp" name="no_ktp" required inputmode="numeric" maxlength="16" pattern="\d{1,16}"></div>
      <div id="ubahAlamatWrapWp" class="field span-2 hidden">
        <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
          <label class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
            <input id="ubahAlamatWp" type="checkbox" class="h-4 w-4 rounded border-slate-300">
            Ubah alamat
          </label>
          <span id="statusAlamatWp" class="rounded-full bg-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-700">Alamat terkunci</span>
        </div>
      </div>
      <div class="field"><label>Kecamatan</label><select id="kecamatanWp"><option value="">Pilih Kecamatan</option></select></div>
      <div class="field"><label>Desa/Kelurahan</label><select id="desaWp" disabled><option value="">Pilih Desa/Kelurahan</option></select></div>
      <div class="field"><label>Kampung/Dusun</label><input id="kampungWp" placeholder="Contoh: Kp. Cikupa"></div>
      <div class="field"><label>RT / RW</label><div class="grid grid-cols-2 gap-2"><input id="rtWp" placeholder="RT"><input id="rwWp" placeholder="RW"></div></div>
      <input type="hidden" id="alamatWp" name="alamat" required>
      <div class="field span-2"><label>Alamat Terpilih</label><input id="alamatWpPreview" type="text" readonly placeholder="Akan terisi otomatis setelah melengkapi alamat"></div>
      <div class="actions"><button id="saveWpBtn" class="btn" type="submit">Simpan</button></div>
    </form>
  </div>
</div>

<script>
const tbodyWp = document.getElementById('tbodyWp');
const pagWp = document.getElementById('paginationWp');
const searchWp = document.getElementById('searchWp');
const formWp = document.getElementById('formWp');
const modalWpTitle = document.getElementById('modalWpTitle');
const methodWp = document.getElementById('methodWp');
const namaWp = document.getElementById('namaWp');
const noKtpWp = document.getElementById('noKtpWp');
const kecamatanWp = document.getElementById('kecamatanWp');
const desaWp = document.getElementById('desaWp');
const kampungWp = document.getElementById('kampungWp');
const rtWp = document.getElementById('rtWp');
const rwWp = document.getElementById('rwWp');
const alamatWp = document.getElementById('alamatWp');
const alamatWpPreview = document.getElementById('alamatWpPreview');
const ubahAlamatWrapWp = document.getElementById('ubahAlamatWrapWp');
const ubahAlamatWp = document.getElementById('ubahAlamatWp');
const statusAlamatWp = document.getElementById('statusAlamatWp');
const saveWpBtn = document.getElementById('saveWpBtn');
let isEditModeWp = false;
let existingAlamatWp = '';
let existingAlamatPartsWp = { kampung: '', rtRw: '', desa: '', kecamatan: '' };
let canEditAlamatWp = true;
let isSubmittingWp = false;
let wpSearchController = null;
let wpRequestSeq = 0;
let wpLastAppliedSeq = 0;
let wpLiveTimer = null;

function filterRowsInstantWp(keyword) {
  const rows = tbodyWp.querySelectorAll('tr');
  const q = (keyword || '').trim().toLowerCase();
  let visible = 0;
  rows.forEach((row) => {
    if (row.querySelector('.empty')) return;
    const txt = row.textContent.toLowerCase();
    const match = q === '' || txt.includes(q);
    row.style.display = match ? '' : 'none';
    if (match) visible++;
  });
  const emptyRow = tbodyWp.querySelector('.empty');
  if (emptyRow) emptyRow.style.display = '';
}

function setAlamatFieldsStateWp(enabled) {
  kecamatanWp.disabled = !enabled;
  desaWp.disabled = !enabled || !kecamatanWp.value;
  kampungWp.disabled = !enabled;
  rtWp.disabled = !enabled;
  rwWp.disabled = !enabled;
  statusAlamatWp.textContent = enabled ? 'Mode edit alamat aktif' : 'Alamat terkunci';
  statusAlamatWp.className = enabled
    ? 'rounded-full bg-emerald-100 px-2.5 py-1 text-[11px] font-semibold text-emerald-700'
    : 'rounded-full bg-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-700';
}

function parseAlamatWp(address) {
  const parts = String(address || '').split(',').map((x) => x.trim()).filter(Boolean);
  const cleaned = parts.filter((x) => x.toLowerCase() !== 'kabupaten bandung');
  return {
    kampung: cleaned[0] || '',
    rtRw: cleaned.find((x) => x.toUpperCase().startsWith('RT ')) || '',
    desa: cleaned[cleaned.length >= 2 ? cleaned.length - 2 : -1] || '',
    kecamatan: cleaned[cleaned.length >= 1 ? cleaned.length - 1 : -1] || '',
  };
}

function openModal(id) {
  const m = document.getElementById(id);
  m.classList.remove('hidden');
  m.classList.add('flex');
}

function closeModal(id) {
  const m = document.getElementById(id);
  m.classList.add('hidden');
  m.classList.remove('flex');
}

function resetWpForm() {
  isEditModeWp = false;
  existingAlamatWp = '';
  existingAlamatPartsWp = { kampung: '', rtRw: '', desa: '', kecamatan: '' };
  canEditAlamatWp = true;
  ubahAlamatWrapWp.classList.add('hidden');
  ubahAlamatWp.checked = false;
  formWp.action = "{{ route('wajib-pajak.store') }}";
  methodWp.value = 'POST';
  modalWpTitle.textContent = 'Tambah Wajib Pajak';
  namaWp.value = '';
  noKtpWp.value = '';
  kecamatanWp.value = '';
  desaWp.value = '';
  desaWp.disabled = true;
  desaWp.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
  kampungWp.value = '';
  rtWp.value = '';
  rwWp.value = '';
  alamatWp.value = '';
  alamatWpPreview.value = '';
  setAlamatFieldsStateWp(true);
}

function openWpModal() {
  resetWpForm();
  openModal('modalWp');
}

function editWp(row) {
  isEditModeWp = true;
  existingAlamatWp = row.alamat || '';
  existingAlamatPartsWp = parseAlamatWp(row.alamat || '');
  canEditAlamatWp = false;
  ubahAlamatWrapWp.classList.remove('hidden');
  ubahAlamatWp.checked = false;
  formWp.action = `{{ url('/wajib-pajak') }}/${row.id_wp}`;
  methodWp.value = 'PUT';
  modalWpTitle.textContent = 'Ubah Wajib Pajak';
  namaWp.value = row.nama_wp;
  noKtpWp.value = row.no_ktp;
  alamatWp.value = row.alamat;
  alamatWpPreview.value = row.alamat;
  kampungWp.value = '';
  rtWp.value = '';
  rwWp.value = '';
  kecamatanWp.value = '';
  desaWp.value = '';
  desaWp.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
  setAlamatFieldsStateWp(false);
  openModal('modalWp');
}

function editWpFromEncoded(encoded) {
  editWp(JSON.parse(decodeURIComponent(encoded)));
}

async function loadWp(page = 1) {
  const reqSeq = ++wpRequestSeq;
  if (wpSearchController) wpSearchController.abort();
  wpSearchController = new AbortController();
  const q = encodeURIComponent(searchWp.value || '');
  try {
    const res = await fetch(`{{ route('wajib-pajak.index') }}?search=${q}&page=${page}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      signal: wpSearchController.signal
    });
    const j = await res.json();
    if (reqSeq < wpLastAppliedSeq) return;
    wpLastAppliedSeq = reqSeq;

    tbodyWp.innerHTML = j.data.length
      ? j.data.map((r, idx) => {
        const encoded = encodeURIComponent(JSON.stringify(r));
        return `<tr><td>${((j.meta.current_page - 1) * j.meta.per_page) + (idx + 1)}</td><td>${r.nama_wp}</td><td>${r.no_ktp}</td><td>${r.alamat}</td><td class='flex gap-2'><button class='btn' type='button' onclick='editWpFromEncoded("${encoded}")'>Ubah</button><form method='post' action='{{ url('/wajib-pajak') }}/${r.id_wp}' onsubmit='return confirm("Yakin ingin menghapus data wajib pajak ini?")'><input type='hidden' name='_token' value='{{ csrf_token() }}'><input type='hidden' name='_method' value='DELETE'><button class='btn danger'>Hapus</button></form></td></tr>`;
      }).join('')
      : `<tr><td colspan='5' class='empty'>Data kosong</td></tr>`;

    pagWp.innerHTML = '';
    for (let i = 1; i <= j.meta.last_page; i++) {
      pagWp.innerHTML += `<button class='logout-btn ${i === j.meta.current_page ? 'bg-teal-50 text-teal-800' : ''}' onclick='loadWp(${i})'>${i}</button>`;
    }
  } catch (err) {
    if (err.name !== 'AbortError') {
      console.error('Live search gagal:', err);
    }
  }
}

searchWp.addEventListener('input', () => {
  const keyword = searchWp.value || '';
  filterRowsInstantWp(keyword);
  clearTimeout(wpLiveTimer);
  wpLiveTimer = setTimeout(() => loadWp(1), 120);
});
searchWp.addEventListener('keydown', (e) => {
  if (e.key === 'Enter') {
    e.preventDefault();
    loadWp(1);
  }
});
searchWp.addEventListener('search', () => loadWp(1));
searchWp.addEventListener('change', () => loadWp(1));
loadWp(1);

noKtpWp.addEventListener('input', () => {
  noKtpWp.value = noKtpWp.value.replace(/\D/g, '').slice(0, 16);
});

function buildAlamatWp() {
  const selectedKecamatan = kecamatanWp.options[kecamatanWp.selectedIndex]?.dataset?.name || '';
  const selectedDesa = desaWp.options[desaWp.selectedIndex]?.dataset?.name || '';
  const kampungInput = (kampungWp.value || '').trim();
  const rt = (rtWp.value || '').trim();
  const rw = (rwWp.value || '').trim();
  const rtRwInput = rt && rw ? `RT ${rt}/RW ${rw}` : '';

  const kampung = (isEditModeWp && canEditAlamatWp) ? (kampungInput || existingAlamatPartsWp.kampung) : kampungInput;
  const rtRw = (isEditModeWp && canEditAlamatWp) ? (rtRwInput || existingAlamatPartsWp.rtRw) : rtRwInput;
  const desaName = (isEditModeWp && canEditAlamatWp) ? (selectedDesa || existingAlamatPartsWp.desa) : selectedDesa;
  const kecamatanName = (isEditModeWp && canEditAlamatWp) ? (selectedKecamatan || existingAlamatPartsWp.kecamatan) : selectedKecamatan;
  const parts = [kampung, rtRw, desaName, kecamatanName, 'Kabupaten Bandung'].filter(Boolean);
  const fullAddress = parts.join(', ');
  if (!canEditAlamatWp) {
    alamatWp.value = existingAlamatWp || '';
    alamatWpPreview.value = existingAlamatWp || '';
    return;
  }
  if (fullAddress) {
    alamatWp.value = fullAddress;
    alamatWpPreview.value = fullAddress;
    return;
  }
  if (isEditModeWp && existingAlamatWp) {
    alamatWp.value = existingAlamatWp;
    alamatWpPreview.value = existingAlamatWp;
    return;
  }
  alamatWp.value = '';
  alamatWpPreview.value = '';
}

async function loadKecamatanWp() {
  const res = await fetch('https://www.emsifa.com/api-wilayah-indonesia/api/districts/3204.json');
  const data = await res.json();
  kecamatanWp.innerHTML = '<option value="">Pilih Kecamatan</option>' + data.map(d => `<option value="${d.id}" data-name="${d.name}">${d.name}</option>`).join('');
}

kecamatanWp.addEventListener('change', async () => {
  if (!canEditAlamatWp) return;
  const districtId = kecamatanWp.value;
  desaWp.disabled = true;
  desaWp.innerHTML = '<option value="">Memuat desa...</option>';
  buildAlamatWp();
  if (!districtId) {
    desaWp.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
    return;
  }
  const res = await fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/villages/${districtId}.json`);
  const data = await res.json();
  desaWp.disabled = false;
  desaWp.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>' + data.map(v => `<option value="${v.id}" data-name="${v.name}">${v.name}</option>`).join('');
});

desaWp.addEventListener('change', buildAlamatWp);
kampungWp.addEventListener('input', buildAlamatWp);
rtWp.addEventListener('input', buildAlamatWp);
rwWp.addEventListener('input', buildAlamatWp);

loadKecamatanWp();
ubahAlamatWp.addEventListener('change', () => {
  canEditAlamatWp = ubahAlamatWp.checked || !isEditModeWp;
  if (!canEditAlamatWp) {
    kecamatanWp.value = '';
    desaWp.value = '';
    desaWp.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
    kampungWp.value = '';
    rtWp.value = '';
    rwWp.value = '';
  }
  setAlamatFieldsStateWp(canEditAlamatWp);
  buildAlamatWp();
});

formWp.addEventListener('submit', (event) => {
  if (isSubmittingWp) {
    event.preventDefault();
    return;
  }

  buildAlamatWp();
  const nama = namaWp.value.trim();
  const noKtp = noKtpWp.value.trim();
  const alamat = alamatWp.value.trim();

  if (!nama || !noKtp || !alamat) {
    event.preventDefault();
    if (window.uiDialog?.alert) {
      window.uiDialog.alert('Data belum lengkap. Mohon lengkapi seluruh field wajib sebelum menyimpan.');
    } else {
      alert('Data belum lengkap. Mohon lengkapi seluruh field wajib sebelum menyimpan.');
    }
    return;
  }

  if (!/^\d{1,16}$/.test(noKtp)) {
    event.preventDefault();
    if (window.uiDialog?.alert) {
      window.uiDialog.alert('No KTP harus angka dan maksimal 16 digit.');
    } else {
      alert('No KTP harus angka dan maksimal 16 digit.');
    }
    return;
  }

  event.preventDefault();
  const isEdit = methodWp.value === 'PUT';
  const message = isEdit ? 'Yakin ingin menyimpan perubahan data wajib pajak ini?' : 'Yakin ingin menambahkan data wajib pajak ini?';

  const doSubmit = () => {
    isSubmittingWp = true;
    saveWpBtn.disabled = true;
    saveWpBtn.textContent = 'Menyimpan...';
    HTMLFormElement.prototype.submit.call(formWp);
  };

  if (window.confirm(message)) doSubmit();
});

</script>
@endsection
