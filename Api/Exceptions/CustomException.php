<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;
use Throwable;

class CustomException extends Exception
{
    /**
     * @var string
     */
    protected $target = "";

    /**
     * @var bool
     */
    protected $showMessage = true;

    /**
     * CustomException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = Response::HTTP_INTERNAL_SERVER_ERROR, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Set target
     *
     * @param string $target
     */
    public function setTarget(string $target)
    {
        $this->target = $target;
    }

    /**
     * Get the target
     *
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * Set show message status
     *
     * @param bool $showMessage
     */
    public function setShowMessage(bool $showMessage = true)
    {
        $this->showMessage = $showMessage;
    }

    /**
     * Get show message status
     *
     * @return bool
     */
    public function getShowMessage(): bool
    {
        return $this->showMessage;
    }
}
