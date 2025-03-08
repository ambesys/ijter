<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Basic captcha implementation
$width = 120;
$height = 40;
$length = 6;

// Create image
$image = imagecreatetruecolor($width, $height);

// Colors
$bg = imagecolorallocate($image, 255, 255, 255);
$text_color = imagecolorallocate($image, 0, 0, 0);

// Fill background
imagefilledrectangle($image, 0, 0, $width, $height, $bg);

// Generate random string
$captcha_string = substr(str_shuffle('123456789ABCDEFGHIJKLMNOPRSTUVWXYZ'), 0, $length);

// Save to session
$_SESSION['captcha'] = $captcha_string;

// Add noise
for ($i = 0; $i < 100; $i++) {
    imagesetpixel($image, rand(0, $width-1), rand(0, $height-1), $text_color);
}

// Add text
imagestring($image, 5, 20, 10, $captcha_string, $text_color);

// Output image
imagepng($image);
imagedestroy($image);
