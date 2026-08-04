<?php
// /aamagicv3/templates/latest-videos.php

// 1. Require your caching helper functions
require_once __DIR__ . '/../inc/cache-helper.php';

// 2. Define your YouTube API credentials and parameters (KEEP API KEY SECURE)
//    It's best practice to NOT expose your API key directly in the client-side JavaScript.
//    If your API key is in a config file, load it from there.
$apiKey = 'AIzaSyA4znqz_oAMUN7LRm_IWl7WK6l5SI4dh3k'; // Your actual API key
$channelId = 'UC4zkEzqEk1yyAgoxctP3z8g'; // Your actual YouTube Channel ID
$maxResults = 10;

// 3. Define a unique cache key and a TTL (Time To Live) for this specific API call
//    The cache key ensures different API calls get different cached files.
//    TTL of 3600 seconds = 1 hour. Adjust as needed.
$cacheKey = 'youtube_latest_videos_' . md5($channelId . '_' . $maxResults);
$cacheTtl = 3600; // Cache for 1 hour (in seconds)

// 4. Try to get the video data from cache first
$videoData = get_cached_data($cacheKey, $cacheTtl);

if ($videoData === false) {
    // 5. If data is NOT in cache or is expired, make the actual API call
    $apiUrl = "https://www.googleapis.com/youtube/v3/search?key={$apiKey}&channelId={$channelId}&part=snippet,id&order=date&maxResults={$maxResults}";

    // Using cURL for more robust API fetching and error handling
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Set a timeout for the request (10 seconds)
    
    // Optional: If you face SSL certificate issues (less common on modern hosts)
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); 
    // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get HTTP status code
    $curl_error = curl_error($ch); // Get any cURL error message
    curl_close($ch);

    if ($response === false || $http_code !== 200) { // Check if request failed or non-200 status
        // 6. Handle API call failure (e.g., network error, invalid key)
        $videoData = ['error' => 'Failed to fetch YouTube videos. ' . ($curl_error ? 'cURL Error: ' . htmlspecialchars($curl_error) : 'HTTP Status: ' . $http_code)];
        error_log("YouTube API error: Could not fetch data from: " . $apiUrl . " | cURL Error: " . $curl_error . " | HTTP Code: " . $http_code); // Log the error for debugging
    } else {
        // 7. Decode the JSON response
        $videoData = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // 8. Handle invalid JSON response
            $videoData = ['error' => 'Invalid JSON response from YouTube API: ' . json_last_error_msg()];
            error_log("YouTube API error: Invalid JSON response: " . json_last_error_msg());
        } else {
            // 9. Store the fresh, valid data in cache
            set_cached_data($cacheKey, $videoData);
        }
    }
} else {
    // Data was successfully retrieved from cache
    // You could add a debug line here: echo "";
}

?>

<section class="section">
    <h2>latest videos</h2>
    
    <style>
    /* --- Video Carousel Styles --- */

    .video-grid {
        display: flex; /* Arranges items in a single row */
        overflow-x: auto; /* Enables horizontal scrolling if content overflows */
        scroll-behavior: smooth; /* Makes scrolling smooth */
        -webkit-overflow-scrolling: touch; /* Improves scrolling performance on iOS devices */
        padding-bottom: 20px; /* Space for scrollbar on some systems */
        margin-bottom: 20px; /* Space below the carousel */
    }

    .video-item {
        flex: 0 0 auto; /* Prevents flex items from shrinking and sets basis to auto */
        width: 320px; /* Fixed width for each video item */
        margin-right: 20px; /* Space between video items */
        /* Add a subtle background or border if you want each video card to stand out */
        /* background-color: #333; */
        /* border-radius: 8px; */
        /* box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); */
        /* padding: 10px; */
    }

    .video-item:last-child {
        margin-right: 0; /* No margin on the very last item */
    }

    .video-item iframe {
        width: 100%; /* Make iframe take full width of its parent (.video-item) */
        height: 200px; /* Keep consistent height */
        display: block; /* Remove any extra space below the iframe */
        border-radius: 6px; /* Slightly rounded corners for the video itself */
    }

    /* Optional: Custom Scrollbar Styling (for Webkit browsers like Chrome/Safari) */
    /* This can make the scrollbar thinner or change its color */
    .video-grid::-webkit-scrollbar {
        height: 8px; /* Height of the horizontal scrollbar */
    }

    .video-grid::-webkit-scrollbar-track {
        background: #444; /* Darker track for dark theme */
        border-radius: 10px;
    }

    .video-grid::-webkit-scrollbar-thumb {
        background: #87CEEB; /* Sky Blue thumb, matches link color */
        border-radius: 10px;
    }

    .video-grid::-webkit-scrollbar-thumb:hover {
        background: #00BFFF; /* Brighter blue on hover */
    }
    </style>
    <div class="video-grid" id="latest-videos">
        <?php
        // 10. Display the videos using the $videoData (either from cache or new API call)
        if (isset($videoData['items']) && is_array($videoData['items'])):
            foreach ($videoData['items'] as $item):
                if (isset($item['id']['kind']) && $item['id']['kind'] === "youtube#video" && isset($item['id']['videoId'])):
                    // Correct YouTube embed URL: https://www.youtube.com/embed/VIDEO_ID
        ?>
                    <div class="video-item">
                        <iframe
                            width="100%" {/* Ensure iframe defaults to 100% width within its parent */}
                            height="200"
                            src="https://www.youtube.com/embed/<?php echo htmlspecialchars($item['id']['videoId']); ?>"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                        ></iframe>
                    </div>
                <?php
                endif;
            endforeach;
        elseif (isset($videoData['error'])):
            // Display error message if data could not be fetched
        ?>
            <p><?php echo htmlspecialchars($videoData['error']); ?></p>
        <?php
        else:
            // Generic fallback if no items and no specific error
        ?>
            <p>Could not retrieve latest videos at this time.</p>
        <?php endif; ?>
    </div>
</section>