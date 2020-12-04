<?php

/*
 * Copyright (c) 2016 - 2020 Itspire.
 * This software is licensed under the BSD-3-Clause license. (see LICENSE.md for full license)
 * All Right Reserved.
 */

declare(strict_types=1);

namespace Itspire\Exception\Resolver;

use Itspire\Exception\ExceptionInterface;
use Psr\Http\Message\ResponseInterface;

interface ExceptionResolverInterface
{
    public function resolve(ExceptionInterface $exception, ?string $serializationFormat = null): ResponseInterface;

    public function supports(ExceptionInterface $exception): bool;
}
