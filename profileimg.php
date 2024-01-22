<?php

// debugging
error_reporting(-1);
ini_set('display_errors', 1);

// initalize variables
$name = isset($_GET["n"]) ? $_GET["n"] : "??";
$radius = isset($_GET["r"]) ? $_GET["r"] : 150;
$size = $radius * 2;

// create a blank image
$image = imagecreatetruecolor($size, $size);

// anti alasing (not very good)
imageantialias($image, true);

// define posible colours for the circle
$colours = array(
    "green" => imagecolorallocate($image, 82, 188, 135),
    "yellow" => imagecolorallocate($image, 255, 206, 50),
    "blue" => imagecolorallocate($image, 66, 157, 208),
    "pink" => imagecolorallocate($image, 239, 75, 121),
    "purple" => imagecolorallocate($image, 159, 72, 150),
    "dark-blue" => imagecolorallocate($image, 69, 74, 156),
    "orange" => imagecolorallocate($image, 244, 125, 18),
);

// draw a circle with set colour otherwise pick a random one
$circle_col = isset($_GET["c"]) ? $colours[$_GET["c"]] : $colours[array_rand($colours)];
imagefilledellipse($image, $radius, $radius, $size - 1, $size - 1, $circle_col);

// write the text on the center of the circle.
$font = realpath('fonts/Roboto/Roboto-Medium.ttf');
$font_col = imagecolorallocate($image, 255, 255, 255);
$text_box = imagettfbbox($radius / 1.5, 0, $font, $name);
$text_width = $text_box[2] - $text_box[0];
$x = $radius - ($text_width / 1.6);
$y = $radius * 1.38;
imagettftext($image, $radius / 1.3, 0, $x, $y, $font_col, $font, $name);

// remove the black backround
$transparent = imagecolorallocate($image, 0, 0, 0);
imagecolortransparent($image, $transparent);

// output the picture
imageantialias($image, true);
header("Content-type: image/png");
imagepng($image);

imagedestroy($image);

?>
