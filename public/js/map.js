// Google Maps initialization script
// This file is loaded separately to avoid Livewire multiple root elements error

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
let mapInitialized = false;

// Helper function to get Livewire component
function getLivewireComponent() {
    if (window.livewireComponent && typeof window.livewireComponent.call === 'function') {
        return window.livewireComponent;
    }
    
    if (typeof Livewire !== 'undefined') {
        const components = Livewire.all();
        if (components && components.length > 0) {
            return components[0];
        }
    }
    
    return null;
}

// Load Google Maps API
function loadGoogleMapsAPI(apiKey) {
    if (!apiKey) {
        console.error('Google Maps API key is missing');
        return;
    }
    
    // Check if already loaded
    if (window.google && window.google.maps) {
        if (typeof window.initGoogleMap === 'function') {
            window.initGoogleMap();
        }
        return;
    }
    
    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=geometry,drawing,places&callback=initGoogleMap`;
    script.async = true;
    script.defer = true;
    script.onerror = function() {
        console.error('Failed to load Google Maps API');
        const mapElement = document.getElementById('map');
        if (mapElement) {
            mapElement.innerHTML = '<div class="flex items-center justify-center h-full"><div class="p-6 bg-red-100 border-2 border-red-400 text-red-800 rounded-lg"><p class="font-bold">Error: Google Maps API gagal dimuat</p><p class="text-sm mt-2">Periksa API key dan koneksi internet</p></div></div>';
        }
    };
    document.head.appendChild(script);
}

// Callback function untuk Google Maps API
window.initGoogleMap = function() {
    if (mapInitialized) return;
    mapInitialized = true;
    initMap();
};

function initMap() {
    const mapElement = document.getElementById('map');
    if (!mapElement) {
        console.error('Map element not found');
        return;
    }
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
    const component = getLivewireComponent();
    if (!component) {
        console.warn('Livewire component not available');
        return;
    }
    
    component.call('getRouters').then(routers => {
        if (routers && Array.isArray(routers)) {
            routers.forEach(router => {
                createRouterMarker(router);
            });
        }
    }).catch(err => {
        console.error('Error loading routers:', err);
    });
}

function loadOdps() {
    const component = getLivewireComponent();
    if (!component) {
        console.warn('Livewire component not available');
        return;
    }
    
    component.call('getOdps').then(odps => {
        if (odps && Array.isArray(odps)) {
            odps.forEach(odp => {
                createOdpMarker(odp);
            });
        }
    }).catch(err => {
        console.error('Error loading ODPs:', err);
    });
}

function loadCables() {
    const component = getLivewireComponent();
    if (!component) {
        console.warn('Livewire component not available');
        return;
    }
    
    component.call('getCables').then(cables => {
        if (cables && Array.isArray(cables)) {
            cables.forEach(cable => {
                createCablePolyline(cable);
            });
        }
    }).catch(err => {
        console.error('Error loading cables:', err);
    });
}

function loadClients() {
    const component = getLivewireComponent();
    if (!component) {
        console.warn('Livewire component not available');
        return;
    }
    
    component.call('getClients').then(clients => {
        if (clients && Array.isArray(clients)) {
            clients.forEach(client => {
                createClientMarker(client);
            });
        }
    }).catch(err => {
        console.error('Error loading clients:', err);
    });
}

function loadTiangs() {
    const component = getLivewireComponent();
    if (!component) {
        console.warn('Livewire component not available');
        return;
    }
    
    component.call('getTiangs').then(tiangs => {
        if (tiangs && Array.isArray(tiangs)) {
            tiangs.forEach(tiang => {
                createTiangMarker(tiang);
            });
        }
    }).catch(err => {
        console.error('Error loading tiangs:', err);
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
        const component = getLivewireComponent();
        if (component) {
            component.call('selectOdp', odp.id).catch(err => {
                console.error('Error selecting ODP:', err);
            });
        }
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
        const component = getLivewireComponent();
        if (component) {
            component.call('editCable', cable.id).catch(err => {
                console.error('Error editing cable:', err);
            });
        }
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
    if (!event || !event.latLng) return;
    const lat = event.latLng.lat();
    const lng = event.latLng.lng();

    const component = getLivewireComponent();
    if (component) {
        component.call('handleMapClick', lat, lng).catch(err => {
            console.error('Error handling map click:', err);
        });
    }
}

// Listen to Livewire events
window.addEventListener('map-mode-changed', (e) => {
    if (!map) return;
    currentMode = e.detail.mode;
    if (currentMode === 'add-pop' || currentMode === 'add-odp' || currentMode === 'add-client') {
        map.setOptions({ cursor: 'crosshair' });
    } else if (currentMode === 'add-cable') {
        map.setOptions({ cursor: 'pointer' });
    } else if (currentMode === 'add-tiang') {
        map.setOptions({ cursor: 'crosshair' });
    } else {
        map.setOptions({ cursor: '' });
    }
});

// Error handling jika Google Maps gagal load
window.addEventListener('error', function(e) {
    if (e.target && e.target.src && e.target.src.includes('maps.googleapis.com')) {
        console.error('Google Maps API failed to load. Check your API key.');
        const mapElement = document.getElementById('map');
        if (mapElement) {
            mapElement.innerHTML = '<div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded"><p class="font-bold">Error loading Google Maps</p><p class="text-sm mt-2">Please check your GOOGLE_MAPS_API_KEY in .env file</p></div>';
        }
    }
}, true);
