<?php

namespace App\Filament\Pages;

use App\Models\Cable;
use App\Models\Client;
use App\Models\Odp;
use App\Models\Router;
use App\Models\Tiang;
use Filament\Pages\Page;
use Livewire\Attributes\On;

class Map extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationLabel = 'Peta';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.pages.map';

    public $mode = null;
    public $placementMode = false;
    public $placementModeText = '';
    public $selectedOdp = null;
    public $cableFrom = null;
    public $cableTo = null;

    public function getGoogleMapsKey()
    {
        return config('services.google_maps.api_key', '');
    }

    public function getRouters()
    {
        return Router::all()->map(function ($router) {
            return [
                'id' => $router->id,
                'name' => $router->name,
                'ip_address' => $router->ip_address,
                'latitude' => $router->latitude,
                'longitude' => $router->longitude,
            ];
        });
    }

    public function getOdps()
    {
        return Odp::with('router')->get()->map(function ($odp) {
            return [
                'id' => $odp->id,
                'name' => $odp->name,
                'router_id' => $odp->router_id,
                'router_name' => $odp->router->name ?? '',
                'latitude' => $odp->latitude,
                'longitude' => $odp->longitude,
                'port_capacity' => $odp->port_capacity,
                'used_ports' => $odp->used_ports,
                'capacity_percentage' => $odp->capacity_percentage,
                'color' => $odp->color,
            ];
        });
    }

    public function getCables()
    {
        return Cable::all();
    }

    public function getClients()
    {
        return Client::with('odp')->get()->map(function ($client) {
            return [
                'id' => $client->id,
                'name' => $client->name,
                'pppoe_username' => $client->pppoe_username,
                'latitude' => $client->latitude,
                'longitude' => $client->longitude,
                'is_online' => $client->is_online,
                'odp' => $client->odp ? [
                    'id' => $client->odp->id,
                    'latitude' => $client->odp->latitude,
                    'longitude' => $client->odp->longitude,
                ] : null,
            ];
        });
    }

    public function getTiangs()
    {
        return Tiang::with('cable')->get();
    }

    public function setMode($mode)
    {
        $this->mode = $mode;
        $this->placementMode = in_array($mode, ['add-pop', 'add-odp', 'add-client', 'add-tiang']);
        
        $modeTexts = [
            'add-pop' => 'Klik di peta untuk menambahkan POP',
            'add-odp' => 'Klik di peta untuk menambahkan ODP',
            'add-client' => 'Klik di peta untuk menambahkan Client',
            'add-tiang' => 'Klik di dekat kabel untuk menambahkan Tiang',
        ];
        
        $this->placementModeText = $modeTexts[$mode] ?? '';
        
        $this->dispatch('map-mode-changed', mode: $mode);
    }

    public function handleMapClick($lat, $lng)
    {
        if ($this->mode === 'add-pop') {
            $this->dispatch('open-pop-dialog', lat: $lat, lng: $lng);
        } elseif ($this->mode === 'add-odp') {
            $this->dispatch('open-odp-dialog', lat: $lat, lng: $lng);
        } elseif ($this->mode === 'add-client' && $this->selectedOdp) {
            $this->dispatch('open-client-dialog', lat: $lat, lng: $lng, odpId: $this->selectedOdp['id']);
        } elseif ($this->mode === 'add-tiang') {
            $this->dispatch('open-tiang-dialog', lat: $lat, lng: $lng);
        } elseif ($this->mode === 'add-cable') {
            // Handle cable selection
            $this->handleCableSelection($lat, $lng);
        }
    }

    public function selectOdp($odpId)
    {
        $odp = Odp::with(['router', 'clients', 'cables'])->find($odpId);
        
        if ($odp) {
            $this->selectedOdp = [
                'id' => $odp->id,
                'name' => $odp->name,
                'router_name' => $odp->router->name ?? '',
                'port_capacity' => $odp->port_capacity,
                'used_ports' => $odp->used_ports,
                'capacity_percentage' => $odp->capacity_percentage,
                'color' => $odp->color,
                'cables' => $odp->cables->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->toArray(),
                'clients' => $odp->clients->map(fn($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'is_online' => $c->is_online,
                ])->toArray(),
            ];
        }
    }

    public function closeOdpPanel()
    {
        $this->selectedOdp = null;
    }

    public function startAddClient()
    {
        $this->setMode('add-client');
    }

    public function handleCableSelection($lat, $lng)
    {
        // Find nearest node (router or odp)
        // This is simplified - in real app, you'd calculate distance
        if (!$this->cableFrom) {
            $this->cableFrom = ['lat' => $lat, 'lng' => $lng];
        } else {
            $this->cableTo = ['lat' => $lat, 'lng' => $lng];
            $this->dispatch('open-cable-dialog', from: $this->cableFrom, to: $this->cableTo);
            $this->cableFrom = null;
            $this->cableTo = null;
        }
    }

    public function editCable($cableId)
    {
        $this->dispatch('open-cable-edit-dialog', cableId: $cableId);
    }
}
