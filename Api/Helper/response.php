<?php

use Illuminate\Support\Facades\Log;

if (!function_exists('dataResponse')) {
    /**
     * format data response
     *
     * @param mixed $message
     * @param mixed $data
     * @param int $code
     * @return mixed
     */
    function dataResponse($message, $data, int $code)
    {
        $dataResponse['message'] = $message;
        $dataResponse['data'] = $data;
        $dataResponse['code'] = $code;

        return $dataResponse;
    }
}

if (!function_exists('trimSpaceFullWidth')) {
    /**
     * Trim full width space
     *
     * @param string|null $str
     * @return string|string[]|null
     */
    function trimSpaceFullWidth($str = null)
    {
        if (empty($str)) {
            return null;
        }

        $str = preg_replace("/^[\s　]+/u", '', $str);
        $str = preg_replace("/[\s　]+$/u", '', $str);
        return $str;
    }
}
