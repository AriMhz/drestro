<?php
$pngPath = 'C:/Users/AZN/Downloads/Annotation 2026-05-22 052946.png';
$icoPath = 'C:/Users/AZN/Documents/DrestroPOS/app_icon.ico';

if (!file_exists($pngPath)) {
    die("PNG file not found.\n");
}

$pngData = file_get_contents($pngPath);
$pngSize = strlen($pngData);

// Read PNG dimensions from IHDR (offset 16 for width, 20 for height)
$widthData = substr($pngData, 16, 4);
$heightData = substr($pngData, 20, 4);

$w = unpack("N", $widthData)[1];
$h = unpack("N", $heightData)[1];

if ($w >= 256) $w = 0;
if ($h >= 256) $h = 0;

// ICO header
$header = pack("vvv", 0, 1, 1); // Reserved, Type (1=ICO), Count (1)
// Directory entry
$entry = pack("CCCCvvVV", 
    $w,           // width
    $h,           // height
    0,            // palette count
    0,            // reserved
    1,            // planes
    32,           // bpp
    $pngSize,     // size of png
    22            // offset to png data (6 bytes header + 16 bytes entry)
);

file_put_contents($icoPath, $header . $entry . $pngData);
echo "Converted to ICO!\n";
