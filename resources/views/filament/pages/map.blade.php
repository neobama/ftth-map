<x-filament-panels::page><div class="relative w-full h-screen" style="height: calc(100vh - 4rem);">
        <!-- Toolbar -->
        <div class="absolute top-4 left-4 z-50 flex flex-wrap gap-2 bg-gray-900/95 backdrop-blur-sm rounded-lg p-2 shadow-xl border border-gray-700 max-w-2xl">
            <button 
                wire:click="setMode('add-pop')" 
                class="px-3 py-1.5 text-sm bg-green-600 hover:bg-green-700 text-white rounded transition-colors font-medium whitespace-nowrap"
                :class="{ 'ring-2 ring-green-400': $wire.mode === 'add-pop' }"
            >
                Add POP
            </button>
            <button 
                wire:click="setMode('add-odp')" 
                class="px-3 py-1.5 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded transition-colors font-medium whitespace-nowrap"
                :class="{ 'ring-2 ring-blue-400': $wire.mode === 'add-odp' }"
            >
                Add ODP
            </button>
            <button 
                wire:click="setMode('add-cable')" 
                class="px-3 py-1.5 text-sm bg-purple-600 hover:bg-purple-700 text-white rounded transition-colors font-medium whitespace-nowrap"
                :class="{ 'ring-2 ring-purple-400': $wire.mode === 'add-cable' }"
            >
                Add Kabel
            </button>
            <button 
                wire:click="setMode('add-tiang')" 
                class="px-3 py-1.5 text-sm bg-amber-600 hover:bg-amber-700 text-white rounded transition-colors font-medium whitespace-nowrap"
                :class="{ 'ring-2 ring-amber-400': $wire.mode === 'add-tiang' }"
            >
                Add Tiang
            </button>
            <button 
                wire:click="setMode(null)" 
                class="px-3 py-1.5 text-sm bg-gray-600 hover:bg-gray-700 text-white rounded transition-colors font-medium whitespace-nowrap"
            >
                Cancel
            </button>
        </div>

        <!-- Banner untuk mode penempatan -->
        <div x-show="$wire.placementMode" x-cloak class="absolute top-16 left-1/2 transform -translate-x-1/2 z-50 bg-blue-600 text-white px-6 py-3 rounded-lg shadow-xl animate-pulse border border-blue-400">
            <span class="font-semibold" x-text="$wire.placementModeText"></span>
        </div>

        <!-- ODP Detail Panel -->
        <div x-show="$wire.selectedOdp" x-cloak class="absolute top-4 right-4 z-50 w-80 bg-gray-900/95 backdrop-blur-sm rounded-lg shadow-xl p-4 max-h-[calc(100vh-8rem)] overflow-y-auto border border-gray-700">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-white">ODP Details</h3>
                    <button wire:click="closeOdpPanel" class="text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="space-y-4 text-white" x-data="{ odp: $wire.selectedOdp }">
                    <div>
                        <p class="text-sm text-gray-400">Nama</p>
                        <p class="font-semibold" x-text="odp?.name"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Router Parent</p>
                        <p class="font-semibold" x-text="odp?.router_name"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Kapasitas</p>
                        <p class="font-semibold" x-text="`${odp?.used_ports} / ${odp?.port_capacity} port`"></p>
                        <div class="w-full bg-gray-700 rounded-full h-2 mt-2">
                            <div class="h-2 rounded-full" 
                                 :class="odp?.color === 'red' ? 'bg-red-500' : (odp?.color === 'yellow' ? 'bg-yellow-500' : 'bg-blue-500')"
                                 :style="`width: ${odp?.capacity_percentage}%`"></div>
                        </div>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-400 mb-2">Kabel Terhubung</p>
                        <div class="space-y-1">
                            <template x-for="cable in odp?.cables || []" :key="cable.id">
                                <div class="bg-gray-800 p-2 rounded text-sm" x-text="cable.name"></div>
                            </template>
                        </div>
                    </div>
                    
                    <div>
                        <p class="text-sm text-gray-400 mb-2">Clients</p>
                        <div class="space-y-1">
                            <template x-for="client in odp?.clients || []" :key="client.id">
                                <div class="bg-gray-800 p-2 rounded text-sm flex justify-between">
                                    <span x-text="client.name"></span>
                                    <span :class="client.is_online ? 'text-green-400' : 'text-red-400'" x-text="client.is_online ? 'Online' : 'Offline'"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                    
                    <button 
                        wire:click="startAddClient" 
                        class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition-colors font-medium"
                    >
                        Add Client
                    </button>
                </div>
        </div>

        <!-- Map Container - Always visible -->
        <div id="map" class="absolute inset-0 w-full h-full z-0" style="min-height: 100%;">
            <!-- Error message jika API key tidak ada -->
            <div x-show="!@js($this->getGoogleMapsKey())" x-cloak class="absolute inset-0 flex items-center justify-center z-10 bg-gray-900">
                <div class="p-6 bg-yellow-100 border-2 border-yellow-400 text-yellow-800 rounded-lg max-w-md shadow-xl">
                    <p class="font-bold text-lg mb-2">⚠️ Google Maps API Key belum dikonfigurasi!</p>
                    <p class="text-sm mb-4">Tambahkan GOOGLE_MAPS_API_KEY di file .env</p>
                    <p class="text-xs text-yellow-700">
                        Contoh: <code class="bg-yellow-200 px-2 py-1 rounded">GOOGLE_MAPS_API_KEY=your_api_key_here</code>
                    </p>
                </div>
            </div>

            <!-- Map initialization - Always runs, no conditional rendering -->
            <div x-data x-init="
                (function() {
                    const apiKey = @js($this->getGoogleMapsKey());
                    if (!apiKey) {
                        return;
                    }
                    
                    // Store Livewire component reference
                    if (typeof Livewire !== 'undefined' && $wire) {
                        window.livewireComponent = $wire;
                    }
                    
                    // Load external map.js file only once
                    if (!window.mapJsLoaded) {
                        window.mapJsLoaded = true;
                        const script = document.createElement('script');
                        script.src = '/js/map.js';
                        script.onload = function() {
                            // Load Google Maps API after map.js is loaded
                            if (typeof loadGoogleMapsAPI === 'function') {
                                loadGoogleMapsAPI(apiKey);
                            }
                        };
                        document.head.appendChild(script);
                    } else {
                        // If map.js already loaded, just initialize map if not already done
                        if (typeof loadGoogleMapsAPI === 'function' && !window.mapInitialized) {
                            loadGoogleMapsAPI(apiKey);
                        }
                    }
                })();
            " class="absolute inset-0 w-full h-full pointer-events-none">
            </div>
        </div>
    </div>
</x-filament-panels::page>
