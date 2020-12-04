<?php

/*
 * Copyright (c) 2016 - 2020 Itspire.
 * This software is licensed under the BSD-3-Clause license. (see LICENSE.md for full license)
 * All Right Reserved.
 */

declare(strict_types=1);

namespace Itspire\Exception\Resolver\Webservice;

use Itspire\Exception\Adapter\Webservice\WebserviceExceptionApiAdapterInterface;
use Itspire\Exception\ExceptionInterface;
use Itspire\Exception\Mapper\ExceptionMapperInterface;
use Itspire\Exception\Resolver\AbstractExceptionResolver;
use Itspire\Exception\Webservice\WebserviceExceptionInterface;
use JMS\Serializer\SerializerInterface;
use Nyholm\Psr7\Stream;
use Psr\Http\Message\ResponseInterface;

class WebserviceExceptionResolver extends AbstractExceptionResolver
{
    private ?WebserviceExceptionApiAdapterInterface $webserviceExceptionAdapter = null;
    private ?SerializerInterface $serializer = null;

    public function __construct(
        ExceptionMapperInterface $exceptionMapper,
        WebserviceExceptionApiAdapterInterface $webserviceExceptionAdapter,
        SerializerInterface $serializer
    ) {
        parent::__construct($exceptionMapper);
        $this->webserviceExceptionAdapter = $webserviceExceptionAdapter;
        $this->serializer = $serializer;
    }

    public function resolve(ExceptionInterface $exception, ?string $serializationFormat = null): ResponseInterface
    {
        $response = parent::resolve($exception, $serializationFormat);

        /** @var WebserviceExceptionInterface $exception */
        $apiException = $this->webserviceExceptionAdapter->adaptBusinessToApi($exception);

        if (null !== $apiException) {
//            $stream = fopen('php://temp', 'rb+');
//            fwrite($stream, $this->serializer->serialize($apiException, $serializationFormat));
//            fseek($stream, 0);

            $response = $response->withBody(
                Stream::create($this->serializer->serialize($apiException, $serializationFormat))
                //new Stream($stream)
            );
        }

        return $response;
    }

    public function supports(ExceptionInterface $exception): bool
    {
        return $exception instanceof WebserviceExceptionInterface;
    }
}
