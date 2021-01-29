<?php

use Illuminate\Support\Facades\File;
/**
 *
 */
if (!function_exists('trimPath')) {
    /**
     * @param $path
     * @param string $explodeBy
     * @param string $implodeSeparator
     * @param string $protocol
     * @return string
     */
    function trimPath($path, $explodeBy = '/', $implodeSeparator = '/', $protocol = 'http')
    {
        $arr = explode($explodeBy, $path);
        $rsArr = [];

        foreach ($arr as $item) {
            if (!empty(trim($item))) {
                $rsArr[] = trim($item);
            }
        }

        return $protocol . '://' . implode($implodeSeparator, $rsArr);
    }
}

/**
 * Define a function get full storage url
 */
if (!function_exists('getFullStorageUrl')) {
    /**
     * @param mixed $path
     * @param null $default
     * @return string
     */
    function getFullStorageUrl($path, $default = null)
    {
        if(empty($path) && empty($default)) {
            return null;
        }

        if(!empty($path) && File::exists(public_path($path))) {
            return trimPath(
                config('app.storage_domain') . '/' . $path,
                '/',
                '/',
                config('app.storage_protocol')
            );
        }

        return trimPath(
            config('app.storage_domain') . '/' . $default,
            '/',
            '/',
            config('app.storage_protocol')
        );
    }
}
