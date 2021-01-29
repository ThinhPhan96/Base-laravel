<?php

namespace App\Service;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageUploadService
{
    /**
     * Image file
     *
     * @var UploadedFile $image
     */
    private $image;

    /**
     * Image type
     *
     * @var String $type
     */
    private $type;

    /**
     * Contains option for image storage
     *
     * @var array
     */
    private $options;

    /**
     * Image file storage name
     *
     * @var String $storageName
     */
    private $storageName;

    /**
     * ImageUpload constructor.
     *
     * @param UploadedFile $image
     * @param string $type
     * @param array|null $options
     */
    public function __construct(UploadedFile $image, string $type, array $options = null)
    {
        $this->image = $image;
        $this->type = $type;
        $this->options = $options;
    }

    /**
     * Handle upload
     *
     * @throws Exception
     */
    public function upload()
    {
        $this->validate();

        if (!empty($this->options)) {
            return $this->uploadWithOptions();
        }

        $this->storageName = Storage::disk($this->getDisk())->putFileAs(
            $this->makeStoragePath(),
            $this->image,
            $this->makeStorageName()
        );

        return $this->exists($this->storageName);
    }

    /**
     * Handle upload image if have options
     *
     * @return bool
     */
    public function uploadWithOptions()
    {
        $cropWidth = $this->options['cropWidth'] ?? null;
        $cropHeight = $this->options['cropHeight'] ?? null;
        $resizeWidth = $this->options['resizeWidth'] ?? null;
        $resizeHeight = $this->options['resizeHeight'] ?? null;
        $cropX = $this->options['cropX'] ?? null;
        $cropY = $this->options['cropY'] ?? null;
        $quality = $this->options['quality'] ?? null;
        $square = $this->options['square'] ?? false;

        $process = new ImageProcessorService($this->image);

        if($square) {
            $process->square();
        }

        if ($resizeWidth && $resizeHeight) {
            $process->resize($resizeWidth, $resizeHeight, $cropX, $cropY);
        } elseif ($cropWidth && $cropHeight) {
            $process->crop($cropWidth, $cropHeight, $cropX, $cropY);
        }

        $this->storageName = $process->save(
            implode('/', [$this->makeStoragePath(), $this->makeStorageName()]),
            $quality
        );

        return $this->exists($this->storageName);
    }

    /**
     * Validate image file
     *
     * @throws Exception
     */
    public function validate()
    {
        $message = null;

        if (!$this->checkType()) {
            $message = __('messages.upload.type_not_allowed', [
                'type' => $this->type
            ]);
        } elseif (!$this->checkExtension()) {
            $message = __('messages.upload.extension_allowed', [
                'extension' => implode('/', $this->getExtensionsAllowed())
            ]);
        } elseif (!$this->checkSize()) {
            $message = __('messages.upload.max_size', [
                'max' => $this->getSizeAllowed()
            ]);
        }

        if ($message) {
            throw new Exception($message);
        }
    }

    /**
     * Get upload disk
     *
     * @return string
     */
    public function getDisk()
    {
        return UPLOAD_IMAGE_DISK;
    }

    /**
     * Get all type supported
     *
     * @return string[]
     */
    public function getTypes()
    {
        return UPLOAD_IMAGE_TYPES_ALLOWED;
    }

    /**
     * Get current upload type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get file storage name
     *
     * @return string
     */
    public function getStorageName()
    {
        return $this->storageName;
    }

    /**
     * Get path to storage that can access from browser
     *
     * @return string
     */
    public function getStorageFolder()
    {
        return UPLOAD_IMAGE_FOLDER;
    }

    /**
     * Make storage path
     *
     * @return String
     */
    public function makeStoragePath()
    {
        return $this->trimPath($this->getStorageFolder() . '/' . $this->getType());
    }

    /**
     * Get path to image file
     *
     * @return string
     */
    public function getPath()
    {
        return $this->trimPath($this->getStorageName());
    }

    /**
     * Trim path
     *
     * @param mixed $path
     * @return String
     */
    public function trimPath($path)
    {
        if(!is_string($path)) {
            return null;
        }

        $arrPath = [];

        foreach (explode('/', $path) as $path) {
            if (trim($path)) {
                $arrPath[] = $path;
            }
        }

        return implode('/', $arrPath);
    }

    /**
     * Get size allowed
     *
     * @return mixed
     */
    public function getSizeAllowed()
    {
        return UPLOAD_IMAGE_SIZE_ALLOWED;
    }

    /**
     * Get image file size
     *
     * @return int
     */
    public function getSize()
    {
        if (!$this->image) {
            return 0;
        }

        return $this->image->getSize();
    }

    /**
     * Check size
     *
     * @return bool
     */
    public function checkSize()
    {
        return $this->getSize() <= $this->getSizeAllowed() * 1000;
    }

    /**
     * Get list extensions allowed
     *
     * @return string[]
     */
    public function getExtensionsAllowed()
    {
        return UPLOAD_IMAGE_EXTENSIONS_ALLOWED;
    }

    /**
     * Get current extension image
     *
     * @return string|null
     */
    public function getExtension()
    {
        if (!$this->image) {
            return null;
        }

        return $this->image->extension();
    }

    /**
     * Check if extension is allowed
     *
     * @return bool
     */
    public function checkExtension()
    {
        return in_array($this->getExtension(), $this->getExtensionsAllowed());
    }

    /**
     * Get current upload type
     *
     * @return bool
     */
    public function checkType()
    {
        return in_array($this->getType(), $this->getTypes());
    }

    /**
     * Make file storage name
     *
     * @return String|null
     */
    private function makeStorageName()
    {
        if (!$this->image) {
            return null;
        }

        return time() . '_' . generateRandomNumber(100000, 999999, 3) . '.' . $this->getExtension();
    }

    /**
     * Remove file
     *
     * @param string|null $path
     * @return bool
     */
    public function remove(?string $path)
    {
        return Storage::disk($this->getDisk())->delete($path ?? $this->getPath());
    }

    /**
     * Check if file is exist
     *
     * @param string|null $path
     * @return bool
     */
    public function exists(string $path = null)
    {
        return Storage::disk($this->getDisk())->exists($path);
    }
}
