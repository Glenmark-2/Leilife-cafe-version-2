// Configuration Constants
const REROUTE_DEVIATION_METERS = 20;
const MIN_MOVE_TO_UPDATE_M = 0.5;   // More sensitive for people standing still
const MAX_ACCEPTABLE_JUMP_M = 1000;  // Be more forgiving about initial GPS lock jumps
const SNAP_THRESHOLD_M = 15;

class DriverNavigation {
    constructor() {
        this.map = null;
        this.driverMarker = null;
        this.customerMarker = null;
        this.currentRoute = null;
        this.watchId = null;
        this.gpsWatchdog = null;

        this.driverCoords = null;
        this.lastHeading = 0;
        this.initialFitDone = false;

        this.customerCoords = window.deliveryData.customer;
        this.isFollowMode = true;

        if (!this.customerCoords || (!this.customerCoords.lat && this.customerCoords.lat !== 0)) {
            console.error("MAP SYSTEM: No destination coords provided.");
            return;
        }

        this.init();
    }

    init() {
        // Initialize MapLibre
        this.map = new maplibregl.Map({
            container: 'deliveryMap',
            style: 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json',
            center: [this.customerCoords.lng, this.customerCoords.lat],
            zoom: 17,
            pitch: 60,
            attributionControl: false
        });

        this.map.on('load', () => {
            console.log("MAP SYSTEM: Map loaded. Initializing tracking...");
            this.setupMarkers();
            this.setupUIListeners();
            this.startTracking();
        });

        this.map.on('dragstart', () => {
            if (this.isFollowMode) {
                console.log("MAP SYSTEM: Follow mode disabled by user interaction.");
                this.setFollowMode(false);
            }
        });
    }

    setupMarkers() {
        // Customer Marker
        const customerEl = document.createElement('div');
        customerEl.className = 'customer-marker';
        customerEl.innerHTML = '<i class="ph-fill ph-map-pin-fill"></i>';

        this.customerMarker = new maplibregl.Marker(customerEl)
            .setLngLat([this.customerCoords.lng, this.customerCoords.lat])
            .addTo(this.map);

        // Driver Marker
        const driverEl = document.createElement('div');
        driverEl.className = 'driver-marker';

        this.driverMarker = new maplibregl.Marker({
            element: driverEl,
            rotationAlignment: 'map',
            pitchAlignment: 'map'
        })
            .setLngLat([this.customerCoords.lng, this.customerCoords.lat])
            .addTo(this.map);
    }

    setupUIListeners() {
        const followBtn = document.getElementById('toggleFollowBtn');
        if (followBtn) {
            followBtn.addEventListener('click', () => {
                this.setFollowMode(!this.isFollowMode);
                if (this.isFollowMode && this.driverCoords) {
                    this.updateMapCamera(this.driverCoords, this.lastHeading);
                }
            });
        }

        const recenterBtn = document.getElementById('recenterBtn');
        if (recenterBtn) {
            recenterBtn.addEventListener('click', () => {
                if (this.driverCoords) {
                    this.updateMapCamera(this.driverCoords, this.lastHeading, true);
                    this.setFollowMode(true);
                } else {
                    console.warn("MAP SYSTEM: Recenter clicked but no GPS lock.");
                }
            });
        }
    }

    setFollowMode(state) {
        this.isFollowMode = state;
        const btn = document.getElementById('toggleFollowBtn');
        if (!btn) return;
        if (state) {
            btn.classList.add('active', 'btn-primary');
            btn.classList.remove('btn-white');
        } else {
            btn.classList.remove('active', 'btn-primary');
            btn.classList.add('btn-white');
        }
    }

    startTracking() {
        if (!navigator.geolocation) {
            console.error("MAP SYSTEM: Geolocation is not supported.");
            return;
        }

        this.resetWatchdog();

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                console.log("MAP SYSTEM: Initial GPS fix.");
                this.onLocationUpdate(pos);
            },
            (err) => console.warn("MAP SYSTEM: Initial fix failed: " + err.message),
            { enableHighAccuracy: false, timeout: 5000 }
        );

        this.watchId = navigator.geolocation.watchPosition(
            (pos) => {
                this.resetWatchdog();
                this.onLocationUpdate(pos);
            },
            (err) => {
                console.error("MAP SYSTEM: GPS Error (" + err.code + "): " + err.message);
                if (err.code === 1) alert("Please enable Location Services to use the map.");
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 1000
            }
        );
    }

    resetWatchdog() {
        if (this.gpsWatchdog) clearTimeout(this.gpsWatchdog);
        this.gpsWatchdog = setTimeout(() => {
            console.log("MAP SYSTEM: GPS stalled, restarting watch...");
            navigator.geolocation.clearWatch(this.watchId);
            this.startTracking();
        }, 20000);
    }

    onLocationUpdate(position) {
        const { latitude, longitude, heading, accuracy } = position.coords;
        const newCoords = { lat: latitude, lng: longitude };

        console.log(`MAP SYSTEM: Update | Accuracy: ${accuracy.toFixed(1)}m | Lat: ${latitude} | Lng: ${longitude}`);

        if (!this.driverCoords) {
            this.driverCoords = newCoords;
            this.driverMarker.setLngLat([longitude, latitude]);
            this.updateMapCamera(newCoords, heading || 0, true);
            this.getRoute();
            return;
        }

        const dist = this.getDistance(this.driverCoords, newCoords);

        if (dist < MIN_MOVE_TO_UPDATE_M && this.currentRoute) return;

        if (dist > MAX_ACCEPTABLE_JUMP_M) {
            console.warn("MAP SYSTEM: GPS Jump ignored (" + dist.toFixed(0) + "m).");
            return;
        }

        let effectiveHeading = heading;
        if (effectiveHeading === null || effectiveHeading === undefined) {
            effectiveHeading = this.calculateBearing(this.driverCoords, newCoords);
        }
        this.lastHeading = effectiveHeading;

        let visualPos = newCoords;
        if (this.currentRoute) {
            const snapResult = this.getSnapPosition(newCoords, this.currentRoute);
            if (snapResult.distance < SNAP_THRESHOLD_M) {
                visualPos = snapResult.point;
            }
        }

        this.driverMarker.setLngLat([visualPos.lng, visualPos.lat]);
        this.driverMarker.setRotation(effectiveHeading);

        if (this.isFollowMode) {
            this.updateMapCamera(visualPos, effectiveHeading);
        }

        if (this.currentRoute) {
            const deviation = this.getDeviationFromRoute(newCoords, this.currentRoute);
            if (deviation > REROUTE_DEVIATION_METERS) {
                console.log("MAP SYSTEM: Deviation detected (" + deviation.toFixed(0) + "m). Requesting new route.");
                this.getRoute();
            }
        } else {
            this.getRoute();
        }

        this.driverCoords = newCoords;
    }

    updateMapCamera(coords, heading, instant = false) {
        if (!this.map) return;
        this.map.easeTo({
            center: [coords.lng, coords.lat],
            bearing: heading || 0,
            pitch: 60,
            duration: instant ? 0 : 1200,
            easing: (t) => t
        });
    }

    async getRoute() {
        if (!this.driverCoords || !this.customerCoords) {
            return;
        }

        // Use backend proxy to avoid CORS and hide API key
        const start = `${this.driverCoords.lng},${this.driverCoords.lat}`;
        const end = `${this.customerCoords.lng},${this.customerCoords.lat}`;
        const url = `../backend/api/proxy_route.php?start=${start}&end=${end}`;

        try {
            const response = await fetch(url);
            if (!response.ok) {
                const errData = await response.json();
                throw new Error(errData.error || "Route proxy failure");
            }
            const data = await response.json();

            if (data.features && data.features.length > 0) {
                this.drawRoute(data.features[0]);
                this.updateDirections(data.features[0].properties.segments[0]);
            } else {
                console.warn("MAP SYSTEM: Proxy returned no route features.");
            }
        } catch (e) {
            console.error("MAP SYSTEM: Routing Proxy Request Failed: ", e.message);
        }
    }

    drawRoute(routeFeature) {
        this.currentRoute = routeFeature.geometry.coordinates;

        const sourceId = 'route';
        const layerId = 'route-layer';

        if (this.map.getSource(sourceId)) {
            this.map.getSource(sourceId).setData(routeFeature);
        } else {
            this.map.addSource(sourceId, { type: 'geojson', data: routeFeature });
            this.map.addLayer({
                id: layerId,
                type: 'line',
                source: sourceId,
                layout: { 'line-join': 'round', 'line-cap': 'round' },
                paint: { 'line-color': '#007AFF', 'line-width': 8, 'line-opacity': 0.8 }
            });
        }

        if (!this.initialFitDone) {
            const bounds = new maplibregl.LngLatBounds();
            this.currentRoute.forEach(c => bounds.extend(c));
            this.map.fitBounds(bounds, { padding: 80, duration: 2000 });
            this.initialFitDone = true;
        }
    }

    updateDirections(segment) {
        const instructionEl = document.getElementById('stepInstruction');
        const distanceEl = document.getElementById('stepDistance');

        if (instructionEl && segment.steps && segment.steps.length > 0) {
            const panel = document.getElementById('directionsPanel');
            if (panel) panel.classList.remove('d-none');
            const step = segment.steps[0];
            instructionEl.innerHTML = `<span class="text-white-50 small">Next:</span> <br> ${step.instruction}`;
            distanceEl.innerText = `${Math.round(step.distance)}m away`;
        }
    }

    getSnapPosition(pt, polyline) {
        let bestPoint = pt, minDistance = Infinity;
        for (let i = 0; i < polyline.length - 1; i++) {
            const v = { lng: polyline[i][0], lat: polyline[i][1] };
            const w = { lng: polyline[i + 1][0], lat: polyline[i + 1][1] };
            const l2 = Math.pow(this.getDistance(v, w), 2);
            if (l2 === 0) continue;
            let t = ((pt.lng - v.lng) * (w.lng - v.lng) + (pt.lat - v.lat) * (w.lat - v.lat)) / l2;
            t = Math.max(0, Math.min(1, t));
            const snap = { lng: v.lng + t * (w.lng - v.lng), lat: v.lat + t * (w.lat - v.lat) };
            const dist = this.getDistance(pt, snap);
            if (dist < minDistance) { minDistance = dist; bestPoint = snap; }
        }
        return { point: bestPoint, distance: minDistance };
    }

    calculateBearing(p1, p2) {
        const dLon = (p2.lng - p1.lng) * Math.PI / 180;
        const lat1 = p1.lat * Math.PI / 180, lat2 = p2.lat * Math.PI / 180;
        const y = Math.sin(dLon) * Math.cos(lat2);
        const x = Math.cos(lat1) * Math.sin(lat2) - Math.sin(lat1) * Math.cos(lat2) * Math.cos(dLon);
        return (Math.atan2(y, x) * 180 / Math.PI + 360) % 360;
    }

    getDistance(c1, c2) {
        const R = 6371e3;
        const φ1 = c1.lat * Math.PI / 180, φ2 = c2.lat * Math.PI / 180;
        const Δφ = (c2.lat - c1.lat) * Math.PI / 180, Δλ = (c2.lng - c1.lng) * Math.PI / 180;
        const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) + Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
        return R * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)));
    }

    getDeviationFromRoute(pt, polyline) {
        return this.getSnapPosition(pt, polyline).distance;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.navSystem = new DriverNavigation();
});
