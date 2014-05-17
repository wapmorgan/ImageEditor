**ImageEditor** is a wrapper of gd functions.
Earlier it was working only in yii-application context, now you can use *the editor* in any application.

## Installation
* Composer package: `wapmorgan/image-editor` [[1](https://packagist.org/packages/wapmorgan/image-editor)]
* Archive: `https://github.com/wapmorgan/ImageEditor/archive/master.zip`

**How to start**:
Create from existing file or make an empty area
```php
$image = ImageEditor::createFromFile($filename);
// or
$image = ImageEditor::createWithSize($width, $height);
// or
$image = ImageEditor::createFromResource(imagecreatetruecolor(90, 90));
```

## Properties:
1. **width** - width of image
2. **height** - height of image
3. **resource** - original gd-resource of image (you can pass it directly to image-gd functions)

## Operations
### Resize && Zoom (don't cut image)
**can minimize and maximize image**:

1. **zoomWidthTo(int $size)** - changes proportionally image width to `$size`
2. **zoomHeightTo(int $size)** - changes proportionally image height to `$size`

**can only minimize image**:

1. **decreaseWidthTo(int $size)** - decreases proportionally image width to `$size`, if needed
2. **decreaseHeightTo(int $size)** - decreases proportionally image height to `$size`, if needed
3. **decreaseTo(int $size)** - decreases proportionally larger side to `$size`, if needed

**can do everything you ask it for**:

1. **resize(int $width, int $height)** - resizes an image to `$width` X `$height`

### Crop (can cut image)
1. **crop($x, $y, $x2, $y2)** - cuts a rectangular piece of image
2. **decreaseSide($side, int $size)** - deletes a piece of image from specific side. For example, if $side=top and $size=100, 100px from top will be deleted.

### Rotation && Mirroring
1. **rotate($angle, $bgColor = 0)** - rotates an image. `True` equals 90°, `False` equals -90°.
2. **horizontalFlip()** - flips an image horizontally.
3. **verticalFlip()** - flips an image vertically.

### Manipulation:
1. **appendImageTo($side, ImageEditor $appendix, int $modifiers)** - appends an image (`$appendix`) to current image at `$side` (`top|bottom|left|right`).
2. **placeImageAt($x, $y, ImageEditor $image)** - places an image atop current image at `$x` X `$y`.
3. **placeImageAtCenter(ImageEditor $image)** - places an image in the center of current image.

### Save
1. **saveToFile($filename, $format, $quality)** - saves image to disk.
Possible `$format` values: jpeg, png, gif, wbmp.
Quality is an integer value between 0 (worst) and 100 (best).

### Links
* ImageEditor repo: https://github.com/wapmorgan/ImageEditor
* ImageEditor testing script: https://github.com/wapmorgan/ImageEditor-test
* ImageEditor docs: https://github.com/wapmorgan/ImageEditor-doc
* ImageEditor class reference (API): http://wapmorgan.github.io/ImageEditor-doc/classes/ImageEditor.html
