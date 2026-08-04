<?php

$gpxDirectory = '/assets/gpx/';
$fullGpxServerPath = __DIR__ . '/..' . $gpxDirectory;
$gpxMetadataFilePath = __DIR__ . '/../content/gpx-maps-meta.json';

$gpxFiles = [];
$gpxMetadata = [];

if (file_exists($gpxMetadataFilePath) && is_readable($gpxMetadataFilePath)) {
    $metadataContent = file_get_contents($gpxMetadataFilePath);
    $gpxMetadata = json_decode($metadataContent, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error parsing gpx-maps-meta.json: " . json_last_error_msg());
        $gpxMetadata = [];
    }
}

if (is_dir($fullGpxServerPath)) {
    if ($dh = opendir($fullGpxServerPath)) {
        while (($file = readdir($dh)) !== false) {
            if (is_file($fullGpxServerPath . $file) && pathinfo($file, PATHINFO_EXTENSION) == 'gpx') {
                $file_id = pathinfo($file, PATHINFO_FILENAME);
                $clean_id = preg_replace('/[^a-zA-Z0-9_]/', '', $file_id);

                $meta = $gpxMetadata[$file] ?? [];

                $display_title = $meta['title'] ?? ucwords(str_replace(['_', '-'], ' ', $file_id));
                $description = $meta['description'] ?? '';
                $activity = $meta['activity'] ?? '';
                $date = $meta['date'] ?? '';
                $default_zoom = $meta['default_zoom'] ?? '';
                $default_center_lat = $meta['default_center']['lat'] ?? '';
                $default_center_lng = $meta['default_center']['lng'] ?? '';
                $related_vid = $meta['related_vid'] ?? '';
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

$clean_id = $_GET['id'];
$firstEntry = $gpxFiles[$clean_id] ?? reset($gpxFiles);

include __DIR__ . '/header.php';
?>
<link rel="stylesheet" href="../assets/style.css" />
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
}

.gpx-maps-carousel-container {
    position: relative;
    max-width: 920px;
    margin: 20px auto;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    padding: 0 50px;
    background-color: #1a1a1a
}

.gpx-maps-carousel-inner {
    display: flex;
    transition: transform 0.5s ease-in-out;
    flex-wrap: nowrap;
    width: 820px;
    margin: 0 auto;
}

.gpx-maps-carousel-group {
    min-width: 100%;
    box-sizing: border-box;
    flex-shrink: 0;
    display: flex;
    gap: 20px;
    justify-content: flex-start;
    align-items: flex-start;
    margin-right: 20px;
}

.carousel-item {
    flex: 0 0 400px;
    max-width: 400px;
    min-width: 200px;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 10px;

}

.gpx-map {
    width: 100%;
    height: 400px;
    border: 1px solid #ccc;
    border-radius: 4px;
    margin-top: 10px;
}

.gpx-file-path {
    display: none;
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
    z-index: 100;
    font-size: 1.5em;
    border-radius: 5px;
    opacity: 0.8;
    transition: opacity 0.3s ease;
}

.carousel-control:hover {
    opacity: 1;
}

.prev-btn {
    left: 0;
}

.next-btn {
    right: 0;
}
</style>

<div class="main-container">
    <div class="sections-wrapper">
        <section class="section gpx-maps-section">
            <h2><?php echo htmlspecialchars($firstEntry['title']); ?></h2>
            <div class="map-wrapper">
                <div id="map-<?php echo htmlspecialchars($clean_id); ?>" class="gpx-map" style="width: 800px; height: 600px;"></div>
                <div class="gpx-file-path"
                    data-gpx-path="<?php echo htmlspecialchars($firstEntry['path']); ?>"
                    data-default-zoom="<?php echo htmlspecialchars($firstEntry['default_zoom']); ?>"
                    data-default-center-lat="<?php echo htmlspecialchars($firstEntry['default_center_lat']); ?>"
                    data-default-center-lng="<?php echo htmlspecialchars($firstEntry['default_center_lng']); ?>"
                ></div>
                <?php if (!empty($firstEntry['description'])): ?>
                    <p class="gpx-description"><?php echo htmlspecialchars($firstEntry['description']); ?></p>
                <?php endif; ?>
                <?php if (!empty($firstEntry['date'])): ?>
                    <p class="gpx-meta-info">
                        date: <?php echo htmlspecialchars($firstEntry['date']); ?>
                    </p>
                <?php endif; ?>
                <?php if (!empty($firstEntry['related_vid'])): ?>
                    <p class="gpx-meta-info">
                        related video: <br>
                        <?php
                        $video_urls = explode(',', $firstEntry['related_vid']);
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
                <?php endif; ?>
                <?php if (!empty($firstEntry['trip_epic'])): ?>
                    <p class="gpx-meta-info">
                        <?php echo nl2br(htmlspecialchars($firstEntry['trip_epic'])); ?>
                    </p>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>

<script>
    window.initGPXMapsCallback = function() {
        const mapId = 'map-<?php echo htmlspecialchars($clean_id); ?>';
        const gpxPathDiv = document.querySelector('.gpx-file-path');

        if (!gpxPathDiv) {
            console.error("GPX file path data div not found. Cannot initialize map.");
            return;
        }

        const gpxFilePath = gpxPathDiv.dataset.gpxPath;
        const defaultZoom = parseInt(gpxPathDiv.dataset.defaultZoom);
        const defaultCenterLat = parseFloat(gpxPathDiv.dataset.defaultCenterLat);
        const defaultCenterLng = parseFloat(gpxPathDiv.dataset.defaultCenterLng);

        const mapElement = document.getElementById(mapId);

        if (!mapElement) {
            console.error("Map element not found for ID:", mapId);
            return;
        }

        const mapOptions = {
            zoom: defaultZoom || 12,
            mapTypeId: google.maps.MapTypeId.TERRAIN
        };

        if (!isNaN(defaultCenterLat) && !isNaN(defaultCenterLng)) {
            mapOptions.center = { lat: defaultCenterLat, lng: defaultCenterLng };
        } else {
            mapOptions.center = { lat: 0, lng: 0 };
        }

        const map = new google.maps.Map(mapElement, mapOptions);

        if (typeof GPXLayer === 'undefined') {
            console.error("GPXLayer is not defined. Please ensure the GPX parsing library (e.g., gpx-maps.js) is loaded before this script, or define GPXLayer within this script block.");
            return;
        }

        const gpxLayer = new GPXLayer(map, gpxFilePath, {});

        if (typeof gpxLayer.on === 'function') {
            gpxLayer.on('loaded', function() {
                if (gpxLayer.getBounds()) {
                    map.fitBounds(gpxLayer.getBounds());
                }
            });
        }
    };
</script>
<script src="gpx-maps.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mapId = 'map-<?php echo htmlspecialchars($clean_id); ?>';
    const gpxPathDiv = document.querySelector('.gpx-file-path');
    const gpxFilePath = gpxPathDiv.dataset.gpxPath;
    const defaultZoom = gpxPathDiv.dataset.defaultZoom;
    const defaultCenterLat = gpxPathDiv.dataset.defaultCenterLat;
    const defaultCenterLng = gpxPathDiv.dataset.defaultCenterLng;

    loadAndRenderGPXMap(mapId, gpxFilePath, defaultZoom, defaultCenterLat, defaultCenterLng);
});
</script>
<?php
include __DIR__ . '/footer.php';
?>