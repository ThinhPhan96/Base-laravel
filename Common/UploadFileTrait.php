<?php


namespace App\Traits;


use App\Exceptions\CustomException;
use App\Service\ImageUploadService;
use App\Service\UploadFile;
use Illuminate\Support\Facades\Storage;

trait UploadFileTrait
{
    /**
     * Handle upload single file
     *
     * @param mixed $file
     * @param $type
     * @return UploadFile|false
     * @throws CustomException
     */
    public function uploadFile($file, $type)
    {
        $upload = new UploadFile();
        $upload->setFile($file)->setType($type);

        if (!$upload->upload()) {
            return false;
        }

        return $upload;
    }

    /**
     * Handle upload single image
     *
     * @param $file
     * @param $type
     * @param null $options
     * @return ImageUploadService|false
     * @throws \Exception
     */
    public function uploadFileImage($file, $type, $options = null)
    {
        $upload = new ImageUploadService($file, $type, $options);

        if (!$upload->upload()) {
            return false;
        }

        return $upload;
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
}
