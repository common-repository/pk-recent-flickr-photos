<?php
$url = '';
$width = 75;
$height = 75;

// check whether url querystring is sent
if(isset($_GET['url'])) {
	$url = $_GET['url'];
	
	if( isset($_GET['width']) && is_numeric($_GET['width']) ) $width = $_GET['width'];
	if(isset($_GET['height']) && is_numeric($_GET['height']) ) $height = $_GET['height'];
	
	generateImage($url, $width, $height);
}

/*
 * Generate an $width x $height px image
 * from the original image given by the $url
 * @return 			a jpeg MIME type
 * @param	$width	width of the destination image
 * @param 	$height	height of the destination image
 *
 * @author 			Saophalkun Ponlu
 * @email			phalkunz@gmail.com
 * @website			http://phalkunz.com
 * @date			Jan, 27 2008
 */
function generateImage($url, $width=75, $height=75) {
	
	$filename = $url;
	// scaling factor
	$factor = 1;
	// destination image's dimensions
	$dWidth = $width;
	$dHeight = $height;
	
	// get the dimensions of the source image
	list($sWidth, $sHeight) = getimagesize($filename);
	
	// copy source image's dimensions
	// for later calculation
	$rWidth = $sWidth;
	$rHeight = $sHeight;
	
	// width scaling factors
	$wFactor = $dWidth / $sWidth;
	// width scaling factors
	$hFactor = $dHeight / $sHeight;
	
	// choose the factor that has the greater value
	if ($wFactor>$hFactor) {
		$factor = $wFactor;
	}
	else {
		$factor = $hFactor;
	}
	
	// calculate the resize dimensions of the orginal image
	$rWidth *= $factor;
	$rHeight *= $factor;
	
	// position the image to the center
	$rX = ($dWidth - $rWidth) / 2;
	$rY = ($dHeight - $rHeight) / 2;
	
	header("Content-type: image/jpeg");
	
	$srcImg     = imagecreatefromjpeg($filename);
	$dstImg = imagecreatetruecolor($dWidth, $dHeight);
	
	// copy & resize
	imagecopyresampled($dstImg, $srcImg, $rX, $rY, 0, 0, $rWidth, $rHeight, $sWidth, $sHeight);
	
	// output image
	imagepng($dstImg);
	
	// destroy all image resources
	imagedestroy($srcImg);
	imagedestroy($dstImg);
}

?>

