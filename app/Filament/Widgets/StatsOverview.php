<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Odp;
use App\Models\Router;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalRouters = Router::count();
        $totalOdps = Odp::count();
        $totalClients = Client::count();
        $onlineClients = Client::where('is_online', true)->count();
        $offlineClients = Client::where('is_online', false)->count();

        return [
            Stat::make('Total Router/POP', $totalRouters)
                ->description('Router yang terdaftar')
                ->descriptionIcon('heroicon-m-server')
                ->color('success'),
            
            Stat::make('Total ODP', $totalOdps)
                ->description('ODP yang terdaftar')
                ->descriptionIcon('heroicon-m-map-pin')
                ->color('info'),
            
            Stat::make('Total Client', $totalClients)
                ->description("Online: {$onlineClients} | Offline: {$offlineClients}")
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
            
            Stat::make('Client Online', $onlineClients)
                ->description(number_format(($totalClients > 0 ? ($onlineClients / $totalClients) * 100 : 0), 1) . '% dari total')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
