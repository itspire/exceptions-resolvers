<?php

/*
 * Copyright (c) 2016 - 2020 Itspire.
 * This software is licensed under the BSD-3-Clause license. (see LICENSE.md for full license)
 * All Right Reserved.
 */

declare(strict_types=1);

namespace Itspire\Exception\Resolver\Test\Functional\Webservice;

use Itspire\Exception\Resolver\ExceptionResolverInterface;
use Itspire\Exception\Adapter\Webservice\WebserviceExceptionApiAdapter;
use Itspire\Exception\Mapper\Webservice\WebserviceExceptionMapper;
use Itspire\Exception\Resolver\Webservice\WebserviceExceptionResolver;
use Itspire\Exception\Webservice\Definition\WebserviceExceptionDefinition;
use Itspire\Exception\Webservice\WebserviceException;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

class WebserviceExceptionResolverTest extends TestCase
{
    private static ?TranslatorInterface $translator = null;
    private static ?SerializerInterface $serializer = null;
    private ?ExceptionResolverInterface $exceptionResolver = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        if (null === static::$translator) {
            static::$translator = new Translator('en');
            static::$translator->addLoader('yml', new YamlFileLoader());

            $finder = new Finder();
            $finder->files()->in(realpath('vendor/itspire/exceptions-adapters/src/test/resources/translations'));

            foreach ($finder as $file) {
                $fileNameParts = explode('.', $file->getFilename());
                static::$translator->addResource('yml', $file->getRealPath(), $fileNameParts[1], $fileNameParts[0]);
            }
        }

        if (null === static::$serializer) {
            // obtaining the serializer
            $serializerBuilder = SerializerBuilder::create();
            static::$serializer = $serializerBuilder->build();
        }
    }

    public static function tearDownAfterClass(): void
    {
        static::$serializer = null;
        static::$translator = null;

        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->exceptionResolver = new WebserviceExceptionResolver(
            new WebserviceExceptionMapper(),
            new WebserviceExceptionApiAdapter(static::$translator),
            static::$serializer
        );
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
            new WebserviceException(
                new WebserviceExceptionDefinition(WebserviceExceptionDefinition::TRANSFORMATION_ERROR)
            ),
            'xml'
        );

        static::assertEquals(500, $httpResponse->getStatusCode());
        static::assertEquals('Internal Server Error', $httpResponse->getReasonPhrase());

        static::assertEquals(
            "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<webservice_exception code=\"TRANSFORMATION_ERROR\" "
            . "message=\"A transformation exception occurred\"/>\n",
            (string) $httpResponse->getBody()
        );
    }
}
