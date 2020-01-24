<?php


namespace IIIRxs\ImageUploadBundle\Exception;


use IIIRxs\ExceptionHandlerBundle\Exception\ExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class InvalidUploadTargetDirException extends \Exception implements ExceptionInterface
{
    const DEFAULT_ERROR = 'Target dir parameter should either be a string or an array with "optimized" and "thumbnails" keys set';

    private $error;

    public function __construct(string $error = null)
    {
        $this->error = $error ?: self::DEFAULT_ERROR;
        parent::__construct('', 0, null);
    }

    public function getStatusCode()
    {
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    public function getPayload()
    {
        return ['errors' => $this->error];
    }

}