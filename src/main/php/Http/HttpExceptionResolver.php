<?php

/*
 * Copyright (c) 2016 - 2020 Itspire.
 * This software is licensed under the BSD-3-Clause license. (see LICENSE.md for full license)
 * All Right Reserved.
 */

declare(strict_types=1);

namespace Itspire\Exception\Resolver\Http;

use Itspire\Exception\ExceptionInterface;
use Itspire\Exception\Http\HttpExceptionInterface;
use Itspire\Exception\Resolver\AbstractExceptionResolver;

class HttpExceptionResolver extends AbstractExceptionResolver
{
    public function supports(ExceptionInterface $exception): bool
    {
        return $exception instanceof HttpExceptionInterface;
    }
}
