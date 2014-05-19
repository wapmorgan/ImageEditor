<?php
if (!class_exists('CComponent', false))
	require_once dirname(__FILE__).'/CComponent.php';

if (!function_exists('imagecreatefrombmp'))
	require_once dirname(__FILE__).'/function.imagecreatefrombmp.php';

/**
 * This is a simple image editor
 * @author wapmorgan (wapmorgan@gmail.com)
 * @link https://github.com/wapmorgan/ImageEditor
 * @link https://github.com/wapmorgan/ImageEditor-test
 * @link https://github.com/wapmorgan/ImageEditor-doc
 * @link http://wapmorgan.github.io/ImageEditor-doc/classes/ImageEditor.html
 */
class ImageEditor extends CComponent {

	const ZOOM_IF_LARGER = 1;

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
	 * Creates a new gd-resource.
	 */
	public function __clone() {
		$source = $this->_image;
		$width = imagesx($source);
		$height = imagesy($source);
		$this->_image = imagecreatetruecolor($width, $height);
		imagecopy($this->_image, $source, 0, 0, 0, 0, $width, $height);
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
	 * @return ImageEditor this object
	 */
	public function copyFrom(ImageEditor $source) {
		imagecopyresampled($this->_image, $source->resource, 0, 0, 0, 0, $this->width, $this->height, $source->width, $source->height);
		return $this;
	}

	/**
	 * Returns an image resource
	 */
	public function getResource() {
		return $this->_image;
	}

	/**
	 * Cuts a rectangular piece of image
	 * @param $x First x
	 * @param $y First y
	 * @param $x2 Second x
	 * @param $y2 Second y
	 * @return ImageEditor this object
	 */
	public function crop($x, $y, $x2, $y2) {
		$width = $x2 - $x;
		$height = $y2 - $y;
		$image = imagecreatetruecolor($width, $height);
		imagecopy($image, $this->_image, 0, 0, $x, $y, $width, $height);
		imagedestroy($this->_image);
		$this->_image = $image;
		return $this;
	}

	/**
	 * Deletes a piece of image from specific side.
	 * For example, if side=top and size=100, 100px from top will be deleted.
	 * @param string $side Side to cut
	 * @param int $size Pixels
	 * @return ImageEditor this object
	 */
	public function decreaseSide($side, $size) {
		$x = $y = 1;
		$x2 = $this->width;
		$y2 = $this->height;
		switch ($side) {
			case 'top':
				$y = $size;
				break;
			case 'right':
				$x2 -= $size;
				break;
			case 'bottom':
				$y2 -= $size;
				break;
			case 'left':
				$x = $size;
				break;
		}
		$this->crop($x, $y, $x2, $y2);
		return $this;
	}

	/**
	 * Resizes an image to new width & height
	 * @param int $width New image width
	 * @param int $height New image height
	 * @return ImageEditor this object
	 */
	public function resize($width, $height) {
		$image = imagecreatetruecolor($width, $height);
		imagecopyresampled($image, $this->_image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
		imagedestroy($this->_image);
		$this->_image = $image;
		return $this;
	}

	/**
	 * Changes proportionally image width to new size
	 * @param int $size New value of width
	 * @return ImageEditor this object
	 */
	public function zoomWidthTo($size) {
		$ratio = round($this->width / $size, 3);
		$this->resize($size, $this->height / $ratio);
		return $this;
	}

	/**
	 * Changes proportionally image height to new size
	 * @param int $size New value of height
	 * @return ImageEditor this object
	 */
	public function zoomHeightTo($size) {
		$ratio = round($this->height / $size, 3);
		$this->resize($this->width / $ratio, $size);
		return $this;
	}

	/**
	 * Decreases proportionally image width to new size, if needed
	 * @param int $size New value of width
	 * @return ImageEditor this object
	 */
	public function decreaseWidthTo($size) {
		if ($this->width <= $size)
			return;
		$this->zoomWidthTo($size);
		return $this;
	}

	/**
	 * Decreases proportionally image height to new size, if needed
	 * @param int $size New value of height
	 * @return ImageEditor this object
	 */
	public function decreaseHeightTo($size) {
		if ($this->height <= $size)
			return;
		$this->zoomHeightTo($size);
		return $this;
	}

	/**
	 * Decreases proportionally larger side to new size, if needed
	 * @param int $size New max value of sides
	 * @return ImageEditor this object
	 */
	public function decreaseTo($size) {
		$currentSize = max($this->height, $this->width);
		if ($currentSize <= $size)
			return;
		$ratio = round($currentSize / $size, 3);
		$this->resize($this->width / $ratio, $this->height / $ratio);
		return $this;
	}

	/**
	 * Appends an image to current image.
	 * @param string $side Side
	 * @param ImageEditor $appendix
	 * @param int $modifiers Set of modifiers.
	 * Modifiers:
	 * 	+ ImageEditor::ZOOM_IF_LARGER - appendix height will be zoomed (not resized) if it's larger than current image's one (when appending to left or right side); appendix width will be zoomed (not resized) if it's larger than current image's one (when appending to top or bottom side);
	 * @return ImageEditor this object
	 */
	public function appendImageTo($side, ImageEditor $appendix, $modifiers = 0) {
		$appendix = clone $appendix;
		switch ($side) {
			case 'top':
			case 'bottom':
				// fix appendix width if needed
				if ($appendix->width != $this->width) {
					if ($appendix->width > $this->width || $modifiers & self::ZOOM_IF_LARGER)
						$appendix->zoomWidthTo($this->width);
					elseif ($appendix->width < $this->width)
						$appendix->resize($this->width, $appendix->height);
				}
				$image = imagecreatetruecolor($this->width, $this->height + $appendix->height);
				break;
			case 'left':
			case 'right':
				// fix appendix height if needed
				if ($appendix->height != $this->height) {
					if ($appendix->height > $this->height || $modifiers & self::ZOOM_IF_LARGER)
						$appendix->zoomHeightTo($this->height);
					else
						$appendix->resize($appendix->width, $this->height);
				}
				$image = imagecreatetruecolor($this->width + $appendix->width, $this->height);
				break;
		}

		// imagecopyresampled(dst_image, src_image, dst_x, dst_y, src_x, src_y, dst_w, dst_h, src_w, src_h)
		switch ($side) {
			case 'top':
				imagecopyresampled($image, $appendix->resource, 0, 0, 0, 0, $appendix->width, $appendix->height, $appendix->width, $appendix->height);
				imagecopyresampled($image, $this->_image, 0, $appendix->height, 0, 0, $this->width, $this->height, $this->width, $this->height);
				break;
			case 'bottom':
				imagecopyresampled($image, $this->_image, 0, 0, 0, 0, $this->width, $this->height, $this->width, $this->height);
				imagecopyresampled($image, $appendix->resource, 0, $this->height, 0, 0, $appendix->width, $appendix->height, $appendix->width, $appendix->height);
				break;
			case 'left':
				imagecopyresampled($image, $appendix->resource, 0, 0, 0, 0, $appendix->width, $appendix->height, $appendix->width, $appendix->height);
				imagecopyresampled($image, $this->_image, $appendix->width, 0, 0, 0, $this->width, $this->height, $this->width, $this->height);
				break;
			case 'right':
				imagecopyresampled($image, $this->_image, 0, 0, 0, 0, $this->width, $this->height, $this->width, $this->height);
				imagecopyresampled($image, $appendix->resource, $this->width, 0, 0, 0, $appendix->width, $appendix->height, $appendix->width, $appendix->height);
				break;
		}

		imagedestroy($this->_image);
		$this->_image = $image;
		return $this;
	}

	/**
	 * Places another image atop current image
	 * @param int $x X-position
	 * @param int $y Y-position
	 * @param ImageEditor $image Image to place
	 * @return ImageEditor this object
	 */
	public function placeImageAt($x, $y, ImageEditor $image) {
		imagecopy($this->_image, $image->resource, $x, $y, 0, 0, $image->width, $image->height);
		return $this;
	}

	/**
	 * Places another image in the center of current image
	 * @param ImageEditor $image Image to place
	 * @return ImageEditor this object
	 */
	public function placeImageAtCenter(ImageEditor $image) {
		$this->placeImageAt($this->width / 2 - $image->width / 2,  $this->height / 2 - $image->height / 2, $image);
		return $this;
	}

	/**
	 * Rotates an image
	 * @param mixed $angle An angle to rotate in degrees.
	 * Also you can pass true or false to rotate
	 * 90 degress and -90 degrees.
	 * @param int $bgColor Color of uncovered zone {@see http://www.php.net/manual/en/function.imagerotate.php}
	 * @return ImageEditor this object
	 */
	public function rotate($angle, $bgColor = 0) {
		if ($angle === true)
			$angle = 90;
		elseif ($angle === false)
			$angle = -90;
		$this->_image = imagerotate($this->_image, 360 - $angle, $bgColor);
		return $this;
	}

	/**
	 * Saves an image to file
	 * @see save()
	 * @deprecated
	 */
	public function saveToFile() {
		return call_user_func_array(array($this, 'save'), func_get_args());
	}

	/**
	 * Saves an image to file
	 * @param string $filename Filename
	 * @param mixed $format Image format. Can be either a string or a defined constant IMAGETYPE_xxx
	 * Formats:
	 * + jpeg (or jpg)
	 * + png
	 * + gif
	 * + wbmp
	 * @param int $quality Image quality. An abstract value between 0 (worst) and 100 (best).
	 * When you save to png, it automatically transfers to appliable value.
	 * If not set, save function will be called without passing quality param.
	 * @return ImageEditor this object
	 */
	public function save($filename, $format, $quality = null) {
		switch ($format) {
			case IMAGETYPE_JPEG:
			case 'jpeg':
			case 'jpg':
				if ($quality === null)
					imagejpeg($this->_image, $filename);
				else
					imagejpeg($this->_image, $filename, $quality);
				break;
			case IMAGETYPE_PNG:
			case 'png':
				if ($quality === null)
					imagepng($this->_image, $filename);
				else {
					$quality = 9 - floor($quality / 11);
					imagepng($this->_image, $filename, $quality);
				}
				break;
			case IMAGETYPE_GIF:
			case 'gif':
				imagegif($this->_image, $filename);
				break;
			case IMAGETYPE_WBMP:
			case 'wbmp':
				imagewbmp($this->_image, $filename);
				break;
			default:
				throw new Exception('Unknown (format) "'.$format.'"!');
				break;
		}
		return $this;
	}

	/**
	 * Flips an image horizontally.
	 * @return ImageEditor this object
	 */
	public function horizontalFlip() {
		if (function_exists('imageflip')) {
			imageflip($this->_image, IMG_FLIP_HORIZONTAL);
		} else {
			$image = imagecreatetruecolor($this->width, $this->height);
			$dst_y = 0;
			$src_y = 0;
			$coordinate = ($this->width - 1);
			foreach (range($this->width, 0) as $range) {
				$src_x = $range;
				$dst_x = $coordinate - $range;
				imagecopy($image, $this->_image, $dst_x, $dst_y, $src_x, $src_y, 1, $this->height);
			}
			imagedestroy($this->_image);
			$this->_image = $image;
		}
		return $this;
	}

	/**
	 * Flips an image vertically.
	 * @return ImageEditor this object
	 */
	public function verticalFlip() {
		if (function_exists('imageflip')) {
			imageflip($this->_image, IMG_FLIP_VERTICAL);
		} else {
			$image = imagecreatetruecolor($this->width, $this->height);
			$dst_x = 0;
			$src_x = 0;
			$coordinate = ($this->height - 1);
			foreach (range($this->height, 0) as $range) {
				$src_y = $range;
				$dst_y = $coordinate - $range;
				imagecopy($image, $this->_image, $dst_x, $dst_y, $src_x, $src_y, $this->width, 1);
			}
			imagedestroy($this->_image);
			$this->_image = $image;
		}
		return $this;
	}

	/**
	 * Creates an instance from existing file
	 * @param string $filename
	 * Note that gif animation will be destroyed.
	 */
	static public function createFromFile($filename) {
		if (!file_exists($filename))
			throw new Exception('Invalid (filename) parameter! File does not exist!');

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
			case IMAGETYPE_WBMP:
				$image = imagecreatefromwbmp($filename);
				break;
			case IMAGETYPE_BMP:
				$image = imagecreatefrombmp($filename);
				break;
			default:
				throw new Exception('Unknown image format!');
				break;
		}

		return new self($image);
	}

	/**
	 * Tries to create an instance from file.
	 * If success, returns instance. Otherwise, null.
	 * It is a shortcut in this use:
	 * <code>
	 * if (($image = ImageEditor::tryCreateFromFile($filename)) === null) {
	 *     throw new CHttpException(400);
	 * }
	 * </code>
	 */
	static public function tryCreateFromFile($filename) {
		try {
			$instance = self::createFromFile($filename);
			return $instance;
		} catch (\Exception $exception) {
			return null;
		}
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

	/**
	 * Creates an instace from gd-resource
	 * @param resource $resource Image resource
	 */
	static public function createFromResource($resource) {
		if (!is_resource($resource))
			throw new Exception('Invalid (resource) parameter! This is not a resource!');
		if (get_resource_type($resource) != 'gd')
			throw new Exception('Invalid (resource) parameter! Resource is not a gd resource!');
		return new self($resource);
	}
}
