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
const data = @json($dashboardData);
const axisColor = '#5f7378';
const gridColor = '#e6eff0';

new Chart(document.getElementById('paymentStatusChart'), {
  type: 'doughnut',
  data: {
    labels: data.paymentStatusLabels,
    datasets: [{
      label: 'Sudah Bayar',
      data: data.paymentStatusValues,
      backgroundColor: ['#0f9f67', '#dc2626'],
      borderColor: ['#0a7d50', '#b91c1c'],
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
      backgroundColor: '#0f5f61'
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
      borderColor: '#0f5f61',
      backgroundColor: 'rgba(15,95,97,.18)',
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
      backgroundColor: '#14b8a6'
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
