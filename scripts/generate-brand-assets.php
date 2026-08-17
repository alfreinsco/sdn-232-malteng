<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$sourcePath = $root.'/public/logo-malteng.png';
$source = imagecreatefrompng($sourcePath);

if ($source === false) {
    throw new RuntimeException('Logo Maluku Tengah tidak dapat dibaca.');
}

function renderSquare(GdImage $source, int $size): GdImage
{
    $canvas = imagecreatetruecolor($size, $size);
    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);
    $transparent = imagecolorallocatealpha($canvas, 255, 255, 255, 127);
    imagefill($canvas, 0, 0, $transparent);
    imagealphablending($canvas, true);

    $sourceWidth = imagesx($source);
    $sourceHeight = imagesy($source);
    $available = (int) floor($size * 0.9);
    $scale = min($available / $sourceWidth, $available / $sourceHeight);
    $width = max(1, (int) round($sourceWidth * $scale));
    $height = max(1, (int) round($sourceHeight * $scale));
    $x = (int) floor(($size - $width) / 2);
    $y = (int) floor(($size - $height) / 2);

    imagecopyresampled($canvas, $source, $x, $y, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

    return $canvas;
}

foreach ([32, 192, 512] as $size) {
    $image = renderSquare($source, $size);
    imagepng($image, $root."/public/favicon-{$size}x{$size}.png", 9);
    imagedestroy($image);
}

$appleIcon = renderSquare($source, 180);
imagepng($appleIcon, $root.'/public/apple-touch-icon.png', 9);
imagedestroy($appleIcon);

$faviconPng = file_get_contents($root.'/public/favicon-32x32.png');

if ($faviconPng === false) {
    throw new RuntimeException('PNG favicon tidak dapat dibaca.');
}

$iconDirectory = pack('vvv', 0, 1, 1);
$iconEntry = pack('CCCCvvVV', 32, 32, 0, 0, 1, 32, strlen($faviconPng), 22);
file_put_contents($root.'/public/favicon.ico', $iconDirectory.$iconEntry.$faviconPng);

imagedestroy($source);

echo "Aset favicon berhasil dibuat.\n";
