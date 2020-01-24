<?php


namespace IIIRxs\ImageUploadBundle\Exception;


use IIIRxs\ExceptionHandlerBundle\Exception\ExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

class InvalidImageCollectionException extends \Exception implements ExceptionInterface
{

    public function getStatusCode()
    {
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    public function getPayload()
    {
        return ['errors' => 'Invalid image collection'];
    }
}