<?php

namespace Msschl\Monolog\Tests\Handler;

use Http\Mock\Client;
use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;
use Msschl\Monolog\Formatter\SeqBaseFormatter;
use Msschl\Monolog\Formatter\SeqCompactJsonFormatter;
use Msschl\Monolog\Handler\SeqHandler;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * This file is part of the msschl\monolog-seq-handler package.
 *
 * Copyright (c) 2018 Markus Schlotbohm
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */
class SeqHandlerTest extends TestCase
{

	/**
	 * The http mock client.
	 *
	 * @var \Http\Mock\Client
	 */
	protected $client;

	/**
	 * The seq handler instance.
	 *
	 * @var \Msschl\Monolog\Handler\SeqHandler
	 */
	protected $handler;

	/**
	 * This method is run once for each test method and creates an instance of the SeqHandler and MockClient.
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$this->client = new Client();
		$this->handler = new SeqHandler('http://seq-server/', null, Logger::DEBUG, true, $this->client);
	}

	public function testDefaultValuesAfterInstanceIsCreated()
	{
		$this->assertNotNull($this->handler->getServerUri());
		$this->assertSame('http://seq-server/', $this->handler->getServerUri());
		$this->assertSame('http://seq-server/' . SeqHandler::SEQ_API_URI, $this->handler->getUri());
		$this->assertSame(SeqHandler::SEQ_API_METHOD, $this->handler->getMethod());
		$this->assertSame(['Content-Type' => 'application/vnd.serilog.clef'], $this->handler->getHeaders());
		$this->assertSame('1.1', $this->handler->getProtocolVersion());
	}

	public function testSetServerUriAndReturnsSelfInstance()
	{
		$expectedValue = 'http://seq/';

		$returnValue = $this->handler->setServerUri($expectedValue);

		$this->assertInstanceOf(SeqHandler::class, $returnValue);
		$this->assertSame($this->handler, $returnValue);

		$this->assertTrue(is_string($this->handler->getUri()));
	 	$this->assertSame($expectedValue . SeqHandler::SEQ_API_URI, $this->handler->getUri());
	 	$this->assertTrue(is_string($this->handler->getServerUri()));
	 	$this->assertSame($expectedValue, $this->handler->getServerUri());
	}

	public function testSetServerUriAppendsSlashAndReturnsSelfInstance()
	{
		$expectedValue = 'http://seq';

		$returnValue = $this->handler->setServerUri($expectedValue);

		$this->assertInstanceOf(SeqHandler::class, $returnValue);
		$this->assertSame($this->handler, $returnValue);

		$this->assertTrue(is_string($this->handler->getUri()));
	 	$this->assertSame($expectedValue . '/' . SeqHandler::SEQ_API_URI, $this->handler->getUri());
	 	$this->assertTrue(is_string($this->handler->getServerUri()));
	 	$this->assertSame($expectedValue . '/', $this->handler->getServerUri());
	}

	public function testSetServerUriNullValueAndReturnsSelfInstance()
	{
		$expectedValue = null;

		$returnValue = $this->handler->setServerUri($expectedValue);

		$this->assertInstanceOf(SeqHandler::class, $returnValue);
		$this->assertSame($this->handler, $returnValue);

		$this->assertNull($this->handler->getUri());
	 	$this->assertNull($this->handler->getServerUri());
	}

	public function testSetApiKeyAndReturnsSelfInstance()
	{
		$expectedValue = 'api-key';

		$returnValue = $this->handler->setApiKey($expectedValue);

		$this->assertInstanceOf(SeqHandler::class, $returnValue);
		$this->assertSame($this->handler, $returnValue);

		$this->assertTrue(is_string($this->handler->getApiKey()));
	 	$this->assertSame($expectedValue, $this->handler->getApiKey());
	}

	public function testSetApiKeyNullValueAndReturnsSelfInstance()
	{
		$expectedValue = null;

		$returnValue = $this->handler->setApiKey($expectedValue);

		$this->assertInstanceOf(SeqHandler::class, $returnValue);
		$this->assertSame($this->handler, $returnValue);

		$this->assertNull($this->handler->getApiKey());
	}

	public function testSetFormatterAndReturnsSelfInstance()
	{
		$expectedValue = new SeqCompactJsonFormatter();

		$returnValue = $this->handler->setFormatter($expectedValue);

		$this->assertInstanceOf(SeqHandler::class, $returnValue);
		$this->assertSame($this->handler, $returnValue);

		$this->assertNotNull($this->handler->getFormatter());
		$this->assertSame($expectedValue, $this->handler->getFormatter());
		$this->assertSame($expectedValue->getContentType(), $this->handler->getHeader('Content-Type'));
	}

	public function testSetFormatterThrowsInvalidArgumentException()
	{
		$stub = $this->createMock(FormatterInterface::class);

		try {
    		$this->handler->setFormatter($stub);
    		$this->assertTrue(false);
    	} catch (\InvalidArgumentException $e) {
    		$this->assertTrue(true);
    	}
	}

	public function testGetDefaultFormatter()
	{
		$log = new Logger('logger');

		$log->pushHandler($this->handler);

		$log->error('Bar');

		$this->assertInstanceOf(RequestInterface::class, $this->client->getLastRequest());
		$this->assertNotNull($this->client->getLastRequest());

		$this->assertNotNull($this->handler->getHeader('Content-Type'));
		$this->assertTrue(is_string($this->handler->getHeader('Content-Type')));
	}

	public function testLogging()
	{
		$log = new Logger('logger');

		$log->pushHandler($this->handler);

		$log->error('Bar', ['exception' => new \Exception('test'), 'snake_case' => 'yes']);

		$this->assertInstanceOf(RequestInterface::class, $this->client->getLastRequest());
		$this->assertNotNull($this->client->getLastRequest());

		$this->assertNotNull($this->handler->getHeader('Content-Type'));
		$this->assertTrue(is_string($this->handler->getHeader('Content-Type')));
	}

	/**
	 * This method is run once after each test method and frees the SeqHandler and MockClient instaces.
	 *
	 * @return void
	 */
	protected function tearDown()
	{
		$this->handler = null;
		$this->client = null;
	}
}