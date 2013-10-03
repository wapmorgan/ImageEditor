<?php
/**
 * This is a simple image editor
 * @author wapmorgan (wapmorgan@gmail.com)
 */
class ImageEditor extends CComponent {

	/**
	 * Resource
	 */
	private $_image;

	/**
	 * Constructor.
	 * @param image_resource $image
	 */
	private function __construct($image) {
		$this->_image = $image;
	}

	/**
	 * Destroys an image resource to free memory
	 */
	public function __destruct() {
		imagedestroy($this->_image);
	}

	/**
	 * Returns image width
	 * @return int
	 */
	public function getWidth() {
		return imagesx($this->_image);
	}

	/**
	 * Returns image height
	 * @return int
	 */
	public function getHeight() {
		return imagesy($this->_image);
	}

	/**
	 * Copies an image fron another instance with zoom
	 * @param ImageEditor $source An image that will be source for resampling
	 */
	public function copyFrom(ImageEditor $source) {
		imagecopyresampled($this->_image, $source->resource, 0, 0, 0, 0, $this->width, $this->height, $source->width, $source->height);
	}

	/**
	 * Returns an image resource
	 */
	public function getResource() {
		return $this->_image;
	}

	/**
	 * Crops an image within sizes
	 * @param $x First x
	 * @param $y First y
	 * @param $x2 Second x
	 * @param $y2 Second y
	 */
	public function crop($x, $y, $x2, $y2) {
		// var_dump(func_get_args()); //#DEBUG
		$width = $x2 - $x;
		$height = $y2 - $y;
		$image = imagecreatetruecolor($width, $height);
		imagecopy($image, $this->_image, 0, 0, $x, $y, $width, $height);
		imagedestroy($this->_image);
		$this->_image = $image;
	}

	/**
	 * Crops a one of four sides (top, right, bottom, left)
	 * @param string $side Side to be cropped
	 * If top or bottom, image height will be changed; if left or right, image width will be changed.
	 * If top, deletion process will start from the bottom.
	 * @param int $size New side size
	 */
	public function cropSide($side, $size) {
		$x = $y = 0;
		$x2 = $this->width;
		$y2 = $this->height;
		// var_dump($x, $y, $x2, $y2); //#DEBUG
		switch ($side) {
			case 'top':
				$y2 -= $y2 - $size;
				break;
			case 'right':
				$x += $size;
				break;
			case 'bottom':
				$y += $size;
				break;
			case 'left':
				$x2 -= $size;
				break;
		}
		// var_dump($x, $y, $x2, $y2); //#DEBUG
		// return -1; //#DEBUG
		$this->crop($x, $y, $x2, $y2);
	}

	/**
	 * Resizes an image
	 * @param int $width New image width
	 * @param int $height New image height
	 */
	public function resize($width, $height) {
		$image = imagecreatetruecolor($width, $height);
		imagecopyresampled($image, $this->_image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
		imagedestroy($this->_image);
		$this->_image = $image;
	}

	/**
	 * Zooms an image by width
	 * @param int $size New value of width
	 */
	public function zoomWidthTo($size) {
		if ($this->width <= $size)
			return;
		$ratio = round($this->width / $size, 3);
		$this->resize($this->width / $ratio, $this->height / $ratio);
	}

	/**
	 * Zooms an image by height
	 * @param int $size New value of height
	 */
	public function zoomHeightTo($size) {
		if ($this->height <= $size)
			return;
		$ratio = round($this->height / $size, 3);
		$this->resize($this->width / $ratio, $this->height / $ratio);
	}

	/**
	 * Adds an image to current image.
	 * @param string $side Side
	 * @param ImageEditor $appendix
	 */
	public function appendImageTo($side, ImageEditor $appendix) {
		switch ($side) {
			case 'top':
			case 'bottom':
				//#TODO: what will be if $appendix->width is NOT equal $this->width
				$image = imagecreatetruecolor($this->width, $this->height + $appendix->height);
				break;
			case 'left':
			case 'right':
				//#TODO: what will be if $appendix->height is NOT equal $this->height
				$image = imagecreatetruecolor($this->width + $appendix->width, $this->height);
				break;
		}

		// imagecopyresampled(dst_image, src_image, dst_x, dst_y, src_x, src_y, dst_w, dst_h, src_w, src_h)
		switch ($side) {
			case 'bottom':
				imagecopyresampled($image, $this->_image, 0, 0, 0, 0, $this->width, $this->height, $this->width, $this->height);
				imagecopyresampled($image, $appendix->resource, 0, $this->height, 0, 0, $appendix->width, $appendix->height, $appendix->width, $appendix->height);
				break;
		}

		imagedestroy($this->_image);
		$this->_image = $image;
	}

	/**
	 * Places another image atop current image
	 * @param int $x X-position
	 * @param int $y Y-position
	 * @param ImageEditor $image Image to place
	 */
	public function placeImageAt($x, $y, ImageEditor $image) {
		imagecopy($this->_image, $image->resource, $x, $y, 0, 0, $image->width, $image->height);
	}

	/**
	 * Places another image in the center of current image
	 * @param ImageEditor $image Image to place
	 */
	public function placeImageAtCenter(ImageEditor $image) {
		$this->placeImageAt($this->width / 2 - $image->width / 2,  $this->height / 2 - $image->height / 2, $image);
	}

	/**
	 * Rotates an image
	 * @param mixed $angle An angle to rotate in degrees.
	 * Also you can pass true or false to rotate
	 * 90 degress and -90 degrees.
	 * @param int $bgColor Color of uncovered zone {@see http://www.php.net/manual/en/function.imagerotate.php}
	 * @todo Check functionality
	 */
	public function rotate($angle, $bgColor = -1) {
		if ($angle === true)
			$angle = 90;
		elseif ($angle === false)
			$angle = -90;
		imagerotate($this->_image, $angle, $bgColor);
	}

	/**
	 * Saves an image to file
	 * @param string $filename Filename
	 * @param strng $format Image format (e.g. jpeg, png)
	 * @param int $quality Image quality. An abstract value between 0 (worst) and 100 (best).
	 * When you save to png, it automatically transfers to appliable value.
	 * If not set, save function will be called without passing quality param.
	 */
	public function saveToFile($filename, $format, $quality = null) {
		switch ($format) {
			case 'jpeg':
				if ($quality === null)
					imagejpeg($this->_image, $filename);
				else
					imagejpeg($this->_image, $filename, $quality);
				break;
			case 'png':
				if ($quality === null)
					imagepng($this->_image, $filename);
				else {
					$quality = 9 - floor($quality / 11);
					var_dump($quality);
					imagepng($this->_image, $filename, $quality);
				}
				break;
			default:
				throw new Exception('Unknown (format) "'.$format.'"!');
		}
	}

	/**
	 * Creates an instance from existing file
	 * @param string $filename
	 * Note that gif animation will be destroyed.
	 */
	static public function createFromFile($filename) {
		if (!file_exists($filename))
			throw new Exception('Invalid (filename) parameter! File does\' not exist!');

		$image_info = getimagesize($filename);
		if ($image_info === false)
			throw new Exception('Invalid (filename) parameter! File is corrupted!');

		switch ($image_info[2]) {
			case IMAGETYPE_JPEG:
			case IMAGETYPE_JPEG2000:
				$image = imagecreatefromjpeg($filename);
				break;
			case IMAGETYPE_PNG:
				$image = imagecreatefrompng($filename);
				break;
			case IMAGETYPE_GIF:
				$image = imagecreatefromgif($filename);
				break;
			default:
				throw new Exception('Unknown image format!');
				break;
		}

		return new self($image);
	}

	/**
	 * Creates an instance with specified size
	 * @param int $width Image width
	 * @param int $height Image height
	 */
	static public function createWithSize($width, $height) {
		$image = imagecreatetruecolor($width, $height);
		return new self($image);
	}
}
