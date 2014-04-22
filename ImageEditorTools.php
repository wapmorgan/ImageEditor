<?php
class ImageEditorTools {
	/**
	 * Calculates Perceptual hash of image
	 * @param ImageEditor $image
	 * @param array $sizes Sizes. Defaults to array(8, 8)
	 * @return string
	 */
	static public function pHash(ImageEditor $image, $sizes = array(8, 8)) {
		$image = clone $image;
		$image->resize($sizes[0], $sizes[1]);
		imagefilter($image->resource, IMG_FILTER_GRAYSCALE);

		$x = $image->width - 1;
		$y = $image->height - 1;
		$sum = array("r" => 0, "g" => 0, "b" => 0);
		for ($i = 0; $i <= $y; $i++) {
			for ($j = 0; $j <= $x; $j++) {
				$color = imagecolorat($image->resource, $j, $i);
				$colors = imagecolorsforindex($image->resource, $color);
				$sum["r"] += $colors["red"];
				$sum["g"] += $colors["green"];
				$sum["b"] += $colors["blue"];
			}
		}

		$pixels = $image->width * $image->height;
		var_dump($sum, $pixels);
		$average = array("r" => ceil($sum["r"] / $pixels), "g" => ceil($sum["g"] / $pixels), "b" => ceil($sum["b"] / $pixels));
		var_dump($average);
		$average = hexdec(dechex($average["r"]).dechex($average["g"]).dechex($average["b"]));
		var_dump($average);

		// hash
		$hash = null;
		for ($i = 0; $i <= $y; $i++) {
			for ($j = 0; $j <= $x; $j++) {
				$color = imagecolorat($image->resource, $j, $i);
				if ($color > $average) {
					$hash .= 1;
				} else {
					$hash .= 0;
				}
			}
		}
		return base_convert($hash, 2, 16);
	}
}
