<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/content.php';

$isLoggedIn = is_logged_in();
$email = $_SESSION['email'] ?? ''; 

include __DIR__ . '/templates/header.php';
// --- Add this block for YouTube API variables ---
echo '<script>';
echo 'const apiKey = "AIzaSyA4znqz_oAMUN7LRm_IWl7WK6l5SI4dh3k";'; // Your actual API key
echo 'const channelId = "UC4zkEzqEk1yyAgoxctP3z8g";'; // Your actual YouTube Channel ID
echo '</script>';
// --- End of YouTube API variables block ---
echo '<div class="main-container">'; // A new wrapper for your main content sections
echo '<div class="sections-wrapper">';

include __DIR__ . '/templates/latest-videos.php';
 
       $sections = ['about'
//,'home'
]; // Define the sections you want to load
        foreach ($sections as $section_name) {
            $content = load_content($section_name);

            if (!empty($content) && isset($content['title']) && isset($content['body'])) {
                echo '<section class="section ' . htmlspecialchars($section_name) . '-section">';
                echo '<h2>' . htmlspecialchars($content['title']) . '</h2>';
                echo '<div class="left-align">' . $content['body'] . '</div>';
                echo '</section>';
            } else {
                echo '<section class="section error-section">';
                echo '<h2>Error loading ' . htmlspecialchars($section_name) . ' content.</h2>';
                echo '</section>';
            }
        }
include __DIR__ . '/templates/blogger-developer.php';
//include __DIR__ . '/templates/gpx-maps-module.php';


include __DIR__ . '/templates/gallery-carousel.php';
include __DIR__ . '/templates/useful-links.php';
include __DIR__ . '/templates/holiday-planner.php';

//include __DIR__ . '/templates/packing-list.php';
//include __DIR__ . '/templates/blogger.php';

echo '</div>';
echo '</div>'; // Closes <div class="sections-wrapper">

include __DIR__ . '/templates/footer.php';

?>