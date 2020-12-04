<?php

/*
 * Copyright (c) 2016 - 2020 Itspire.
 * This software is licensed under the BSD-3-Clause license. (see LICENSE.md for full license)
 * All Right Reserved.
 */

declare(strict_types=1);

namespace Itspire\Exception\Resolver\Test\Unit\Http;

use Itspire\Exception\Http\Definition\HttpExceptionDefinition;
use Itspire\Exception\Http\HttpException;
use Itspire\Exception\Mapper\ExceptionMapperInterface;
use Itspire\Exception\Resolver\ExceptionResolverInterface;
use Itspire\Exception\Resolver\Http\HttpExceptionResolver;
use Itspire\Exception\Webservice\Definition\WebserviceExceptionDefinition;
use Itspire\Exception\Webservice\WebserviceException;
use Itspire\Http\Common\Enum\HttpResponseStatus;
use PHPUnit\Framework\TestCase;

class HttpExceptionResolverTest extends TestCase
{
    private ?ExceptionMapperInterface $exceptionMapper = null;
    private ?ExceptionResolverInterface $exceptionResolver = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->exceptionMapper = $this->getMockBuilder(ExceptionMapperInterface::class)->getMock();

        $this->exceptionResolver = new HttpExceptionResolver($this->exceptionMapper);
    }

    protected function tearDown(): void
    {
        unset($this->exceptionResolver, $this->exceptionMapper);

        parent::tearDown();
    }

    /** @test */
    public function supportsFalseTest(): void
    {
        static::assertFalse(
            $this->exceptionResolver->supports(
                new WebserviceException(
                    new WebserviceExceptionDefinition(WebserviceExceptionDefinition::TRANSFORMATION_ERROR)
                )
            )
        );
    }

    /** @test */
    public function supportsTest(): void
    {
        static::assertTrue(
            $this->exceptionResolver->supports(
                new HttpException(new HttpExceptionDefinition(HttpExceptionDefinition::HTTP_BAD_REQUEST))
            )
        );
    }

    /** @test */
    public function resolveUnsupportedTest(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf('Resolver %s does not support %s class', HttpExceptionResolver::class, WebserviceException::class)
        );

        $this->exceptionResolver->resolve(
            new WebserviceException(
                new WebserviceExceptionDefinition(WebserviceExceptionDefinition::TRANSFORMATION_ERROR)
            )
        );
    }


    /** @test */
    public function resolveTest(): void
    {
        $httpException = new HttpException(new HttpExceptionDefinition(HttpExceptionDefinition::HTTP_BAD_REQUEST));
        $httpResponseStatus = new HttpResponseStatus(HttpResponseStatus::HTTP_BAD_REQUEST);

        $this->exceptionMapper
            ->expects(static::once())
            ->method('map')
            ->with($httpException)
            ->willReturn($httpResponseStatus);

        $httpResponse = $this->exceptionResolver->resolve($httpException);

        static::assertEquals(400, $httpResponse->getStatusCode());
        static::assertEquals('Bad Request', $httpResponse->getReasonPhrase());
    }
}
