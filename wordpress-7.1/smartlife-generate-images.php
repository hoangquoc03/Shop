<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

require_once __DIR__ . '/wp-load.php';
require_once __DIR__ . '/smartlife-import-core.php';

if (!extension_loaded('gd') || !function_exists('imagewebp')) {
    fwrite(STDERR, "GD with WebP support is required.\n");
    exit(2);
}

$force = in_array('--force-demo', $argv ?? array(), true);
$catalog = require __DIR__ . '/smartlife-catalog.php';
$upload = wp_upload_dir();
$directory = trailingslashit($upload['basedir']) . 'smartlife-products';
if (!wp_mkdir_p($directory)) {
    fwrite(STDERR, "Cannot create local image directory.\n");
    exit(3);
}

$font = 'C:/Windows/Fonts/arial.ttf';
$manifest = array();
$created = 0;
$skipped = 0;

function sl11_image_color($image, $hex)
{
    $hex = ltrim($hex, '#');
    return imagecolorallocate($image, hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
}

function sl11_image_text($image, $font, $size, $x, $y, $color, $text)
{
    if ($font && is_file($font) && function_exists('imagettftext')) {
        imagettftext($image, $size, 0, $x, $y, $color, $font, $text);
        return;
    }
    imagestring($image, 5, $x, $y - 14, strtoupper(remove_accents($text)), $color);
}

function sl11_draw_product_icon($image, $category, $accent, $body, $white, $variant)
{
    $cx = 450;
    $cy = 390;
    $dark = sl11_image_color($image, '203832');
    imagefilledellipse($image, $cx, 690, 390, 44, sl11_image_color($image, 'd7e2dc'));

    if (in_array($category, array('Bóng đèn thông minh', 'Đèn LED thông minh', 'Đèn ngủ thông minh'), true)) {
        imagefilledellipse($image, $cx, 350, 210, 230, $body);
        imagefilledrectangle($image, 390, 420, 510, 540, $body);
        imagefilledrectangle($image, 395, 520, 505, 558, $dark);
        imagefilledrectangle($image, 405, 560, 495, 580, $dark);
        imagefilledellipse($image, $cx, 350, 90, 90, $accent);
        imagefilledrectangle($image, 438, 485, 462, 535, $white);
    } elseif (in_array($category, array('Camera thông minh', 'Chuông cửa thông minh'), true)) {
        imagefilledrectangle($image, 275, 330, 625, 515, $body);
        imagefilledellipse($image, 440, 420, 145, 145, $dark);
        imagefilledellipse($image, 440, 420, 82, 82, $accent);
        imagefilledellipse($image, 440, 420, 34, 34, $white);
        imagefilledellipse($image, 555, 368, 24, 24, $accent);
        imagefilledrectangle($image, 385, 515, 505, 550, $dark);
        imagefilledrectangle($image, 410, 550, 480, 630, $dark);
    } elseif ($category === 'Khóa cửa thông minh') {
        imagefilledrectangle($image, 340, 245, 560, 620, $body);
        imagefilledrectangle($image, 375, 280, 525, 395, $dark);
        imagefilledellipse($image, 450, 340, 62, 62, $accent);
        for ($row = 0; $row < 3; $row++) {
            for ($col = 0; $col < 3; $col++) {
                imagefilledellipse($image, 400 + ($col * 50), 440 + ($row * 46), 17, 17, $white);
            }
        }
        imagefilledellipse($image, 450, 590, 80, 18, $dark);
    } elseif ($category === 'Công tắc thông minh') {
        imagefilledrectangle($image, 300, 250, 600, 610, $body);
        imagefilledrectangle($image, 330, 280, 570, 580, $white);
        $count = 1 + ($variant % 3);
        for ($index = 0; $index < $count; $index++) {
            $x = 390 + ($index * 60);
            imagefilledrectangle($image, $x - 22, 350, $x + 22, 500, $body);
            imagefilledellipse($image, $x, 390, 18, 18, $accent);
        }
    } elseif ($category === 'Ổ cắm thông minh') {
        imagefilledrectangle($image, 320, 255, 580, 600, $body);
        imagefilledrectangle($image, 350, 285, 550, 565, $white);
        imagefilledellipse($image, 450, 410, 110, 110, $body);
        imagefilledellipse($image, 420, 395, 18, 40, $dark);
        imagefilledellipse($image, 480, 395, 18, 40, $dark);
        imagefilledellipse($image, 450, 450, 18, 40, $dark);
        imagefilledellipse($image, 535, 300, 18, 18, $accent);
    } elseif (in_array($category, array('Hub điều khiển', 'Bộ điều khiển trung tâm', 'Bộ phát Wi-Fi'), true)) {
        imagefilledrectangle($image, 300, 390, 600, 530, $body);
        imagefilledellipse($image, 450, 390, 300, 90, $body);
        imagefilledellipse($image, 450, 530, 300, 90, $dark);
        imagefilledellipse($image, 450, 386, 250, 54, $body);
        for ($index = 0; $index < 3; $index++) {
            imagefilledellipse($image, 390 + ($index * 60), 455, 16, 16, $index === ($variant % 3) ? $accent : $white);
        }
        imagearc($image, 450, 330, 180, 110, 205, 335, $accent);
        imagearc($image, 450, 330, 120, 74, 205, 335, $accent);
    } elseif ($category === 'Remote thông minh') {
        imagefilledrectangle($image, 375, 205, 525, 625, $body);
        imagefilledrectangle($image, 390, 225, 510, 605, $white);
        imagefilledellipse($image, 450, 290, 64, 64, $accent);
        for ($row = 0; $row < 4; $row++) {
            for ($col = 0; $col < 3; $col++) {
                imagefilledellipse($image, 415 + ($col * 35), 375 + ($row * 48), 19, 19, $body);
            }
        }
    } elseif ($category === 'Robot hút bụi') {
        imagefilledellipse($image, 450, 450, 350, 250, $body);
        imagefilledellipse($image, 450, 405, 290, 200, $white);
        imagefilledellipse($image, 450, 405, 76, 76, $accent);
        imagefilledellipse($image, 450, 405, 30, 30, $dark);
        imagefilledrectangle($image, 300, 505, 600, 540, $dark);
        imagefilledellipse($image, 330, 550, 42, 24, $dark);
        imagefilledellipse($image, 570, 550, 42, 24, $dark);
    } elseif (in_array($category, array('Máy lọc không khí', 'Máy hút ẩm'), true)) {
        imagefilledrectangle($image, 340, 205, 560, 630, $body);
        imagefilledrectangle($image, 365, 235, 535, 595, $white);
        for ($y = 310; $y < 520; $y += 32) {
            imagefilledrectangle($image, 390, $y, 510, $y + 7, $body);
        }
        imagefilledellipse($image, 450, 280, 23, 23, $accent);
    } elseif ($category === 'Rèm cửa thông minh') {
        imagefilledrectangle($image, 260, 245, 640, 610, $dark);
        imagefilledrectangle($image, 285, 270, 615, 585, $white);
        imagefilledpolygon($image, array(285, 270, 445, 270, 430, 585, 285, 585), 4, $body);
        imagefilledpolygon($image, array(455, 270, 615, 270, 615, 585, 470, 585), 4, $accent);
        imagefilledrectangle($image, 430, 270, 470, 585, $dark);
    } elseif (in_array($category, array('Cảm biến an ninh', 'Cảm biến chuyển động', 'Cảm biến cửa', 'Cảm biến nhiệt độ và độ ẩm', 'Cảm biến khói'), true)) {
        imagefilledrectangle($image, 325, 290, 575, 540, $body);
        imagefilledrectangle($image, 350, 315, 550, 515, $white);
        imagefilledellipse($image, 450, 395, 105, 105, $accent);
        imagefilledellipse($image, 450, 395, 48, 48, $dark);
        imagefilledrectangle($image, 390, 475, 510, 490, $dark);
    } else {
        imagefilledrectangle($image, 300, 335, 600, 520, $body);
        imagefilledrectangle($image, 330, 365, 570, 490, $white);
        imagefilledellipse($image, 450, 425, 82, 82, $accent);
        imagefilledellipse($image, 450, 425, 32, 32, $dark);
    }
}

foreach ($catalog as $index => $entry) {
    $filename = sanitize_file_name($entry['sku']) . '.webp';
    $path = trailingslashit($directory) . $filename;
    $group = $entry['category'];
    $palette = array(
        'background' => 'f2f5ef',
        'body' => '246a57',
        'accent' => array('d56b4d', 'd8a83e', '547d9e', '8a715d')[$index % 4],
        'white' => 'ffffff',
    );

    if (is_file($path) && !$force) {
        $skipped++;
    } else {
        $image = imagecreatetruecolor(900, 900);
        imageantialias($image, true);
        $background = sl11_image_color($image, $palette['background']);
        $body = sl11_image_color($image, $palette['body']);
        $accent = sl11_image_color($image, $palette['accent']);
        $white = sl11_image_color($image, $palette['white']);
        $ink = sl11_image_color($image, '18322b');
        $muted = sl11_image_color($image, '5c7067');
        imagefill($image, 0, 0, $background);
        imagefilledellipse($image, 450, 395, 570, 570, sl11_image_color($image, 'e2ece4'));
        sl11_draw_product_icon($image, $group, $accent, $body, $white, $index);
        sl11_image_text($image, $font, 29, 58, 82, $ink, 'SMARTLIFE');
        sl11_image_text($image, $font, 18, 650, 80, $accent, 'DEMO');
        sl11_image_text($image, $font, 22, 58, 755, $ink, $entry['sku']);
        sl11_image_text($image, $font, 16, 58, 800, $muted, 'DEMO IMAGE - NOT OFFICIAL PRODUCT PHOTO');
        sl11_image_text($image, $font, 17, 58, 850, $muted, $group);
        if (!imagewebp($image, $path, 82)) {
            imagedestroy($image);
            fwrite(STDERR, 'Could not write ' . $filename . "\n");
            exit(4);
        }
        imagedestroy($image);
        $created++;
    }

    $bytes = is_file($path) ? filesize($path) : 0;
    $manifest[] = array(
        'sku' => $entry['sku'],
        'name' => $entry['name'],
        'category' => $entry['category'],
        'filename' => $filename,
        'relative_path' => 'smartlife-products/' . $filename,
        'format' => 'webp',
        'width' => 900,
        'height' => 900,
        'bytes' => $bytes,
        'kind' => 'clearly_labeled_demo_illustration',
    );
}

$manifest_path = trailingslashit($directory) . 'image-manifest.json';
if (file_put_contents($manifest_path, wp_json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
    fwrite(STDERR, "Could not write image manifest.\n");
    exit(5);
}

echo wp_json_encode(array(
    'generated' => $created,
    'skipped_existing' => $skipped,
    'manifest' => $manifest_path,
    'images' => count($manifest),
    'max_bytes' => max(array_column($manifest, 'bytes')),
), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
