@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')

{{-- Cabeçalho + filtros --}}
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="mb-0 fw-semibold">Dashboard</h4>
        <small class="text-muted">Monitoramento de execuções da API Dataprev</small>
    </div>
    <form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex gap-2 align-items-center flex-wrap">
        <select name="client" class="form-select form-select-sm" style="width:auto">
            <option value="">Todos os clientes</option>
            @foreach ($clientList as $c)
                <option value="{{ $c }}" {{ $clientFilter === $c ? 'selected' : '' }}>{{ $c }}</option>
            @endforeach
        </select>
        <select name="days" class="form-select form-select-sm" style="width:auto">
            @foreach ([7, 14, 30, 60, 90] as $d)
                <option value="{{ $d }}" {{ $days == $d ? 'selected' : '' }}>Últimos {{ $d }} dias</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-sm btn-dark">Filtrar</button>
    </form>
</div>

{{-- Cards por cliente --}}
<div class="row g-3 mb-4">
    @forelse ($perClient as $stat)
        <div class="col-sm-6 col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <span class="fw-semibold font-monospace text-truncate">{{ $stat->client_token }}</span>
                        <span class="badge {{ $stat->total_requests > 0 ? 'bg-primary' : 'bg-secondary' }} ms-2">ativo</span>
                    </div>
                    <div class="mt-3 d-flex gap-4">
                        <div>
                            <div class="fs-4 fw-bold text-primary">{{ number_format($stat->total_requests) }}</div>
                            <div class="text-muted" style="font-size:.75rem">EXECUÇÕES</div>
                        </div>
                        <div>
                            <div class="fs-4 fw-bold text-success">{{ number_format($stat->total_results) }}</div>
                            <div class="text-muted" style="font-size:.75rem">RESULTADOS</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-info mb-0">
                <i class="bi bi-info-circle me-1"></i>Nenhuma execução registrada ainda.
                As chamadas à API aparecerão aqui automaticamente.
            </div>
        </div>
    @endforelse
</div>

{{-- Gráficos linha (por dia) e distribuição (doughnut) --}}
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h6 class="card-title fw-semibold mb-3">
                    Execuções por dia
                    <small class="text-muted fw-normal">(últimos {{ $days }} dias)</small>
                </h6>
                <canvas id="chartDay" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <h6 class="card-title fw-semibold mb-3">Distribuição por cliente</h6>
                <div class="flex-grow-1 d-flex align-items-center justify-content-center">
                    <canvas id="chartClients" style="max-height:220px"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Gráfico horário --}}
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-body">
                <h6 class="card-title fw-semibold mb-3">
                    Execuções por hora do dia
                    <small class="text-muted fw-normal">(últimos {{ $days }} dias)</small>
                </h6>
                <canvas id="chartHour" height="60"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Tabela de execuções recentes --}}
<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold">Últimas 50 execuções</h6>
        </div>
        @if ($recentLogs->isEmpty())
            <div class="text-center text-muted py-4">Nenhum registro.</div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle small">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Data / Hora</th>
                            <th>Cliente</th>
                            <th>Endpoint</th>
                            <th>Status</th>
                            <th class="pe-4 text-end">Resultados</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentLogs as $log)
                            <tr>
                                <td class="ps-4 text-muted font-monospace" style="font-size:.8rem">
                                    {{ $log->created_at->format('d/m/Y H:i:s') }}
                                </td>
                                <td><span class="badge bg-dark">{{ $log->client_token }}</span></td>
                                <td class="font-monospace" style="font-size:.8rem">{{ $log->endpoint }}</td>
                                <td>
                                    @if ($log->http_status >= 200 && $log->http_status < 300)
                                        <span class="badge bg-success">{{ $log->http_status }}</span>
                                    @elseif ($log->http_status >= 400 && $log->http_status < 500)
                                        <span class="badge bg-warning text-dark">{{ $log->http_status }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ $log->http_status }}</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end text-muted">
                                    {{ $log->results_count ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
const colors = ['#0d6efd','#198754','#dc3545','#fd7e14','#6f42c1','#20c997','#0dcaf0'];

// Gráfico: execuções por dia (multi-linha por cliente)
new Chart(document.getElementById('chartDay'), {
    type: 'line',
    data: {
        labels: @json($dailyLabels),
        datasets: @json($clientDayDatasets),
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
    }
});

// Gráfico: distribuição por cliente (doughnut)
new Chart(document.getElementById('chartClients'), {
    type: 'doughnut',
    data: {
        labels: @json($clientLabels),
        datasets: [{ data: @json($clientData), backgroundColor: colors }],
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } },
    }
});

// Gráfico: execuções por hora
new Chart(document.getElementById('chartHour'), {
    type: 'bar',
    data: {
        labels: @json($hourlyLabels),
        datasets: [{
            label: 'Execuções',
            data: @json($hourlyData),
            backgroundColor: '#0d6efd88',
            borderColor: '#0d6efd',
            borderWidth: 1,
        }],
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
    }
});
</script>

@endsection
