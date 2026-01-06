// Configuration Constants
const ROUTE_UPDATE_DISTANCE_M = 30; // Min distance to trigger a major route re-calc
const REROUTE_DEVIATION_METERS = 25; // Reroute if driver is >25m away from polyline
const MIN_MOVE_TO_UPDATE_M = 1.5;   // Ignore micro-jitters
const MAX_ACCEPTABLE_JUMP_M = 60;   // Ignore GPS spikes

class DriverNavigation {
    constructor() {
        this.map = null;
        this.driverMarker = null;
        this.customerMarker = null;
        this.currentRoute = null;
        this.watchId = null;

        this.driverCoords = null;
        this.customerCoords = window.deliveryData.customer; // From PHP
        this.orsKey = window.deliveryData.orsKey; // From PHP (.env)

        this.isFollowMode = true;
        this.isMapInteracting = false;

        this.init();
    }

    init() {
        if (!this.customerCoords || !this.customerCoords.lat) {
            console.warn("No active delivery coordinates found.");
            return;
        }

        // Initialize MapLibre
        this.map = new maplibregl.Map({
            container: 'deliveryMap',
            style: 'https://basemaps.cartocdn.com/gl/positron-gl-style/style.json',
            center: [this.customerCoords.lng, this.customerCoords.lat],
            zoom: 14,
            pitch: 45,
            attributionControl: false
        });

        this.map.on('load', () => {
            this.setupMarkers();
            this.startTracking();
            this.setupUIListeners();

            // Initial path fit
            if (this.driverCoords) {
                this.getRoute();
            }
        });

        // Detect user interaction to disable follow mode
        this.map.on('dragstart', () => {
            if (this.isFollowMode) {
                this.setFollowMode(false);
            }
        });
    }

    setupMarkers() {
        // Customer Destination Marker
        const customerEl = document.createElement('div');
        customerEl.className = 'customer-marker';
        customerEl.innerHTML = '<i class="ph-fill ph-map-pin-fill"></i>';

        this.customerMarker = new maplibregl.Marker(customerEl)
            .setLngLat([this.customerCoords.lng, this.customerCoords.lat])
            .addTo(this.map);

        // Driver Marker (Pointer)
        const driverEl = document.createElement('div');
        driverEl.className = 'driver-marker';

        this.driverMarker = new maplibregl.Marker({
            element: driverEl,
            rotationAlignment: 'viewport', // Marker stays UP relative to screen
            pitchAlignment: 'viewport'
        })
            .setLngLat([this.customerCoords.lng, this.customerCoords.lat - 0.001]) // Start slightly offset
            .addTo(this.map);
    }

    setupUIListeners() {
        document.getElementById('toggleFollowBtn').addEventListener('click', () => {
            this.setFollowMode(!this.isFollowMode);
        });

        document.getElementById('recenterBtn').addEventListener('click', () => {
            const btn = document.getElementById('recenterBtn');
            const icon = btn.querySelector('i');

            // Visual feedback
            icon.classList.add('ph-spin');

            // Force a high-accuracy check
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    icon.classList.remove('ph-spin');
                    console.log("Manual GPS refresh successful.");

                    this.onLocationUpdate(pos, true); // True = forced

                    this.map.easeTo({
                        center: [pos.coords.longitude, pos.coords.latitude],
                        zoom: 25,
                        duration: 1000
                    });
                    this.setFollowMode(true);
                },
                (err) => {
                    icon.classList.remove('ph-spin');
                    console.error("Manual GPS refresh failed:", err);
                    // Fallback to last known if possible
                    if (this.driverCoords) {
                        this.map.easeTo({
                            center: [this.driverCoords.lng, this.driverCoords.lat],
                            zoom: 17,
                            duration: 1000
                        });
                        this.setFollowMode(true);
                    }
                },
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
            );
        });
    }

    setFollowMode(state) {
        this.isFollowMode = state;
        const btn = document.getElementById('toggleFollowBtn');
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
            alert("Geolocation is not supported by your browser.");
            return;
        }

        this.watchId = navigator.geolocation.watchPosition(
            (pos) => {
                console.log("GPS Position received:", pos.coords.latitude, pos.coords.longitude);
                this.onLocationUpdate(pos);
            },
            (err) => console.error("GPS Error:", err),
            {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0
            }
        );

        // Dummy update for immediate visibility if needed
        setTimeout(() => {
            if (!this.driverCoords) {
                console.log("No GPS yet, using customer-relative offset for initial view.");
            }
        }, 3000);
    }

    onLocationUpdate(position, isForced = false) {
        const { latitude, longitude, heading, speed } = position.coords;
        const newCoords = { lat: latitude, lng: longitude };

        // 1. Initial coords setup
        if (!this.driverCoords) {
            console.log("First GPS lock acquired.");
            this.driverCoords = newCoords;
            this.animateMarker(newCoords);
            this.getRoute();
            return;
        }

        // 2. Filter micro-movements (unless forced by user)
        const dist = this.getDistance(this.driverCoords, newCoords);
        console.log(`Movement detected: ${dist.toFixed(2)}m (Forced: ${isForced})`);
        if (!isForced && dist < MIN_MOVE_TO_UPDATE_M) return;
        if (dist > MAX_ACCEPTABLE_JUMP_M) return;

        // 3. Update Position Tracking
        this.driverCoords = newCoords;

        // 4. Cubic Smoothing Move
        this.animateMarker(newCoords);

        // 5. Rotation Logic (Heading or Vector)
        if (heading !== null) {
            this.rotateMarker(heading);
        }

        // 6. Rerouting Logic
        if (this.currentRoute) {
            const deviation = this.getDeviationFromRoute(newCoords, this.currentRoute);
            if (deviation > REROUTE_DEVIATION_METERS) {
                console.log("Deviation detected (" + deviation.toFixed(1) + "m). Rerouting...");
                this.getRoute();
            }
        }

        // 7. Auto-pan / Level following (Heading Up Mode)
        // 7. Auto-pan / Level following (Heading Up Mode)
        if (this.isFollowMode) {
            // In Heading Up mode, we rotate the MAP so that the driver's heading is 0 (UP)
            // But MapLibre bearing is counter-clockwise, so we use -heading
            this.map.easeTo({
                center: [longitude, latitude],
                bearing: heading !== null ? -heading : this.map.getBearing(),
                pitch: 45,
                duration: 500,
                easing: (t) => t * (2 - t)
            });
        }
    }

    animateMarker(target) {
        // Simple cubic transition for visual marker
        // In a real app we'd use requestAnimationFrame for true per-frame interp
        this.driverMarker.setLngLat([target.lng, target.lat]);
    }

    rotateMarker(bearing) {
        // We keep the rider icon always facing forward (Up on the phone)
        // The map bearing handles the orientation relative to the path
    }

    async getRoute() {
        if (!this.driverCoords || !this.customerCoords || !this.orsKey) {
            console.warn("Missing coordinates or API key for routing.");
            return;
        }

        const url = `https://api.openrouteservice.org/v2/directions/driving-car?api_key=${this.orsKey}&start=${this.driverCoords.lng},${this.driverCoords.lat}&end=${this.customerCoords.lng},${this.customerCoords.lat}`;

        try {
            console.log("Fetching route from ORS...");
            const response = await fetch(url);
            const data = await response.json();

            if (data.features && data.features.length > 0) {
                console.log("Route received successfully.");
                this.drawRoute(data.features[0]);
                this.updateDirections(data.features[0].properties.segments[0]);
            } else {
                console.warn("ORS returned no features:", data);
            }
        } catch (error) {
            console.error("Routing Error:", error);
        }
    }

    drawRoute(routeFeature) {
        this.currentRoute = routeFeature.geometry.coordinates;

        // Add or Update Layer
        if (this.map.getSource('route')) {
            this.map.getSource('route').setData(routeFeature);
        } else {
            this.map.addSource('route', {
                type: 'geojson',
                data: routeFeature
            });

            this.map.addLayer({
                id: 'route-layer',
                type: 'line',
                source: 'route',
                layout: {
                    'line-join': 'round',
                    'line-cap': 'round'
                },
                paint: {
                    'line-color': '#4a90e2',
                    'line-width': 6,
                    'line-opacity': 0.8
                }
            });
        }

        // Fit bounds for first route only
        if (!this.initialFit) {
            const bounds = new maplibregl.LngLatBounds();
            this.currentRoute.forEach(c => bounds.extend(c));
            this.map.fitBounds(bounds, { padding: 50 });
            this.initialFit = true;
        }
    }

    updateDirections(segment) {
        const panel = document.getElementById('directionsPanel');
        const instructionEl = document.getElementById('stepInstruction');
        const distanceEl = document.getElementById('stepDistance');

        if (segment.steps && segment.steps.length > 0) {
            panel.classList.remove('d-none');
            const currentStep = segment.steps[0];
            instructionEl.innerText = currentStep.instruction;
            distanceEl.innerText = `${Math.round(currentStep.distance)} meters away`;
        }
    }

    // Haversine helper
    getDistance(c1, c2) {
        const R = 6371e3;
        const φ1 = c1.lat * Math.PI / 180;
        const φ2 = c2.lat * Math.PI / 180;
        const Δφ = (c2.lat - c1.lat) * Math.PI / 180;
        const Δλ = (c2.lng - c1.lng) * Math.PI / 180;
        const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
            Math.cos(φ1) * Math.cos(φ2) *
            Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    getDeviationFromRoute(pt, polyline) {
        // Find minDist from point to any segment in polyline
        let minDist = Infinity;
        for (let i = 0; i < polyline.length - 1; i++) {
            const d = this.distToSegment(pt, { lng: polyline[i][0], lat: polyline[i][1] }, { lng: polyline[i + 1][0], lat: polyline[i + 1][1] });
            if (d < minDist) minDist = d;
        }
        return minDist;
    }

    distToSegment(p, v, w) {
        const l2 = Math.pow(this.getDistance(v, w), 2);
        if (l2 == 0) return this.getDistance(p, v);
        let t = ((p.lng - v.lng) * (w.lng - v.lng) + (p.lat - v.lat) * (w.lat - v.lat)) / l2;
        t = Math.max(0, Math.min(1, t));
        return this.getDistance(p, {
            lng: v.lng + t * (w.lng - v.lng),
            lat: v.lat + t * (w.lat - v.lat)
        });
    }
}

// Global instance
document.addEventListener('DOMContentLoaded', () => {
    window.navSystem = new DriverNavigation();
});
