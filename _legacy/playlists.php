<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>magic soul travel - see the colours, hear the sounds, feel the atmosphere</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($basePath) ?>assets/style.css" />
    <link rel="icon" href="assets/img/favicon.png" type="image/png">

    <?php
    require_once __DIR__ . '/../inc/gallery-utils.php';

    $backgroundOriginalsDir = __DIR__ . '/../uploads/gallery/originals/';
    $randomBackgroundImage = GalleryUtils::getRandomGalleryImage($backgroundOriginalsDir);

    if ($randomBackgroundImage) : ?>
        <style>
            body {
                background-image: url('<?php echo htmlspecialchars($randomBackgroundImage); ?>');
                background-size: cover;       /* Ensures the image covers the entire viewport */
                background-position: center;  /* Centers the image */
                background-attachment: fixed; /* Keeps the background fixed while scrolling */
                background-repeat: no-repeat; /* Prevents the image from tiling */
            }
        </style>
    <?php endif; ?>

    <script>
        // This ensures that 'initGPXMapsCallback' exists globally, even before
        // your gpx-maps.js file has fully loaded and defined it.
        // Your gpx-maps.js will then overwrite this placeholder.
        window.initGPXMapsCallback = function() {};
    </script>

    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAibw0NuRheQo4Qv1mYcm5gN4LROaeWuCE&callback=initGPXMapsCallback"></script>

    </head>
<body>
    <div class="page-wrapper">
        <header>
         <?php include __DIR__ . '/timezone-banner.php'; ?>
    <h1>magic soul travel</h1>

            <p>
            魔法灵魂旅行&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;رحلة سحرية للروح&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;μαγικό ταξίδι ψυχής&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;जादुई आत्मा यात्रा&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;魂の魔法の旅
            </p>
<!--div id="openweathermap-widget-2" style="float: right;"></div-->
        </header>
        <main>

<!--script>window.myWidgetParam ? window.myWidgetParam : window.myWidgetParam = [];  window.myWidgetParam.push({id: 2,cityid: '3374036',appid: 'b6183361012cda6ba79575b8d1fbdbec',units: 'metric',containerid: 'openweathermap-widget-2',  });  (function() {var script = document.createElement('script');script.async = true;script.charset = "utf-8";script.src = "//openweathermap.org/themes/openweathermap/assets/vendor/owm/js/weather-widget-generator.js";var s = document.getElementsByTagName('script')[0];s.parentNode.insertBefore(script, s);  })();</script-->