<?php
// Assuming the URL parameter is the image filename
$imageFilename = basename($_GET['image']);

// Define paths
$galleryPath = __DIR__ . '/../uploads/gallery/';
$originalsPath = $galleryPath . 'originals/';
$metadataPath = $galleryPath . 'metadata/';

// Load metadata
$metadataFile = str_replace('.jpg', '.json', $imageFilename);
$metadata = [];
if (file_exists($metadataPath . $metadataFile)) {
    $metadata = json_decode(file_get_contents($metadataPath . $metadataFile), true);
}

// Display image and metadata
?>

<div class="image-container">
    <img src="<?php echo htmlspecialchars('../uploads/gallery/originals/' . $imageFilename); ?>" alt="<?php echo htmlspecialchars($imageFilename); ?>">
    <?php if (!empty($metadata)) : ?>
        <div class="metadata">
            <?php foreach ($metadata['exif'] as $section => $data) : ?>
                <h2><?php echo htmlspecialchars($section); ?></h2>
                <?php foreach ($data as $key => $value) : ?>
                    <p><strong><?php echo htmlspecialchars($key); ?>:</strong> 
                    <?php 
                        if (is_array($value)) {
                            echo htmlspecialchars(implode(', ', $value));
                        } else {
                            echo htmlspecialchars($value);
                        }
                    ?>
                    </p>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>