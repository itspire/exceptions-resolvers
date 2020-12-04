<?php

/*
 * Copyright (c) 2016 - 2020 Itspire.
 * This software is licensed under the BSD-3-Clause license. (see LICENSE.md for full license)
 * All Right Reserved.
 */

declare(strict_types=1);

namespace Itspire\Exception\Resolver\Test\Functional\Http;

use Itspire\Exception\Http\Definition\HttpExceptionDefinition;
use Itspire\Exception\Http\HttpException;
use Itspire\Exception\Mapper\Http\HttpExceptionMapper;
use Itspire\Exception\Resolver\ExceptionResolverInterface;
use Itspire\Exception\Resolver\Http\HttpExceptionResolver;
use PHPUnit\Framework\TestCase;

class HttpExceptionResolverTest extends TestCase
{
    private ?ExceptionResolverInterface $exceptionResolver = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->exceptionResolver = new HttpExceptionResolver(new HttpExceptionMapper());
    }

    protected function tearDown(): void
    {
        unset($this->exceptionResolver);

        parent::tearDown();
    }

    /** @test */
    public function resolveTest(): void
    {
        $httpResponse = $this->exceptionResolver->resolve(
            new HttpException(new HttpExceptionDefinition(HttpExceptionDefinition::HTTP_BAD_REQUEST))
        );

        static::assertEquals(400, $httpResponse->getStatusCode());
        static::assertEquals('Bad Request', $httpResponse->getReasonPhrase());
    }
}
