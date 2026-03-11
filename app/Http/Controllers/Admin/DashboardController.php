<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RequestLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $days = (int) $request->input('days', 30);
        $clientFilter = $request->input('client');

        $baseQuery = fn () => RequestLog::query()
            ->when($clientFilter, fn ($q) => $q->where('client_token', $clientFilter))
            ->where('created_at', '>=', now()->subDays($days - 1)->startOfDay());

        // Cards: resumo por cliente
        $perClient = RequestLog::selectRaw('client_token, count(*) as total_requests, COALESCE(sum(results_count), 0) as total_results')
            ->groupBy('client_token')
            ->orderByDesc('total_requests')
            ->get();

        // Gráfico 1: execuções por dia (últimos N dias)
        $dailyRaw = $baseQuery()
            ->selectRaw('DATE(created_at) as date, count(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $dailyLabels = collect(range($days - 1, 0))
            ->map(fn ($d) => now()->subDays($d)->format('Y-m-d'));

        $dailyData   = $dailyLabels->map(fn ($d) => (int) ($dailyRaw->get($d, 0)));
        $dailyLabels = $dailyLabels->map(fn ($d) => Carbon::parse($d)->format('d/m'));

        // Gráfico 2: execuções por hora do dia
        $hourlyRaw = $baseQuery()
            ->selectRaw('HOUR(created_at) as hour, count(*) as total')
            ->groupBy('hour')
            ->pluck('total', 'hour');

        $hourlyLabels = collect(range(0, 23))->map(fn ($h) => str_pad($h, 2, '0', STR_PAD_LEFT) . 'h');
        $hourlyData   = collect(range(0, 23))->map(fn ($h) => (int) ($hourlyRaw->get($h, 0)));

        // Gráfico 3: distribuição por cliente (doughnut)
        $clientLabels = $perClient->pluck('client_token');
        $clientData   = $perClient->pluck('total_requests');

        // Gráfico 4: execuções por dia agrupado por cliente (últimos N dias, linha por cliente)
        $allClients = RequestLog::distinct()->pluck('client_token');

        $byClientDay = $baseQuery()
            ->selectRaw('client_token, DATE(created_at) as date, count(*) as total')
            ->groupBy('client_token', 'date')
            ->get()
            ->groupBy('client_token');

        $chartColors = ['#0d6efd', '#198754', '#dc3545', '#fd7e14', '#6f42c1', '#20c997', '#0dcaf0'];

        $clientDayDatasets = $allClients->values()->map(function ($client, $i) use ($byClientDay, $days, $chartColors) {
            $raw = $byClientDay->get($client, collect())->pluck('total', 'date');
            $data = collect(range($days - 1, 0))
                ->map(fn ($d) => (int) ($raw->get(now()->subDays($d)->format('Y-m-d'), 0)));
            return [
                'label'           => $client,
                'data'            => $data->values(),
                'borderColor'     => $chartColors[$i % count($chartColors)],
                'backgroundColor' => $chartColors[$i % count($chartColors)] . '22',
                'tension'         => 0.3,
                'fill'            => true,
            ];
        });

        // Tabela: últimas 50 requisições
        $recentLogs = RequestLog::query()
            ->when($clientFilter, fn ($q) => $q->where('client_token', $clientFilter))
            ->latest()
            ->limit(50)
            ->get();

        // Lista de clientes para o filtro
        $clientList = RequestLog::distinct()->pluck('client_token')->sort()->values();

        return view('admin.dashboard', compact(
            'perClient', 'days', 'clientFilter', 'clientList',
            'dailyLabels', 'dailyData',
            'hourlyLabels', 'hourlyData',
            'clientLabels', 'clientData',
            'clientDayDatasets', 'recentLogs'
        ));
    }
}
