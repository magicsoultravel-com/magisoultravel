let currentSlide = 0;
let carouselGroups = [];
let carouselInner;
let mapObjects = {}; // Stores Google Map instances, keyed by map ID

// Function to parse GPX data and render on map
function parseGPX(xmlDoc, map) {
    let trackPoints = [];
    const trackSegments = xmlDoc.getElementsByTagName('trkseg');
    for (let i = 0; i < trackSegments.length; i++) {
        const trackPs = trackSegments[i].getElementsByTagName('trkpt');
        for (let j = 0; j < trackPs.length; j++) {
            const lat = parseFloat(trackPs[j].getAttribute('lat'));
            const lon = parseFloat(trackPs[j].getAttribute('lon'));
            trackPoints.push({ lat: lat, lng: lon });
        }
    }

    if (trackPoints.length > 0) {
        const trackPath = new google.maps.Polyline({
            path: trackPoints,
            geodesic: true,
            strokeColor: '#FF0000',
            strokeOpacity: 1.0,
            strokeWeight: 4
        });
        trackPath.setMap(map);

        const bounds = new google.maps.LatLngBounds();
        for (let i = 0; i < trackPoints.length; i++) {
            bounds.extend(trackPoints[i]);
        }
        map.fitBounds(bounds);
    }

    const waypoints = xmlDoc.getElementsByTagName('wpt');
    for (let i = 0; i < waypoints.length; i++) {
        const lat = parseFloat(waypoints[i].getAttribute('lat'));
        const lon = parseFloat(waypoints[i].getAttribute('lon'));
        const nameElement = waypoints[i].getElementsByTagName('name')[0];
        const name = nameElement ? nameElement.textContent : 'Waypoint';

        new google.maps.Marker({
            position: { lat: lat, lng: lon },
            map: map,
            title: name
        });
    }
}

// THIS IS THE CRUCIAL FUNCTION FOR LAZY LOADING
// It now creates the map if it doesn't exist, and loads GPX data.
// If the map already exists, it just triggers a resize.
function loadAndRenderGPXMap(mapId, gpxFilePath, defaultZoom, defaultCenterLat, defaultCenterLng) {
    const mapElement = document.getElementById(mapId);
    if (!mapElement) {
        console.error(`Map element with ID ${mapId} not found.`);
        return;
    }

    // Check if the map has already been initialized
    if (!mapObjects[mapId]) {
        // Initialize the map if it doesn't exist
        const mapOptions = {
            zoom: defaultZoom ? parseInt(defaultZoom) : 2,
            center: (defaultCenterLat && defaultCenterLng) ?
                { lat: parseFloat(defaultCenterLat), lng: parseFloat(defaultCenterLng) } :
                { lat: 0, lng: 0 },
            mapTypeId: google.maps.MapTypeId.HYBRID
        };

        const map = new google.maps.Map(mapElement, mapOptions);
        mapObjects[mapId] = map; // Store the map instance

        // Fetch and parse GPX data
        fetch(gpxFilePath)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(xmlString => {
                const parser = new DOMParser();
                const xmlDoc = parser.parseFromString(xmlString, "text/xml");
                parseGPX(xmlDoc, mapObjects[mapId]); // Pass the stored map instance
            })
            .catch(error => {
                console.error(`Error loading or parsing GPX file for ${mapId}:`, error);
                mapElement.innerHTML = `<p style="color: red;">Error loading map for ${gpxFilePath}.</p>`;
            });
    } else {
        // If map already exists, trigger a resize (important for hidden maps that become visible)
        if (google.maps.event) {
            google.maps.event.trigger(mapObjects[mapId], 'resize');
        }
    }
}

function showSlide(index) {
    if (carouselGroups.length === 0) return;

    if (index < 0) {
        currentSlide = carouselGroups.length - 1;
    } else if (index >= carouselGroups.length) {
        currentSlide = 0;
    } else {
        currentSlide = index;
    }

    const groupWidth = carouselGroups[0].offsetWidth;
    const offset = -currentSlide * groupWidth;
    carouselInner.style.transform = `translateX(${offset}px)`;

    // Lazy load maps for the newly active group
    const activeGroup = carouselGroups[currentSlide];
    activeGroup.querySelectorAll('.gpx-map').forEach(mapDiv => {
        const mapId = mapDiv.id;
        const mapWrapper = mapDiv.closest('.carousel-item');
        const gpxPathDiv = mapWrapper ? mapWrapper.querySelector('.gpx-file-path') : null;

        if (gpxPathDiv && gpxPathDiv.classList.contains('gpx-file-path')) {
            const gpxFilePath = gpxPathDiv.dataset.gpxPath;
            const defaultZoom = gpxPathDiv.dataset.defaultZoom;
            const defaultCenterLat = gpxPathDiv.dataset.defaultCenterLat;
            const defaultCenterLng = gpxPathDiv.dataset.defaultCenterLng;

            if (gpxFilePath) {
                // Use the refactored function to load/render this map
                loadAndRenderGPXMap(mapId, gpxFilePath, defaultZoom, defaultCenterLat, defaultCenterLng);
            }
        }
    });

    // Optional: Preload maps for adjacent groups for smoother transitions
    const preloadAhead = 1; // Number of groups to preload ahead
    for (let i = 1; i <= preloadAhead; i++) {
        const preloadIndex = (currentSlide + i) % carouselGroups.length;
        // Ensure the preloadIndex doesn't exceed the number of groups
        if (carouselGroups[preloadIndex]) {
            carouselGroups[preloadIndex].querySelectorAll('.gpx-map').forEach(mapDiv => {
                const mapId = mapDiv.id;
                const mapWrapper = mapDiv.closest('.carousel-item');
                const gpxPathDiv = mapWrapper ? mapWrapper.querySelector('.gpx-file-path') : null;

                if (gpxPathDiv && gpxPathDiv.classList.contains('gpx-file-path')) {
                    const gpxFilePath = gpxPathDiv.dataset.gpxPath;
                    const defaultZoom = gpxPathDiv.dataset.defaultZoom;
                    const defaultCenterLat = gpxPathDiv.dataset.defaultCenterLat;
                    const defaultCenterLng = gpxPathDiv.dataset.defaultCenterLng;

                    if (gpxFilePath) {
                        // Call loadAndRenderGPXMap; it will only initialize if not already done
                        loadAndRenderGPXMap(mapId, gpxFilePath, defaultZoom, defaultCenterLat, defaultCenterLng);
                    }
                }
            });
        }
    }
}


function initGPXMaps() {
    if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
        console.warn("Google Maps API not yet loaded. Retrying initGPXMaps...");
        setTimeout(initGPXMaps, 500);
        return;
    }

    carouselInner = document.querySelector('.gpx-maps-carousel-inner');
    carouselGroups = document.querySelectorAll('.gpx-maps-carousel-inner .gpx-maps-carousel-group');
    const prevBtn = document.querySelector('.prev-btn');
    const nextBtn = document.querySelector('.next-btn');

    if (!carouselInner || carouselGroups.length === 0 || !prevBtn || !nextBtn) {
        console.warn("Carousel elements not found or no groups. GPX Maps Carousel not initialized.");
        return;
    }

    prevBtn.addEventListener('click', () => showSlide(currentSlide - 1));
    nextBtn.addEventListener('click', () => showSlide(currentSlide + 1));

    // Initialize only the maps in the *first* slide/group initially
    // showSlide(0) will handle the initial loading of visible maps
    showSlide(0);
}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
        initGPXMaps();
    } else {
        // If Google Maps API isn't loaded yet, it relies on the 'callback' parameter
        // in the script URL in header.php to call window.initGPXMapsCallback
        window.initGPXMapsCallback = initGPXMaps;
    }
});