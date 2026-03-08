<x-filament-panels::page>
    <div class="relative w-full h-screen" style="height: calc(100vh - 4rem);">
        <!-- Toolbar -->
        <div class="absolute top-4 left-4 z-10 flex gap-2 bg-gray-900/90 backdrop-blur-sm rounded-lg p-2 shadow-lg">
            <button 
                wire:click="setMode('add-pop')" 
                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition-colors font-medium"
                :class="{ 'ring-2 ring-green-400': mode === 'add-pop' }"
            >
                Add POP
            </button>
            <button 
                wire:click="setMode('add-odp')" 
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors font-medium"
                :class="{ 'ring-2 ring-blue-400': mode === 'add-odp' }"
            >
                Add ODP
            </button>
            <button 
                wire:click="setMode('add-cable')" 
                class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md transition-colors font-medium"
                :class="{ 'ring-2 ring-purple-400': mode === 'add-cable' }"
            >
                Add Kabel
            </button>
            <button 
                wire:click="setMode('add-tiang')" 
                class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-md transition-colors font-medium"
                :class="{ 'ring-2 ring-amber-400': mode === 'add-tiang' }"
            >
                Add Tiang
            </button>
            <button 
                wire:click="setMode(null)" 
                class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md transition-colors font-medium"
            >
                Cancel
            </button>
        </div>

        <!-- Banner untuk mode penempatan -->
        @if($placementMode)
        <div class="absolute top-20 left-1/2 transform -translate-x-1/2 z-10 bg-blue-600 text-white px-6 py-3 rounded-lg shadow-lg animate-pulse">
            <span class="font-semibold">{{ $placementModeText }}</span>
        </div>
        @endif

        <!-- ODP Detail Panel -->
        @if($selectedOdp)
        <div class="absolute top-4 right-4 z-10 w-80 bg-gray-900 rounded-lg shadow-xl p-4 max-h-[calc(100vh-8rem)] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-white">ODP Details</h3>
                <button wire:click="closeOdpPanel" class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-4 text-white">
                <div>
                    <p class="text-sm text-gray-400">Nama</p>
                    <p class="font-semibold">{{ $selectedOdp['name'] }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Router Parent</p>
                    <p class="font-semibold">{{ $selectedOdp['router_name'] }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Kapasitas</p>
                    <p class="font-semibold">{{ $selectedOdp['used_ports'] }} / {{ $selectedOdp['port_capacity'] }} port</p>
                    <div class="w-full bg-gray-700 rounded-full h-2 mt-2">
                        <div class="h-2 rounded-full {{ $selectedOdp['color'] === 'red' ? 'bg-red-500' : ($selectedOdp['color'] === 'yellow' ? 'bg-yellow-500' : 'bg-blue-500') }}" 
                             style="width: {{ $selectedOdp['capacity_percentage'] }}%"></div>
                    </div>
                </div>
                
                <div>
                    <p class="text-sm text-gray-400 mb-2">Kabel Terhubung</p>
                    <div class="space-y-1">
                        @foreach($selectedOdp['cables'] ?? [] as $cable)
                        <div class="bg-gray-800 p-2 rounded text-sm">{{ $cable['name'] }}</div>
                        @endforeach
                    </div>
                </div>
                
                <div>
                    <p class="text-sm text-gray-400 mb-2">Clients</p>
                    <div class="space-y-1">
                        @foreach($selectedOdp['clients'] ?? [] as $client)
                        <div class="bg-gray-800 p-2 rounded text-sm flex justify-between">
                            <span>{{ $client['name'] }}</span>
                            <span class="{{ $client['is_online'] ? 'text-green-400' : 'text-red-400' }}">
                                {{ $client['is_online'] ? 'Online' : 'Offline' }}
                            </span>
                        </div>
                        @endforeach
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
        @endif

        <!-- Map Container -->
        <div id="map" class="w-full h-full"></div>
    </div>

    @push('scripts')
    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.google_maps.api_key') }}&libraries=geometry,drawing,places"></script>
    <script>
        let map;
        let markers = {};
        let polylines = {};
        let drawingManager;
        let currentMode = null;
        let selectedFrom = null;
        let selectedTo = null;
        let manualWaypoints = [];
        let directionsService;
        let directionsRenderer;

        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: { lat: -6.2088, lng: 106.8456 }, // Jakarta default
                zoom: 13,
                styles: [
                    { elementType: 'geometry', stylers: [{ color: '#242f3e' }] },
                    { elementType: 'labels.text.stroke', stylers: [{ color: '#242f3e' }] },
                    { elementType: 'labels.text.fill', stylers: [{ color: '#746855' }] },
                    {
                        featureType: 'administrative.locality',
                        elementType: 'labels.text.fill',
                        stylers: [{ color: '#d59563' }]
                    },
                    {
                        featureType: 'poi',
                        elementType: 'labels.text.fill',
                        stylers: [{ color: '#d59563' }]
                    },
                    {
                        featureType: 'poi.park',
                        elementType: 'geometry',
                        stylers: [{ color: '#263c3f' }]
                    },
                    {
                        featureType: 'road',
                        elementType: 'geometry',
                        stylers: [{ color: '#38414e' }]
                    },
                    {
                        featureType: 'road',
                        elementType: 'geometry.stroke',
                        stylers: [{ color: '#212a37' }]
                    },
                    {
                        featureType: 'road',
                        elementType: 'labels.text.fill',
                        stylers: [{ color: '#9ca5b3' }]
                    },
                    {
                        featureType: 'road.highway',
                        elementType: 'geometry',
                        stylers: [{ color: '#746855' }]
                    },
                    {
                        featureType: 'road.highway',
                        elementType: 'geometry.stroke',
                        stylers: [{ color: '#1f2835' }]
                    },
                    {
                        featureType: 'road.highway',
                        elementType: 'labels.text.fill',
                        stylers: [{ color: '#f3d19c' }]
                    },
                    {
                        featureType: 'transit',
                        elementType: 'geometry',
                        stylers: [{ color: '#2f3948' }]
                    },
                    {
                        featureType: 'transit.station',
                        elementType: 'labels.text.fill',
                        stylers: [{ color: '#d59563' }]
                    },
                    {
                        featureType: 'water',
                        elementType: 'geometry',
                        stylers: [{ color: '#17263c' }]
                    },
                    {
                        featureType: 'water',
                        elementType: 'labels.text.fill',
                        stylers: [{ color: '#515c6d' }]
                    },
                    {
                        featureType: 'water',
                        elementType: 'labels.text.stroke',
                        stylers: [{ color: '#17263c' }]
                    }
                ]
            });

            directionsService = new google.maps.DirectionsService();
            directionsRenderer = new google.maps.DirectionsRenderer({
                map: map,
                suppressMarkers: true
            });

            // Load existing data
            loadRouters();
            loadOdps();
            loadCables();
            loadClients();
            loadTiangs();

            // Map click handler
            map.addListener('click', handleMapClick);
        }

        function loadRouters() {
            @this.call('getRouters').then(routers => {
                routers.forEach(router => {
                    createRouterMarker(router);
                });
            });
        }

        function loadOdps() {
            @this.call('getOdps').then(odps => {
                odps.forEach(odp => {
                    createOdpMarker(odp);
                });
            });
        }

        function loadCables() {
            @this.call('getCables').then(cables => {
                cables.forEach(cable => {
                    createCablePolyline(cable);
                });
            });
        }

        function loadClients() {
            @this.call('getClients').then(clients => {
                clients.forEach(client => {
                    createClientMarker(client);
                });
            });
        }

        function loadTiangs() {
            @this.call('getTiangs').then(tiangs => {
                tiangs.forEach(tiang => {
                    createTiangMarker(tiang);
                });
            });
        }

        function createRouterMarker(router) {
            const marker = new google.maps.Marker({
                position: { lat: parseFloat(router.latitude), lng: parseFloat(router.longitude) },
                map: map,
                icon: {
                    url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                        <svg width="40" height="40" xmlns="http://www.w3.org/2000/svg">
                            <rect width="40" height="40" fill="#22c55e" rx="4"/>
                            <text x="20" y="25" font-size="12" fill="white" text-anchor="middle" font-weight="bold">POP</text>
                        </svg>
                    `),
                    scaledSize: new google.maps.Size(40, 40),
                    anchor: new google.maps.Point(20, 20)
                },
                title: router.name
            });

            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div class="p-2">
                        <h3 class="font-bold">${router.name}</h3>
                        <p>IP: ${router.ip_address}</p>
                    </div>
                `
            });

            marker.addListener('click', () => {
                infoWindow.open(map, marker);
            });

            markers[`router_${router.id}`] = marker;
        }

        function createOdpMarker(odp) {
            const color = getOdpColor(odp);
            const marker = new google.maps.Marker({
                position: { lat: parseFloat(odp.latitude), lng: parseFloat(odp.longitude) },
                map: map,
                icon: {
                    url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                        <svg width="30" height="30" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15 0 L30 30 L0 30 Z" fill="${color}"/>
                            <text x="15" y="22" font-size="10" fill="white" text-anchor="middle" font-weight="bold">ODP</text>
                        </svg>
                    `),
                    scaledSize: new google.maps.Size(30, 30),
                    anchor: new google.maps.Point(15, 30)
                },
                title: odp.name
            });

            marker.addListener('click', () => {
                @this.call('selectOdp', odp.id);
            });

            markers[`odp_${odp.id}`] = marker;
        }

        function getOdpColor(odp) {
            const percentage = (odp.used_ports / odp.port_capacity) * 100;
            if (percentage < 50) return '#3b82f6'; // blue
            if (percentage < 80) return '#eab308'; // yellow
            return '#ef4444'; // red
        }

        function createClientMarker(client) {
            const color = client.is_online ? '#22c55e' : '#ef4444';
            const marker = new google.maps.Marker({
                position: { lat: parseFloat(client.latitude), lng: parseFloat(client.longitude) },
                map: map,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 8,
                    fillColor: color,
                    fillOpacity: 1,
                    strokeColor: '#fff',
                    strokeWeight: 2
                },
                title: client.name
            });

            if (client.is_online) {
                marker.setAnimation(google.maps.Animation.BOUNCE);
            }

            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div class="p-2">
                        <h3 class="font-bold">${client.name}</h3>
                        <p>PPPoE: ${client.pppoe_username}</p>
                        <p class="${client.is_online ? 'text-green-500' : 'text-red-500'}">
                            ${client.is_online ? 'Online' : 'Offline'}
                        </p>
                    </div>
                `
            });

            marker.addListener('click', () => {
                infoWindow.open(map, marker);
            });

            marker.addListener('mouseover', () => {
                infoWindow.open(map, marker);
            });

            markers[`client_${client.id}`] = marker;

            // Create client cable line
            if (client.odp) {
                createClientCable(client);
            }
        }

        function createClientCable(client) {
            const lineColor = client.is_online ? '#22c55e' : '#ef4444';
            const linePattern = client.is_online ? null : [5, 5];
            
            const polyline = new google.maps.Polyline({
                path: [
                    { lat: parseFloat(client.odp.latitude), lng: parseFloat(client.odp.longitude) },
                    { lat: parseFloat(client.latitude), lng: parseFloat(client.longitude) }
                ],
                geodesic: true,
                strokeColor: lineColor,
                strokeOpacity: 1.0,
                strokeWeight: 2,
                icons: linePattern ? [{
                    icon: { path: google.maps.SymbolPath.CIRCLE, scale: 2, fillOpacity: 1 },
                    offset: '0%',
                    repeat: '10px'
                }] : []
            });

            if (client.is_online) {
                // Animated line for online clients
                animateLine(polyline);
            }

            polyline.setMap(map);
            polylines[`client_${client.id}`] = polyline;
        }

        function animateLine(polyline) {
            let count = 0;
            setInterval(() => {
                count = (count + 1) % 200;
                const icons = polyline.get('icons');
                if (icons && icons[0]) {
                    icons[0].offset = (count / 2) + '%';
                    polyline.set('icons', icons);
                }
            }, 50);
        }

        function createCablePolyline(cable) {
            let path = [];
            
            if (cable.route_type === 'point-to-point') {
                // Get from and to coordinates
                const fromCoords = getNodeCoordinates(cable.from_type, cable.from_id);
                const toCoords = getNodeCoordinates(cable.to_type, cable.to_id);
                path = [fromCoords, toCoords];
            } else if (cable.route_type === 'ikut-jalan') {
                // Use waypoints from database
                if (cable.waypoints && cable.waypoints.length > 0) {
                    path = cable.waypoints.map(wp => ({ lat: wp.lat, lng: wp.lng }));
                }
            } else if (cable.route_type === 'manual') {
                // Use manual waypoints
                if (cable.waypoints && cable.waypoints.length > 0) {
                    path = cable.waypoints.map(wp => ({ lat: wp.lat, lng: wp.lng }));
                }
            }

            if (path.length < 2) return;

            const polyline = new google.maps.Polyline({
                path: path,
                geodesic: true,
                strokeColor: '#8b5cf6',
                strokeOpacity: 0.8,
                strokeWeight: 3
            });

            polyline.setMap(map);
            polylines[`cable_${cable.id}`] = polyline;

            // Make cable clickable for editing
            polyline.addListener('click', () => {
                @this.call('editCable', cable.id);
            });
        }

        function getNodeCoordinates(type, id) {
            // This would need to fetch from markers or make API call
            // For now, placeholder
            return { lat: 0, lng: 0 };
        }

        function createTiangMarker(tiang) {
            const marker = new google.maps.Marker({
                position: { lat: parseFloat(tiang.latitude), lng: parseFloat(tiang.longitude) },
                map: map,
                icon: {
                    url: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
                        <svg width="20" height="20" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="10" cy="10" r="8" fill="#92400e"/>
                            <circle cx="10" cy="10" r="4" fill="#fbbf24"/>
                        </svg>
                    `),
                    scaledSize: new google.maps.Size(20, 20),
                    anchor: new google.maps.Point(10, 10)
                },
                title: tiang.name || 'Tiang'
            });

            markers[`tiang_${tiang.id}`] = marker;
        }

        function handleMapClick(event) {
            const lat = event.latLng.lat();
            const lng = event.latLng.lng();

            @this.call('handleMapClick', lat, lng);
        }

        // Listen to Livewire events
        window.addEventListener('map-mode-changed', (e) => {
            currentMode = e.detail.mode;
            if (currentMode === 'add-pop' || currentMode === 'add-odp' || currentMode === 'add-client') {
                map.setOptions({ cursor: 'crosshair' });
            } else if (currentMode === 'add-cable') {
                // Enable selection mode
                map.setOptions({ cursor: 'pointer' });
            } else if (currentMode === 'add-tiang') {
                map.setOptions({ cursor: 'crosshair' });
            } else {
                map.setOptions({ cursor: '' });
            }
        });

        // Initialize map when page loads
        window.addEventListener('load', initMap);
    </script>
    @endpush
</x-filament-panels::page>
