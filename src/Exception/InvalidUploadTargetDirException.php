<?php


namespace IIIRxs\ImageUploadBundle\Exception;


use IIIRxs\ExceptionHandlerBundle\Exception\ExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

class InvalidUploadTargetDirException extends \Exception implements ExceptionInterface
{

    public function getStatusCode()
    {
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    public function getPayload()
    {
        return ['errors' => 'Target dir parameter should either be a string or an array with "optimized" and "thumbnails" keys set'];
    }

}