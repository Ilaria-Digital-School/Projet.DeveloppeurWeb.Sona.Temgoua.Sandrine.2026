<?php

use Imagick;

$directory = __DIR__ . '/public/images';

$images = glob($directory . '/*.{jpg,jpeg,png,avif}', GLOB_BRACE);

foreach ($images as $imagePath) {

    try {

        $webpPath = preg_replace(
            '/\.(jpg|jpeg|png|avif)$/i',
            '.webp',
            $imagePath
        );

        // Ignore si déjà converti
        if (file_exists($webpPath)) {

            echo "Déjà convertie : " . basename($webpPath) . PHP_EOL;
            continue;
        }

        $imagick = new Imagick($imagePath);

        $imagick->setImageFormat('webp');

        $imagick->setImageCompressionQuality(75);

        $imagick->writeImage($webpPath);

        $imagick->clear();
        $imagick->destroy();

        echo "Compressée : " . basename($webpPath) . PHP_EOL;

    } catch (Exception $e) {

        echo "Erreur : " . basename($imagePath) . PHP_EOL;
    }
}

echo "Compression terminée !";