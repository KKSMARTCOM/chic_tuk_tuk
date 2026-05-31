<?php

/**
 * Script pour générer les icônes PWA et screenshots
 * À exécuter une seule fois: php generate-pwa-icons.php
 */

namespace App;

class PWAIconGenerator
{
    protected $outputDir = 'public/images/pwa-icons';
    protected $screenshotDir = 'public/images/pwa-screenshots';

    public function __construct()
    {
        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0755, true);
        }
        if (!is_dir($this->screenshotDir)) {
            mkdir($this->screenshotDir, 0755, true);
        }
    }

    public function generate()
    {
        echo "🎨 Génération des icônes PWA...\n\n";

        // Générer les icônes
        $this->generateIcon(192, true);  // icon-192x192.png
        $this->generateIcon(192, false); // icon-192x192-maskable.png (maskable)
        $this->generateIcon(384, true);  // icon-384x384.png
        $this->generateIcon(384, false); // icon-384x384-maskable.png (maskable)
        $this->generateIcon(512, true);  // icon-512x512.png
        $this->generateIcon(512, false); // icon-512x512-maskable.png (maskable)

        // Générer les screenshots
        $this->generateScreenshot(540, 720);   // screenshot-540x720.png (mobile)
        $this->generateScreenshot(1280, 720);  // screenshot-1280x720.png (wide)

        echo "\n✅ Génération terminée!\n";
        echo "   - Icônes créées dans: {$this->outputDir}\n";
        echo "   - Screenshots créés dans: {$this->screenshotDir}\n";
    }

    protected function generateIcon($size, $isMaskable = false)
    {
        // Créer une image GD
        $image = imagecreatetruecolor($size, $size);

        // Couleurs
        $bgColor = imagecolorallocate($image, 3, 105, 161);      // Bleu #0369a1
        $accentColor = imagecolorallocate($image, 59, 130, 246);  // Bleu ciel
        $white = imagecolorallocate($image, 255, 255, 255);

        // Remplir le fond
        imagefill($image, 0, 0, $bgColor);

        // Ajouter un dégradé (effet basique)
        $step = $size / 10;
        for ($i = 0; $i < 10; $i++) {
            // Alpha entre 0 et 127
            $alpha = (int)(127 - ($i * 12.7));
            $alpha = max(0, min(127, $alpha));
            $color = imagecolorallocatealpha($image, 3, 105, 161 + ($i * 5), $alpha);
            $y = (int)($i * $step);
            imagefilledrectangle($image, 0, $y, $size, $y + $step, $color);
        }

        // Ajouter un logo (simple carré arrondi ou texte)
        if ($isMaskable) {
            // Pour les maskable, on utilise un design simple centré
            $squareSize = (int)($size * 0.6);
            $offset = (int)(($size - $squareSize) / 2);

            // Rond blanc
            imagefilledellipse($image, (int)($size / 2), (int)($size / 2), $squareSize, $squareSize, $white);

            // Texte "CT" (ChicTukTuk)
            $textColor = $bgColor;
            imagestring($image, 5, (int)($size * 0.35), (int)($size * 0.4), 'CT', $textColor);
        } else {
            // Pour les icônes standard, ajouter un design plus complexe
            $squareSize = (int)($size * 0.5);
            $offset = (int)(($size - $squareSize) / 2);

            // Rectangle arrondi blanc au centre
            imagefilledrectangle($image, $offset, $offset, $offset + $squareSize, $offset + $squareSize, $white);

            // Petits carrés colorés pour l'accent
            $smallSquare = (int)($size * 0.1);
            imagefilledrectangle($image, $offset + $squareSize - $smallSquare, $offset, $offset + $squareSize, $offset + $smallSquare, $accentColor);
        }

        $filename = $this->outputDir . '/icon-' . $size . 'x' . $size . ($isMaskable ? '-maskable' : '') . '.png';
        imagepng($image, $filename);
        imagedestroy($image);

        echo "✅ Créé: $filename\n";
    }

    protected function generateScreenshot($width, $height)
    {
        // Créer une image
        $image = imagecreatetruecolor($width, $height);

        // Couleurs
        $bgColor = imagecolorallocate($image, 3, 105, 161);      // Bleu #0369a1
        $lightBg = imagecolorallocate($image, 15, 23, 42);       // Bleu foncé
        $white = imagecolorallocate($image, 255, 255, 255);
        $accentColor = imagecolorallocate($image, 59, 130, 246);

        // Remplir le fond
        imagefill($image, 0, 0, $bgColor);

        // Simuler un header
        $headerHeight = (int)($height * 0.15);
        imagefilledrectangle($image, 0, 0, $width, $headerHeight, $lightBg);

        // Logo/titre
        $textColor = $white;
        $fontSize = (int)($width / 30);
        if (function_exists('imagettftext')) {
            // Si les fonts TrueType sont disponibles
            // imagettftext($image, $fontSize, 0, 20, 40, $textColor, __DIR__ . '/resources/fonts/arial.ttf', 'ChicTukTuk');
        } else {
            // Utiliser imagestring comme fallback
            imagestring($image, 5, 20, 20, 'ChicTukTuk', $textColor);
        }

        // Simuler du contenu (rectangles)
        $contentStart = $headerHeight + 20;
        $itemHeight = (int)(($height - $contentStart - 20) / 4);

        for ($i = 0; $i < 4; $i++) {
            $y = $contentStart + ($i * $itemHeight);

            // Ligne grise
            $gray = imagecolorallocate($image, 200, 200, 200);
            imagefilledrectangle($image, 20, $y, $width - 20, $y + (int)($itemHeight * 0.7), $gray);

            // Accent bleu
            imagefilledrectangle($image, 20, $y, (int)(20 + ($width * 0.3)), $y + 5, $accentColor);
        }

        $filename = $this->screenshotDir . '/screenshot-' . $width . 'x' . $height . '.png';
        imagepng($image, $filename);
        imagedestroy($image);

        echo "✅ Créé: $filename\n";
    }
}

// Exécuter si c'est le fichier principal
if (php_sapi_name() === 'cli' && basename(__FILE__) === 'generate-pwa-icons.php') {
    try {
        $generator = new PWAIconGenerator();
        $generator->generate();
    } catch (\Exception $e) {
        echo "❌ Erreur: " . $e->getMessage() . "\n";
        exit(1);
    }
}
