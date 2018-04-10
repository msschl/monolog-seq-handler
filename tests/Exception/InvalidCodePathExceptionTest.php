<?php

namespace Msschl\Monolog\Tests\Exception;

use Msschl\Monolog\Exception\InvalidCodePathException;
use PHPUnit\Framework\TestCase;


/**
 * This file is part of the msschl\monolog-seq-handler package.
 *
 * Copyright (c) 2018 Markus Schlotbohm
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */
class InvalidCodePathExceptionTest extends TestCase
{
	public function testDefaultValuesAfterInstanceIsCreated()
	{
		$exception = new InvalidCodePathException();

		$this->assertSame('Invalid code path!', $exception->getMessage());
	}
}