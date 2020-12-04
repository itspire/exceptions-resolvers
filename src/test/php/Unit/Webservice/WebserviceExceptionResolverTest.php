<?php

/*
 * Copyright (c) 2016 - 2020 Itspire.
 * This software is licensed under the BSD-3-Clause license. (see LICENSE.md for full license)
 * All Right Reserved.
 */

declare(strict_types=1);

namespace Itspire\Exception\Resolver\Test\Unit\Webservice;

use Itspire\Exception\Adapter\Webservice\WebserviceExceptionApiAdapterInterface;
use Itspire\Exception\Http\Definition\HttpExceptionDefinition;
use Itspire\Exception\Http\HttpException;
use Itspire\Exception\Mapper\ExceptionMapperInterface;
use Itspire\Exception\Resolver\ExceptionResolverInterface;
use Itspire\Exception\Resolver\Webservice\WebserviceExceptionResolver;
use Itspire\Exception\Serializer\Model\Api\Webservice\ApiWebserviceException;
use Itspire\Exception\Webservice\Definition\WebserviceExceptionDefinition;
use Itspire\Exception\Webservice\WebserviceException;
use Itspire\Http\Common\Enum\HttpResponseStatus;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;

class WebserviceExceptionResolverTest extends TestCase
{
    private ?ExceptionMapperInterface $exceptionMapper = null;
    private ?WebserviceExceptionApiAdapterInterface $webserviceExceptionApiAdapter = null;
    private ?SerializerInterface $serializer = null;
    private ?ExceptionResolverInterface $exceptionResolver = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->exceptionMapper = $this->getMockBuilder(ExceptionMapperInterface::class)->getMock();
        $this->webserviceExceptionApiAdapter = $this
            ->getMockBuilder(WebserviceExceptionApiAdapterInterface::class)
            ->getMock();
        $this->serializer = $this->getMockBuilder(SerializerInterface::class)->getMock();

        $this->exceptionResolver = new WebserviceExceptionResolver(
            $this->exceptionMapper,
            $this->webserviceExceptionApiAdapter,
            $this->serializer
        );
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
                new HttpException(
                    new HttpExceptionDefinition(HttpExceptionDefinition::HTTP_NON_PROCESSABLE_ENTITY)
                )
            )
        );
    }

    /** @test */
    public function supportsTest(): void
    {
        static::assertTrue(
            $this->exceptionResolver->supports(
                new WebserviceException(
                    new WebserviceExceptionDefinition(WebserviceExceptionDefinition::TRANSFORMATION_ERROR)
                )
            )
        );
    }

    /** @test */
    public function resolveUnsupportedTest(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Resolver %s does not support %s class',
                WebserviceExceptionResolver::class,
                HttpException::class
            )
        );

        $this->exceptionResolver->resolve(
            new HttpException(
                new HttpExceptionDefinition(HttpExceptionDefinition::HTTP_NON_PROCESSABLE_ENTITY)
            )
        );
    }


    /** @test */
    public function resolveTest(): void
    {
        $webserviceException = new WebserviceException(
            new WebserviceExceptionDefinition(WebserviceExceptionDefinition::TRANSFORMATION_ERROR)
        );

        $apiWebserviceException = (new ApiWebserviceException())
            ->setCode('TRANSFORMATION_ERROR')
            ->setMessage('itspire.exceptions.adapter_transformation_error')
            ->addDetail('Detail1');

        $httpResponseStatus = new HttpResponseStatus(HttpResponseStatus::HTTP_NON_PROCESSABLE_ENTITY);

        $this->exceptionMapper
            ->expects(static::once())
            ->method('map')
            ->with($webserviceException)
            ->willReturn($httpResponseStatus);

        $this->webserviceExceptionApiAdapter
            ->expects(static::once())
            ->method('adaptBusinessToApi')
            ->with($webserviceException)
            ->willReturn($apiWebserviceException);

        $this->serializer
            ->expects(static::once())
            ->method('serialize')
            ->with($apiWebserviceException, 'xml')
            ->willReturn(
                file_get_contents(
                    realpath('vendor/itspire/exceptions-serializer/src/test/resources/test_webservice_exception.xml')
                )
            );

        $httpResponse = $this->exceptionResolver->resolve($webserviceException, 'xml');

        static::assertEquals(422, $httpResponse->getStatusCode());
        static::assertEquals('Unprocessable Entity', $httpResponse->getReasonPhrase());

        static::assertStringContainsString(
            '<webservice_exception code="TEST" message="test">',
            (string) $httpResponse->getBody()
        );
    }
}
