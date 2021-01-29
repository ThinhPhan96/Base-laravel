<?php

namespace App\Service;

use Intervention\Image\Facades\Image;

class ImageProcessorService
{
    /**
     * Image
     *
     * @var Image $image
     */
    private $image;

    /**
     * ImageProcessor constructor.
     * @param String $image
     */
    public function __construct(string $image)
    {
        $this->image = Image::make($image);
    }

    /**
     * Get file extension
     * Because when upload file with PHP, PHP will rename to something like 'phpJHlKbK'
     * so file will have not extension available
     *
     * @return string
     */
    public function getExtension()
    {
        $mime = $this->image->mime();
        $extension = '';

        if ($mime == 'image/jpeg') {
            $extension = '.jpg';
        } elseif ($mime == 'image/png') {
            $extension = '.png';
        } elseif ($mime == 'image/gif') {
            $extension = '.gif';
        }

        return $extension;
    }

    /**
     * Save image
     *
     * @param String $path
     * @param int|null $quality
     * @return string|null
     */
    public function save(string $path, int $quality = null)
    {
        $this->image->save($path, $quality);

        return $this->image->basePath();
    }

    /**
     * Crop the image
     *
     * @param int|null $width
     * @param int|null $height
     * @param int|null $x
     * @param int|null $y
     */
    public function crop(?int $width, ?int $height, ?int $x = null, ?int $y = null)
    {
        $imageWidth = $this->image->width();
        $imageHeight = $this->image->height();
        $cropWidth = $imageWidth <= $width ? $imageWidth : $width;
        $cropHeight = $imageHeight <= $height ? $imageHeight : $height;

        $this->processSquareImage($cropWidth, $cropHeight, $x, $y);

        $canvas = Image::canvas($width, $height, IMAGE_BACKGROUND_COLOR);

        $canvas->insert($this->image, IMAGE_CANVAS_POSITION);

        $this->image = $canvas;
    }

    /**
     * Handle crop image
     *
     * @param $width
     * @param $height
     * @param null $x
     * @param null $y
     */
    public function processSquareImage($width, $height, $x = null, $y = null)
    {
        $this->image->crop($width, $height, $x, $y);
    }

    /**
     * Handle square image
     */
    public function square()
    {
        $imageWidth = $this->image->width();
        $imageHeight = $this->image->height();

        $min = min($imageWidth, $imageHeight);

        $this->processSquareImage($min, $min);
    }

    /**
     * Handle resize image
     *
     * @param int|null $width
     * @param int|null $height
     * @param int|null $x
     * @param int|null $y
     */
    public function resize(?int $width, ?int $height, ?int $x = null, ?int $y = null)
    {
        $imageWidth = $this->image->width();
        $imageHeight = $this->image->height();

        if ($imageWidth <= AVATAR_IMAGE_CROP_WIDTH || $imageHeight <= AVATAR_IMAGE_CROP_HEIGHT) {
            $this->crop($width, $height, $x, $y);
        } else {
            $this->image->resize($width, $height);
        }

    }
}
