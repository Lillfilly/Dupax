<?php
class Image {

private $maxWidth = 2000;
private $maxHeight = 2000;

private static $CACHE_PATH; 

function __construct(){
    self::$CACHE_PATH = './cache/';
}
/**
 * Display log message.
 *
 * @param string $message the log message to display.
 */
private function verbose($message) {
  echo "<p>" . htmlentities($message) . "</p>";
}
/**
 * Display error message.
 *
 * @param string $message the error message to display.
 */
private function errorMessage($message) {
  header("Status: 404 Not Found");
  die('img.php says 404 - ' . htmlentities($message));
}
/**
 * Sharpen image as http://php.net/manual/en/ref.image.php#56144
 * http://loriweb.pair.com/8udf-sharpen.html
 *
 * @param resource $image the image to apply this filter on.
 * @return resource $image as the processed image.
 */
private function sharpenImage($image) {
  $matrix = array(
    array(-1,-1,-1,),
    array(-1,16,-1,),
    array(-1,-1,-1,)
  );
  $divisor = 8;
  $offset = 0;
  imageconvolution($image, $matrix, $divisor, $offset);
  return $image;
}
/**
 * Output an image together with last modified header.
 *
 * @param string $file as path to the image.
 * @param boolean $verbose if verbose mode is on or off.
 */
public function outputImage($file, $verbose) {
  $info = getimagesize($file);
  !empty($info) or $this->errorMessage("The file doesn't seem to be an image.");
  $mime   = $info['mime'];

  $lastModified = filemtime($file);  
  $gmdate = gmdate("D, d M Y H:i:s", $lastModified);

  if($verbose) {
    $this->verbose("Memory peak: " . round(memory_get_peak_usage() /1024/1024) . "M");
    $this->verbose("Memory limit: " . ini_get('memory_limit'));
    $this->verbose("Time is {$gmdate} GMT.");
  }

  if(!$verbose) header('Last-Modified: ' . $gmdate . ' GMT');
  if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $lastModified){
    if($verbose) { $this->verbose("Would send header 304 Not Modified, but its verbose mode."); exit; }
    header('HTTP/1.0 304 Not Modified');
  } else {  
    if($verbose) { $this->verbose("Would send header to deliver image with modified time: {$gmdate} GMT, but its verbose mode."); exit; }
    header('Content-type: ' . $mime);  
    readfile($file);
  }
}
/*
*	Returns the info about the image
*/
private function getImageInfo($pathToImage, $verbose){
    $imgInfo = list($width, $height, $type, $attr) = getimagesize($pathToImage);
    !empty($imgInfo) or $this->errorMessage("The file doesn't seem to be an image.");
    $mime = $imgInfo['mime'];

    if($verbose) {
	$filesize = filesize($pathToImage);
	$this->verbose("Image file: {$pathToImage}");
	$this->verbose("Image information: " . print_r($imgInfo, true));
	$this->verbose("Image width x height (type): {$width} x {$height} ({$type}).");
	$this->verbose("Image file size: {$filesize} bytes.");
	$this->verbose("Image mime type: {$mime}.");
    }
    return $imgInfo;
}
/*
*	Saves the image to the cache
*/
private function saveImage($image, $cacheFileName, $quality, $saveAs, $verbose){
    switch($saveAs) {
	case 'jpeg':
	case 'jpg':
	    if($verbose) { $this->verbose("Saving image as JPEG to cache using quality = {$quality}."); }
	    imagejpeg($image, $cacheFileName, $quality);
	break;  

	case 'png':  
	    if($verbose) { $this->verbose("Saving image as PNG to cache."); }
	    imagepng($image, $cacheFileName);  
	break;  

	default:
	    $this->errorMessage('No support to save as this file extension.');
	break;
    }

    if($verbose) { 
	clearstatcache();
	$cacheFilesize = filesize($cacheFileName);
	$this->verbose("File size of cached file: {$cacheFilesize} bytes."); 
    }
}
private function createCacheName($pathToImage, $saveAs, $newWidth, $newHeight, $quality, $cropToFit, $sharpen, $verbose)
{
    $parts = pathinfo($pathToImage);
    $fileExtension = $parts['extension'];
    $saveAs = is_null($saveAs) ? $fileExtension : $saveAs;
    $quality = is_null($quality) ? null : "_q{$quality}";
    $cropToFit = is_null($cropToFit) ? null : "_cf";
    $sharpen = is_null($sharpen) ? null : "_s";
    $dirName = preg_replace('/\//', '-', dirname($pathToImage));
    $cacheFileName = self::$CACHE_PATH . "-{$dirName}-{$parts['filename']}_{$newWidth}_{$newHeight}{$quality}{$cropToFit}{$sharpen}.{$saveAs}";
    $cacheFileName = preg_replace('/^a-zA-Z0-9\.-_/', '', $cacheFileName);
    if($verbose){
	$this->verbose("Cache file is: " . $cacheFileName);
    }
    return $cacheFileName;
}
/**
*	Checks if there is a valid cached image saved on the server.
*	@return bool True if valid, false if not
*/
private function isCached($cacheFileName, $imageModifiedTime, $verbose){
    if(is_file($cacheFileName) && filemtime($cacheFileName) > $imageModifiedTime)
    {
	if($verbose)
	    $this->verbose("Chache file is valid");
	return true;
    }
    else{
	if($verbose)
	    $this->verbose("Chache is not valid");
	return false;
    }
}
/**
*	Creates an image object from file.
*	@return image The image (PHP GD) or null
*/
private function readImageFromFile($pathToImage, $fileExtension, $verbose){
    if($verbose)
	$this->verbose("File extension is: " . $fileExtension);
    switch($fileExtension){
	case 'jpg':
	case 'jpeg':
	    $image = imagecreatefromjpeg($pathToImage);
	    if($verbose)
		$this->verbose("Opened image as jpg");
	    break;
	case 'png':  
	    $image = imagecreatefrompng($pathToImage); 
	    if($verbose) { $this->verbose("Opened the image as a PNG image."); }
	    break;
	default:
	    $this->errorMessage("Unsupported image type - " . $fileExtension);
    }
    return isset($image) ? $image : null;
}
/**
*	Creates an image object processed according to the arguments
*	@return image The processed image (PHP GD)
*/
private function processImage
    ($pathToImage, $fileExtension, $cropToFit, $width, $height, $newWidth, $newHeight, $sharpen, $verbose)
{
    $image = $this->readImageFromFile($pathToImage, $fileExtension, $verbose);
    $aspectRatio = $width / $height;
    if($cropToFit && $newWidth && $newHeight) {
	$targetRatio = $newWidth / $newHeight;
	$cropWidth   = $targetRatio > $aspectRatio ? $width : round($height * $targetRatio);
	$cropHeight  = $targetRatio > $aspectRatio ? round($width  / $targetRatio) : $height;
	if($verbose){
	    $this->verbose("Crop to fit into box of {$newWidth}x{$newHeight}. Cropping dimensions: {$cropWidth}x{$cropHeight}.");
	}   
    }
    //Resize if needed
    if($cropToFit) {
	if($verbose) { $this->verbose("Resizing, crop to fit."); }
	$cropX = round(($width - $cropWidth) / 2);  
	$cropY = round(($height - $cropHeight) / 2);    
	$imageResized = imagecreatetruecolor($newWidth, $newHeight);
	imagecopyresampled($imageResized, $image, 0, 0, $cropX, $cropY, $newWidth, $newHeight, $cropWidth, $cropHeight);
	$image = $imageResized;
	$width = $newWidth;
	$height = $newHeight;
    }
    else if(!($newWidth == $width && $newHeight == $height)) {
	if($verbose) { $this->verbose("Resizing, new height and/or width."); }
	    $imageResized = imagecreatetruecolor($newWidth, $newHeight);
	    imagecopyresampled($imageResized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
	    $image  = $imageResized;
	    $width  = $newWidth;
	    $height = $newHeight;
	}
    if($sharpen) {
	$image = $this->sharpenImage($image);
    }
    return $image;
}
/**
*	Processes the image according to the arguments and returns a url pointing to the
*	processed image
*	@return string Path to the image
*/
public function getProcessedImage
    ($src = null, $saveAs = null, $quality = 60, $ignoreCache = null, $newWidth = null, $newHeight = null, $cropToFit = null, $sharpen = null, $verbose = false)
{
    //Validate
    is_dir(dirname($src)) or $this->errorMessage('The image dir is not a valid directory.');
    is_writable(self::$CACHE_PATH) or $this->errorMessage('The cache dir is not a writable directory.');
    isset($src) or $this->errorMessage('Must set src-attribute.');
    preg_match('#^[a-z0-9A-Z-_\.\/]+$#', $src) or $this->errorMessage('Filename contains invalid characters.');
    is_null($saveAs) or in_array($saveAs, array('png', 'jpg', 'jpeg')) or $this->errorMessage('Not a valid extension to save image as');
    is_null($quality) or (is_numeric($quality) and $quality > 0 and $quality <= 100) or $this->errorMessage('Quality out of range');
    is_null($newWidth) or (is_numeric($newWidth) and $newWidth > 0 and $newWidth <= $this->maxWidth) or $this->errorMessage('Width out of range');
    is_null($newHeight) or (is_numeric($newHeight) and $newHeight > 0 and $newHeight <= $this->maxHeight) or $this->errorMessage('Height out of range');
    !($cropToFit) or ($cropToFit and $newWidth and $newHeight) or $this->errorMessage('Crop to fit needs both width and height to work');
    if(is_null($saveAs)) {$saveAs = pathinfo($src)['extension'];}
    $pathToImage = $src;
    echo realpath(__DIR__ . '/cache' . $src);
    if($verbose) {
	$query = array();
	parse_str($_SERVER['QUERY_STRING'], $query);
	unset($query['verbose']);
	$url = '?' . http_build_query($query);
	echo "
	    <html lang='en'>
	    <meta charset='UTF-8'/>
	    <title>Image verbose mode</title>
	    <h1>Verbose mode</h1>
	    <p><a href=$url><code>$url</code></a><br>
	    <img src='{$url}' /></p>";
    }
    $imgInfo = $this->getImageInfo($pathToImage, $verbose);
    list($width, $height, $type, $attr) = getimagesize($pathToImage);
    //
    // Calculate new width and height for the image
    //
    $aspectRatio = $width / $height;
    if($cropToFit && $newWidth && $newHeight) {
	//Is handled during processing
    }
    else if($newWidth && !$newHeight) {
	$newHeight = round($newWidth / $aspectRatio);
	if($verbose){
	    $this->verbose("New width is known {$newWidth}, height is calculated to {$newHeight}."); 
	}
    }
    else if(!$newWidth && $newHeight) {
	$newWidth = round($newHeight * $aspectRatio);
	if($verbose){
	    $this->verbose("New height is known {$newHeight}, width is calculated to {$newWidth}.");
	}
    }
    else if($newWidth && $newHeight) {
	$ratioWidth  = $width  / $newWidth;
	$ratioHeight = $height / $newHeight;
	$ratio = ($ratioWidth > $ratioHeight) ? $ratioWidth : $ratioHeight;
	$newWidth  = round($width  / $ratio);
	$newHeight = round($height / $ratio);
	if($verbose){
	    $this->verbose("New width & height is requested, keeping aspect ratio results in {$newWidth}x{$newHeight}.");
	}
    }
    else {
	$newWidth = $width;
	$newHeight = $height;
	if($verbose) { $this->verbose("Keeping original width & heigth."); }
    }
    $cacheFileName = $this->createCacheName($pathToImage, $saveAs, $newWidth, $newHeight, $quality, $cropToFit, $sharpen, $verbose);
    if($ignoreCache || !$this->isCached($cacheFileName, filemtime($pathToImage), $verbose)){
	$image = $this->processImage($pathToImage, pathinfo($pathToImage)['extension'], $cropToFit, $width, $height, $newWidth, $newHeight, $sharpen, $verbose);
	$this->saveImage($image, $cacheFileName, $quality, $saveAs, $verbose);
    }
    return $cacheFileName;
}
}//EOC