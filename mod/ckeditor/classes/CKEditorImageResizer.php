<?php

/**
 * Resize an image and save it
 */
class CKEditorImageResizer {

	protected $maximumDimension;

	public function __construct($maximumDimension) {
		$this->maximumDimension = $maximumDimension;
	}

	public function process($srcFilePath, $destFilePath, $format = 'jpeg') {
		$srcImage = $this->read($srcFilePath);
		if (!$srcImage) {
			error_log('failed to read input image');
			return false;
		}

		$destSize = $this->calculateSize($srcImage);
		$destImage = $this->create($destSize);
		if (!$destImage) {
			error_log('failed to create output image');
			imagedestroy($srcImage);
			return false;
		}

		if (!$this->resize($srcImage, $destImage)) {
			error_log('failed to resize');
			return false;
		}
		imagedestroy($srcImage);

		$result = $this->save($destImage, $destFilePath, $format);
		if (!$result) {
			error_log('failed to save new image');
		}
		imagedestroy($destImage);
		return $result;
	}

	protected function read($path) {
		$handle = fopen($path, 'r');
		if (false === $handle) {
			return null;
		}

		$content = stream_get_contents($handle);
		if (false === $content) {
			fclose($handle);
			return null;
		}
		fclose($handle);

		$image = imagecreatefromstring($content);
		if (!is_resource($image)) {
			return null;
		}

		return $image;
	}

	protected function create(CKEditorImageSize $size) {
		$image = imagecreatetruecolor($size->width, $size->height);
		if (false === $image) {
			return null;
		}
		return $image;
	}

	protected function calculateSize($srcImage) {
		$size = new CKEditorImageSize();
		$srcWidth = imagesx($srcImage);
		$srcHeight = imagesy($srcImage);
		
		$widthRatio = $this->maximumDimension / $srcWidth;
		$heightRatio = $this->maximumDimension / $srcHeight;
		$ratio = min(1, $widthRatio, $heightRatio);
		$size->width = (int)floor($ratio * $srcWidth);
		$size->height = (int)floor($ratio * $srcHeight);
		return $size;
	}

	protected function resize($srcImage, $destImage) {
		imagealphablending($srcImage, true);
		imagealphablending($destImage, true);

		$sw = imagesx($srcImage);
		$sh = imagesy($srcImage);
		$dw = imagesx($destImage);
		$dh = imagesy($destImage);
		return imagecopyresampled($destImage, $srcImage, 0, 0, 0, 0, $dw, $dh, $sw, $sh);
	}

	protected function save($image, $filePath, $format) {
		$saveFunction = "image$format";
		
		$args = array($image, $filePath);
		if ($format == 'jpeg') {
			array_push($args, 75);
		} else {
			// png - compression high
			array_push($args, 9);
		}
		return call_user_func_array($saveFunction, $args);
	}
}
