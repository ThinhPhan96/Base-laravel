<?php


namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends Controller
{
    /**
     * response error
     *
     * @param array $errors
     * @param int|null $statusCode
     * @param bool $notShowMessage
     * @return JsonResponse
     */
    public function responseError($errors = [], int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR, $notShowMessage = false)
    {
        $dataResponse = dataResponse($errors, null, $statusCode);

        if ($notShowMessage) {
            $dataResponse['not_show_message'] = $notShowMessage;
        }

        return response()->json($dataResponse, $statusCode);
    }

    /**
     * response success
     *
     * @param null $message
     * @param null $data
     * @param int $statusCode
     * @param bool $notShowMessage
     * @return JsonResponse
     */
    public function responseSuccess($message = null, $data = null, $statusCode = Response::HTTP_OK, $notShowMessage = false)
    {
        $message = !is_null($message) ? $message : Response::$statusTexts[$statusCode];
        $dataResponse = dataResponse($message, $data, $statusCode);

        if ($notShowMessage) {
            $dataResponse['not_show_message'] = $notShowMessage;
        }

        return response()->json($dataResponse, $statusCode);
    }

    /**
     * response 404
     * @param string|null $message
     * @param bool $notShowMessage
     * @return JsonResponse
     */
    public function response404(string $message = null, $notShowMessage = false)
    {
        $data = dataResponse($message ?? __('messages.common.lb_not_found'), null, Response::HTTP_NOT_FOUND);

        if ($notShowMessage) {
            $data['not_show_message'] = $notShowMessage;
        }

        return response()->json($data, Response::HTTP_NOT_FOUND);
    }
}
