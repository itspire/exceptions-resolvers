<?php

/*
 * Copyright (c) 2016 - 2020 Itspire.
 * This software is licensed under the BSD-3-Clause license. (see LICENSE.md for full license)
 * All Right Reserved.
 */

declare(strict_types=1);

namespace Itspire\Exception\Resolver;

use Itspire\Exception\ExceptionInterface;
use Itspire\Exception\Mapper\ExceptionMapperInterface;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractExceptionResolver implements ExceptionResolverInterface
{
    private ?ExceptionMapperInterface $exceptionMapper = null;

    public function __construct(ExceptionMapperInterface $exceptionMapper)
    {
        $this->exceptionMapper = $exceptionMapper;
    }

    public function resolve(ExceptionInterface $exception, ?string $serializationFormat = null): ResponseInterface
    {
        if (false === $this->supports($exception)) {
            throw new \InvalidArgumentException(
                sprintf('Resolver %s does not support %s class', static::class, get_class($exception))
            );
        }

        $httpResponseStatus = $this->exceptionMapper->map($exception);

        return (new Response())->withStatus($httpResponseStatus->getValue(), $httpResponseStatus->getDescription());
    }
}
