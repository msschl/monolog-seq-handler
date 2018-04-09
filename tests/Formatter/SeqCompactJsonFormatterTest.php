<?php

namespace Msschl\Monolog\Tests\Formatter;

use Msschl\Monolog\Formatter\SeqCompactJsonFormatter;
use PHPUnit\Framework\TestCase;


/**
 * This file is part of the msschl\monolog-seq-handler package.
 *
 * Copyright (c) 2018 Markus Schlotbohm
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */
class SeqCompactJsonFormatterTest extends TestCase
{

	/**
	 * The seq compact json formatter instance.
	 *
	 * @var \Msschl\Monolog\Formatter\SeqCompactJsonFormatter
	 */
	protected $formatter;

	/**
	 * This method is run once for each test method and creates an instance of the SeqCompactJsonFormatter.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->formatter = new SeqCompactJsonFormatter();
	}

	public function testDefaultValuesAfterInstanceIsCreated()
	{
		$this->assertSame('application/vnd.serilog.clef', $this->formatter->getContentType());
		$this->assertTrue($this->formatter->getExtractContent());
		$this->assertTrue($this->formatter->getExtractExtras());
	}

	public function testSetExtractContentAndReturnsSelfInstance()
	{
		$expectedValue = false;

		$returnValue = $this->formatter->setExtractContent($expectedValue);

		$this->assertInstanceOf(SeqCompactJsonFormatter::class, $returnValue);
		$this->assertSame($this->formatter, $returnValue);

		$this->assertTrue(is_bool($this->formatter->getExtractContent()));
	 	$this->assertSame($expectedValue, $this->formatter->getExtractContent());
	}

	public function testSetExtractExtrasAndReturnsSelfInstance()
	{
		$expectedValue = false;

		$returnValue = $this->formatter->setExtractExtras($expectedValue);

		$this->assertInstanceOf(SeqCompactJsonFormatter::class, $returnValue);
		$this->assertSame($this->formatter, $returnValue);

		$this->assertTrue(is_bool($this->formatter->getExtractExtras()));
	 	$this->assertSame($expectedValue, $this->formatter->getExtractExtras());
	}

	/**
	 * This method is run once after each test method and frees the SeqCompactJsonFormatter instace.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
		$this->formatter = null;
	}
}