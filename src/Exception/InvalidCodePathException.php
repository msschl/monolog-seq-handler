<?php

namespace Msschl\Monolog\Exception;

use Exception;

/**
 * This file is part of the msschl\monolog-seq-handler package.
 *
 * Copyright (c) 2018 Markus Schlotbohm
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */
class InvalidCodePathException extends Exception
{
	public function __construct()
	{
		parent::__construct('Invalid code path!');
	}
}