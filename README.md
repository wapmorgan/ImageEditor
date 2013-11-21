**ImageEditor** is a wrapper of gd functions.

**How to start**:
Create from existing file or make an empty area
```php
$image = ImageEditor::createFromFile($filename);
// or
$image = ImageEditor::createWithSize($width, $height);
```

## Properties:
1. **width** - width of image
2. **height** - height of image
3. **resource** - original gd-resource of image (you can pass it directly to image-gd functions)


## Operations
### Resize:
1. **resize($width, $height)** - resizes an image to `$width` X `$height`
2. **zoomWidthTo($size)** - decreases proportionally image width to `$size`
3. **zoomHeightTo($size)** - decreases proportionally image height to `$size`

### Crop:
1. **crop($x, $y, $x2, $y2)** - cuts a rectangular piece of image
2. **cropSide($side, $size)** - changes the size of `$side` (`top|bottom|left|right`) to `$size`.

### Manipulation:
1. **appendImageTo($side, ImageEditor $appendix)** - appends an image (`$appendix`) to current image at `$side` (`top|bottom|left|right`).
**Warning**: Function `appendImageTo` is not finished!
2. **placeImageAt($x, $y, ImageEditor $image)** - places an image atop current image at `$x` X `$y`.
3. **placeImageAtCenter(ImageEditor $image)** - places an image in the center of current image.
4. **rotate($angle, $bgColor)** - rotates an image.
**Warning**: Function `rotate` works unpredictable!

### Save
**saveToFile($filename, $format, $quality)** - saves image to disk.
Possible `$format` values: jpeg, png.
Quality is an integer value between 0 (worst) and 100 (best).
