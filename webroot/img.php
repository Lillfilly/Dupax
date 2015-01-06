<?php
include ('./config.php');
$image = new Image();

$src        = isset($_GET['src'])     ? $_GET['src']      : null;
$verbose    = isset($_GET['verbose']) ? true              : false;
$saveAs     = isset($_GET['save-as']) ? $_GET['save-as']  : null;
$quality    = isset($_GET['quality']) ? $_GET['quality']  : 60;
$ignoreCache = isset($_GET['no-cache']) ? true            : false;
$newWidth   = isset($_GET['width'])   ? $_GET['width']    : null;
$newHeight  = isset($_GET['height'])  ? $_GET['height']   : null;
$cropToFit  = isset($_GET['crop-to-fit']) ? true : false;
$sharpen    = isset($_GET['sharpen']) ? true : false;

$image->outputImage($image->getProcessedImage($src, $saveAs, $quality, $ignoreCache, $newWidth, $newHeight, $cropToFit, $sharpen, $verbose), $verbose);
