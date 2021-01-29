<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param \Exception $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof CustomException) {
            return response()->json([
                RESPONSE_MESSAGE => $exception->getMessage(),
                RESPONSE_TARGET => $exception->getTarget(),
                RESPONSE_NOT_SHOW_MESSAGE => !$exception->getShowMessage(),
                RESPONSE_CODE => $exception->getCode()
            ], $exception->getCode());
        }

        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                RESPONSE_MESSAGE => __('messages.common.lb_not_found'),
                RESPONSE_ERROR => $exception->getMessage(),
                RESPONSE_CODE => Response::HTTP_NOT_FOUND
            ], Response::HTTP_NOT_FOUND);
        }

        if ($exception instanceof ValidationException) {
            return response()->json([
                RESPONSE_MESSAGE => $exception->errors(),
                RESPONSE_DATA => null,
                RESPONSE_CODE => Response::HTTP_UNPROCESSABLE_ENTITY
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (
            $exception instanceof \ErrorException ||
            $exception instanceof QueryException ||
            $exception instanceof DecryptException
        ) {
            return response()->json([
                RESPONSE_MESSAGE => __('messages.common.error_occurred'),
                RESPONSE_ERROR => $exception->getMessage(),
                RESPONSE_CODE => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return parent::render($request, $exception);
    }
}
