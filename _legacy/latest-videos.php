<?php
// templates/gpx-maps-module.php


$gpxDirectory = '/assets/gpx/';
$fullGpxServerPath = __DIR__ . '/..' . $gpxDirectory;
$gpxMetadataFilePath = __DIR__ . '/../content/gpx-maps-meta.json';


$gpxFiles = [];
$gpxMetadata = [];

// 1. Load GPX metadata from the JSON file
if (file_exists($gpxMetadataFilePath) && is_readable($gpxMetadataFilePath)) {
    $metadataContent = file_get_contents($gpxMetadataFilePath);
    $gpxMetadata = json_decode($metadataContent, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error parsing gpx-maps-meta.json: " . json_last_error_msg());
        $gpxMetadata = []; // Fallback to empty array if parsing fails
    }
} else {
}

// 2. Scan for GPX files and associate with metadata
if (is_dir($fullGpxServerPath)) {
    if ($dh = opendir($fullGpxServerPath)) {
        while (($file = readdir($dh)) !== false) {
            // Check if it's a file and ends with .gpx (case-insensitive)
            if (is_file($fullGpxServerPath . $file) && pathinfo($file, PATHINFO_EXTENSION) == 'gpx') {
                $file_id = pathinfo($file, PATHINFO_FILENAME); // Get filename without extension
                $clean_id = preg_replace('/[^a-zA-Z0-9_]/', '', $file_id); // Sanitize for HTML ID

                // Get metadata for this file, or fallback to default generated from filename
               
                $meta = $gpxMetadata[$file] ?? [];

                $display_title = $meta['title'] ?? ucwords(str_replace(['_', '-'], ' ', $file_id));
                $description = $meta['description'] ?? '';
                $activity = $meta['activity'] ?? '';
                $date = $meta['date'] ?? '';
                $default_zoom = $meta['default_zoom'] ?? ''; // Passed to JS
                $default_center_lat = $meta['default_center']['lat'] ?? ''; // Passed to JS
                $default_center_lng = $meta['default_center']['lng'] ?? ''; // Passed to JS
                $related_vid = $meta['related_vid'] ?? ''; // Get related video URL (can be comma-separated)
                $trip_epic = $meta['trip_epic'] ?? '';

                $gpxFiles[$clean_id] = [
                    'path' => $gpxDirectory . $file,
                    'title' => $display_title,
                    'description' => $description,
                    'activity' => $activity,
                    'date' => $date,
                    'default_zoom' => $default_zoom,
                    'default_center_lat' => $default_center_lat,
                    'default_center_lng' => $default_center_lng,
                    'related_vid' => $related_vid,
                    'trip_epic' => $trip_epic,
                ];
            }
        }
        closedir($dh);
    } else {
        error_log("Error: Could not open GPX directory: " . $fullGpxServerPath);
        echo '<p style="color: red;">Error: Could not read GPX files directory.</p>';
    }
} else {
    error_log("Error: GPX directory not found or not readable: " . $fullGpxServerPath);
    echo '<p style="color: red;">Error: GPX directory not found.</p>';
}

// Check if any GPX files were found
if (empty($gpxFiles)) {
    echo '<p>No GPX files found in the specified directory: <code>' . htmlspecialchars($gpxDirectory) . '</code></p>';
}

// This module assumes the Google Maps API is loaded in header.php
?>

<section class="section gpx-maps-section">
    <h2>trips</h2>
click title to expand
    <div class="gpx-maps-carousel-container">
        <button class="carousel-control prev-btn">&lt;</button>
        <div class="gpx-maps-carousel-inner">
            <?php
            $gpxFileCount = 0;
            $itemsPerGroup = 2; // Define how many items per group/slide
            foreach ($gpxFiles as $id => $data):
                if ($gpxFileCount % $itemsPerGroup === 0) {
                    // Start a new carousel group every $itemsPerGroup items
                    echo '<div class="gpx-maps-carousel-group">';
                }
            ?>
                    <div class="map-wrapper carousel-item" data-map-id="map-<?php echo htmlspecialchars($id); ?>">
<h3>
    <a href="templates/gpx-maps-lander.php?id=<?php echo htmlspecialchars($id); ?>" target="_blank">
        <?php echo htmlspecialchars($data['title']); ?>
    </a>
</h3>
                        <?php if (!empty($data['description'])): ?>
                            <p class="gpx-description"><?php echo htmlspecialchars($data['description']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($data['activity']) || !empty($data['date'])): ?>
                            <p class="gpx-meta-info">
                                <?php if (!empty($data['date'])) echo 'date: ' . htmlspecialchars($data['date']); ?>
                            </p>
                        <?php endif; ?>
                        <div id="map-<?php echo htmlspecialchars($id); ?>" class="gpx-map"></div>
                        <div class="gpx-file-path"
                            data-gpx-path="<?php echo htmlspecialchars($data['path']); ?>"
                            data-default-zoom="<?php echo htmlspecialchars($data['default_zoom']); ?>"
                            data-default-center-lat="<?php echo htmlspecialchars($data['default_center_lat']); ?>"
                            data-default-center-lng="<?php echo htmlspecialchars($data['default_center_lng']); ?>"
                        ></div>
                        <?php if (!empty($data['related_vid'])): ?>
                            <p class="gpx-meta-info">
                                related video: <br>
                                <?php
                                $video_urls = explode(',', $data['related_vid']);
                                foreach ($video_urls as $index => $video_url_raw) {
                                    $video_url = trim($video_url_raw);
                                    if (filter_var($video_url, FILTER_VALIDATE_URL)) {
                                        echo '<a href="' . htmlspecialchars($video_url) . '" target="_blank">' . htmlspecialchars($video_url) . '</a><br>';
                                    } else {
                                        echo htmlspecialchars($video_url) . '<br>';
                                    }
                                }
                                ?>
                            </p>
                            <?php if (!empty($data['trip_epic'])): ?>
                                <p class="gpx-meta-info">
                                    <?php echo nl2br(htmlspecialchars($data['trip_epic'])); ?>
                                </p>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
            <?php
                $gpxFileCount++;
                if ($gpxFileCount % $itemsPerGroup === 0 || $gpxFileCount === count($gpxFiles)) {
                    // Close the carousel group
                    echo '</div>';
                }
            endforeach;
            ?>
        </div>
        <button class="carousel-control next-btn">&gt;</button>
    </div>
</section>

<style>
 .gpx-description,
.gpx-meta-info {
    font-size: 0.9em;
    color: #555;
    margin-top: 5px;
    margin-bottom: 5px;
}

.gpx-maps-section {
    padding: 20px;
    margin-top: 20px;
    border-radius: 8px;
    /* background-color: #f9f9f9; */
}

.gpx-maps-carousel-container {
    position: relative;
    /* Max width to comfortably fit two 400px maps + 20px gap + padding for controls */
    /* Calculation: 820px (inner content) + 2 * 50px (padding for controls) = 920px */
    max-width: 920px; /* Adjusted from 1300px */
    margin: 20px auto; /* Center the entire carousel */
    overflow: hidden; /* Hide anything outside the container */
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    /* Padding to make space for the absolute-positioned carousel controls */
    padding: 0 50px;

}

.gpx-maps-carousel-inner {
    display: flex;
    transition: transform 0.5s ease-in-out; /* Smooth sliding animation */
    flex-wrap: nowrap; /* Ensure groups stay in a single horizontal line */
    /* Define the exact width for one "slide" (two items + gap) */
    width: 820px; /* Adjusted from 1220px: (400px item width * 2) + 20px gap */
    margin: 0 auto; /* Center the inner track within its container */
}

.gpx-maps-carousel-group {
    min-width: 100%; /* Each group takes the full width of the inner track (820px) */
    box-sizing: border-box; /* Include padding/border in width */
    flex-shrink: 0; /* Prevent groups from shrinking */
    display: flex; /* Make items within the group flex */
    gap: 20px; /* Space between the two maps in the grid */
    /* Removed: padding: 10px; */
    justify-content: flex-start; /* Adjusted based on previous discussion, use 'center' if preferred */
    align-items: flex-start; /* Align items to the top if they have different heights */
margin-right: 20px; /* Add some margin to the right of each group */
}

.carousel-item {
    /* Define explicit size for each map card within the flex container */
    flex: 0 0 400px; /* Adjusted from 600px: Don't grow, don't shrink, prefer 400px width */
    max-width: 400px; /* Explicit max width for safety, adjusted from 600px */
    min-width: 200px; /* A smaller min-width for better responsiveness, adjust if needed */
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 10px;
background-color: #1a1a1a
}

.gpx-map {
    width: 100%;
    height: 400px; /* Adjust height as needed */
    border: 1px solid #ccc;
    border-radius: 4px;
    margin-top: 10px;
}

.gpx-file-path {
    display: none; /* Hide this div, it's solely for data storage */
}

.carousel-control {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background-color: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    padding: 10px 15px;
    cursor: pointer;
    z-index: 100; /* Ensure controls are above other content */
    font-size: 1.5em;
    border-radius: 5px;
    opacity: 0.8;
    transition: opacity 0.3s ease;
}

.carousel-control:hover {
    opacity: 1;
}

.prev-btn {
    left: 0; /* Position controls at the very left edge of the container */
}

.next-btn {
    right: 0; /* Position controls at the very right edge of the container */
}
</style>
<script>let currentSlide = 0;
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
});</script>