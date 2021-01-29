<?php

namespace App\Service;

use Illuminate\Http\UploadedFile;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Storage;

class UploadFile
{
    /**
     * @var UploadedFile
     */
    private $file;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $storageName;

    /**
     * @var string
     */
    private $originalName;

    /**
     * Set file
     *
     * @param $file
     * @return UploadFile
     */
    public function setFile($file): UploadFile
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file
     *
     * @return UploadedFile
     */
    public function getFile(): UploadedFile
    {
        return $this->file;
    }

    /**
     * Get original name of file
     *
     * @return string|null
     */
    public function getOriginalName()
    {
        if (!$this->getFile()) {
            return null;
        }

        return $this->getFile()->getClientOriginalName();
    }

    /**
     * Set type
     *
     * @param string $type
     * @return $this
     */
    public function setType(string $type): UploadFile
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Handle validate
     *
     * @throws CustomException
     */
    public function validate()
    {
        $message = null;

        if (!$this->checkType()) {
            $message = __('messages.upload.type_not_allowed', [
                'type' => $this->type
            ]);
        } elseif (!$this->checkSize()) {
            $message = __('messages.upload.max_size', [
                'max' => $this->getSizeAllowed()
            ]);
        }

        if ($message) {
            throw new CustomException($message);
        }
    }

    /**
     * Get upload disk
     *
     * @return string
     */
    public function getDisk(): string
    {
        return UPLOAD_DISK;
    }

    /**
     * Get all type supported
     *
     * @return string[]
     */
    public function getTypes(): array
    {
        return UPLOAD_TYPES_ALLOWED;
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
    public function getSize(): int
    {
        if (!$this->file) {
            return 0;
        }

        return $this->file->getSize();
    }

    /**
     * Check size
     *
     * @return bool
     */
    public function checkSize(): bool
    {
        return $this->getSize() <= $this->getSizeAllowed() * 1000;
    }

    /**
     * Get current upload type
     *
     * @return bool
     */
    public function checkType(): bool
    {
        return in_array($this->getType(), $this->getTypes());
    }

    /**
     * Make storage path
     *
     * @return String
     */
    public function makeStoragePath(): ?string
    {
        return $this->trimPath($this->getStorageFolder() . '/' . $this->getType());
    }

    /**
     * Get path to image file
     *
     * @return string
     */
    public function getPath(): ?string
    {
        return $this->trimPath($this->getStorageName());
    }

    /**
     * Get path to storage that can access from browser
     *
     * @return string
     */
    public function getStorageFolder(): string
    {
        return UPLOAD_FILE_FOLDER;
    }

    /**
     * Get file storage name
     *
     * @return string
     */
    public function getStorageName(): ?string
    {
        return $this->storageName;
    }

    /**
     * Trim path
     *
     * @param mixed $path
     * @return String
     */
    public function trimPath(string $path = null): ?string
    {
        if (!is_string($path)) {
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
     * Get current extension file
     *
     * @return string|null
     */
    public function getExtension(): ?string
    {
        if (!$this->getFile()) {
            return null;
        }

        return $this->getFile()->extension();
    }

    /**
     * Get client extension
     *
     * @return string|null
     */
    public function getClientExtension(): ?string
    {
        if (!$this->getFile()) {
            return null;
        }

        return $this->getFile()->clientExtension();
    }

    /**
     * Make file storage name
     *
     * @return String|null
     */
    private function makeStorageName(): ?string
    {
        if (!$this->getFile()) {
            return null;
        }

        return time() . '_' . generateRandomNumber(100000, 999999, 3) . '.' . $this->getClientExtension();
    }

    /**
     * Check if file is exist
     *
     * @param string|null $path
     * @return bool
     */
    public function exists(string $path = null): bool
    {
        return Storage::disk($this->getDisk())->exists($path);
    }

    /**
     * Remove file
     *
     * @param string|null $path
     * @return bool
     */
    public function remove(string $path = null): bool
    {
        return Storage::disk($this->getDisk())->delete($path ?? $this->getPath());
    }

    /**
     * Handle upload
     *
     * @throws CustomException
     */
    public function upload(): bool
    {
        $this->validate();

        $this->storageName = Storage::disk($this->getDisk())->putFileAs(
            $this->makeStoragePath(),
            $this->getFile(),
            $this->makeStorageName()
        );

        return $this->exists($this->storageName);
    }
}
