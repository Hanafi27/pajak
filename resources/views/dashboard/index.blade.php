@extends('layouts.app')
@section('content')
<style>
  .dashboard-chart-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
    margin-bottom: 14px;
  }
  @media (min-width: 1024px) {
    .dashboard-chart-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }
  .chart-box {
    position: relative;
    height: 280px;
  }
  .chart-box--donut {
    max-width: 260px;
    height: 220px;
    margin: 0 auto;
  }
  .ai-insight-panel {
    display: grid;
    gap: 16px;
  }
  .ai-insight-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
  }
  .ai-insight-box {
    min-height: 120px;
    white-space: pre-wrap;
    border: 1px solid #ededed;
    border-radius: 8px;
    background: #fafafa;
    padding: 16px;
    color: #212121;
    line-height: 1.55;
  }
  @media (max-width: 760px) {
    .ai-insight-head {
      align-items: stretch;
      flex-direction: column;
    }
  }
</style>
<div class="hero">
  <div>
    <p class="eyebrow">Ringkasan {{ strtoupper($role) }}</p>
    <h1>Dashboard Menu Utama PBB</h1>
    <p class="muted">Visualisasi dinamis berdasarkan data real dari menu Data Wajib Pajak, Data Objek Pajak, Pengolahan PBB, dan Laporan.</p>
  </div>
</div>

<div class="stats-grid mb-3.5">
  <article class="stat-card">
    <p class="stat-label">Total Wajib Pajak</p>
    <p class="stat-value">{{ number_format($summary['wajib_pajak']) }}</p>
  </article>
  <article class="stat-card">
    <p class="stat-label">Total Objek Pajak</p>
    <p class="stat-value">{{ number_format($summary['objek_pajak']) }}</p>
  </article>
  <article class="stat-card accent">
    <p class="stat-label">Dana Terkumpul {{ $dashboardData['currentYear'] }}</p>
    <p class="stat-value">Rp {{ number_format($summary['total_penerimaan_tahun'], 0, ',', '.') }}</p>
  </article>
</div>


<section class="panel ai-insight-panel mb-3.5">
  <div class="ai-insight-head">
    <div>
      <p class="eyebrow">AI Insight</p>
      <h3 class="mt-2 mb-1">Analisis Otomatis Dashboard</h3>
      <p class="muted text-sm">Menggunakan data agregat dashboard tanpa mengirim KTP atau data pribadi mentah.</p>
    </div>
    <button id="generateInsightBtn" class="btn" type="button">Generate Insight AI</button>
  </div>
  <div id="aiInsightBox" class="ai-insight-box">Klik tombol generate untuk membuat ringkasan, temuan penting, dan saran tindak lanjut.</div>
</section>

<div class="dashboard-chart-grid">
  <section class="panel">
    <h3 class="mt-0">Status Pembayaran PBB Tahun {{ $dashboardData['currentYear'] }}</h3>
    <div class="chart-box chart-box--donut">
      <canvas id="paymentStatusChart" class="h-full w-full"></canvas>
    </div>
  </section>
  <section class="panel">
    <h3 class="mt-0">Pertumbuhan Objek Pajak Baru per Bulan</h3>
    <div class="chart-box">
      <canvas id="objectGrowthChart" class="h-full w-full"></canvas>
    </div>
  </section>
  <section class="panel">
    <h3 class="mt-0">Top Penerimaan Bulanan (Miliar Rupiah)</h3>
    <div class="chart-box">
      <canvas id="revenueTrendChart" class="h-full w-full"></canvas>
    </div>
  </section>
  <section class="panel">
    <h3 class="mt-0">Top Wilayah Penerimaan Tertinggi (Miliar Rupiah)</h3>
    <div class="chart-box">
      <canvas id="topRegionChart" class="h-full w-full"></canvas>
    </div>
  </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const generateInsightBtn = document.getElementById('generateInsightBtn');
const aiInsightBox = document.getElementById('aiInsightBox');

generateInsightBtn?.addEventListener('click', async () => {
  generateInsightBtn.disabled = true;
  generateInsightBtn.textContent = 'Menganalisis...';
  aiInsightBox.textContent = 'AI sedang membaca data agregat dashboard...';

  try {
    const res = await fetch('{{ route('dashboard.ai-insight') }}', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: JSON.stringify({})
    });
    const json = await res.json();

    if (!res.ok) {
      throw new Error(json.detail || json.message || 'Gagal membuat insight AI.');
    }

    aiInsightBox.textContent = json.insight || 'Insight kosong.';
  } catch (err) {
    aiInsightBox.textContent = `Insight AI belum bisa dibuat. ${err.message}`;
  } finally {
    generateInsightBtn.disabled = false;
    generateInsightBtn.textContent = 'Generate Insight AI';
  }
});

const data = @json($dashboardData);
const axisColor = '#707070';
const gridColor = '#ededed';

new Chart(document.getElementById('paymentStatusChart'), {
  type: 'doughnut',
  data: {
    labels: data.paymentStatusLabels,
    datasets: [{
      label: 'Sudah Bayar',
      data: data.paymentStatusValues,
      backgroundColor: ['#3ecf8e', '#1c1c1c'],
      borderColor: ['#24b47e', '#1c1c1c'],
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {legend: {position: 'bottom'}},
    cutout: '62%'
  }
});

new Chart(document.getElementById('objectGrowthChart'), {
  type: 'bar',
  data: {
    labels: data.monthlyLabels,
    datasets: [{
      label: 'Objek Baru',
      data: data.objectGrowth,
      backgroundColor: '#3ecf8e'
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {legend: {display: false}},
    scales: {
      x: {ticks: {color: axisColor}, grid: {display: false}},
      y: {ticks: {color: axisColor, precision: 0}, grid: {color: gridColor}}
    }
  }
});

new Chart(document.getElementById('revenueTrendChart'), {
  type: 'line',
  data: {
    labels: data.monthlyLabels,
    datasets: [{
      label: 'Penerimaan',
      data: data.monthlyRevenue,
      borderColor: '#24b47e',
      backgroundColor: 'rgba(62,207,142,.14)',
      fill: true,
      tension: .35,
      pointRadius: 3
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {legend: {display: false}},
    scales: {
      x: {ticks: {color: axisColor}, grid: {color: gridColor}},
      y: {ticks: {color: axisColor}, grid: {color: gridColor}}
    }
  }
});

new Chart(document.getElementById('topRegionChart'), {
  type: 'bar',
  data: {
    labels: data.regionLabels,
    datasets: [{
      label: 'Penerimaan',
      data: data.regionValues,
      backgroundColor: '#1c1e54'
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: 'y',
    plugins: {legend: {display: false}},
    scales: {
      x: {ticks: {color: axisColor}, grid: {color: gridColor}},
      y: {ticks: {color: axisColor}, grid: {display: false}}
    }
  }
});
</script>
@endsection






