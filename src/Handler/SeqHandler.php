<?php

namespace Msschl\Monolog\Handler;

use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;
use Msschl\Monolog\Formatter\SeqBaseFormatter;
use Msschl\Monolog\Formatter\SeqCompactJsonFormatter;
use Msschl\Monolog\Handler\HttpHandler;

/**
 * This file is part of the msschl\monolog-seq-handler package.
 *
 * Copyright (c) 2018 Markus Schlotbohm
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */
class SeqHandler extends HttpHandler
{

	/**
	 * The uri to the logging endpoint of a seq-server.
	 *
	 * @var string
	 */
	const SEQ_API_URI = 'api/events/raw';

	/**
	 * The http method to the logging endpoint of a seq-server.
	 *
	 * @var string
	 */
	const SEQ_API_METHOD = 'POST';

	/**
     * The options array.
     *
     * @var array
     */
    protected $options = [
    	'uri'             => null,
    	'method'          => SeqHandler::SEQ_API_METHOD,
    	'headers'         => [
    		'Content-Type' => 'application/vnd.serilog.clef'
    	],
    	'protocolVersion' => '1.1'
    ];

	/**
	 * Initializes a new instance of the {@see SeqHandler} class.
	 *
	 * @param  string               $serverUri Uri to a seq server instance.
	 * @param  string|null          $apiKey    A Seq API key to authenticate or tag messages from the logger.
	 * @param  int                  $level     The minimum logging level at which this handler will be triggered.
	 * @param  boolean              $bubble    Whether the messages that are handled can bubble up the stack or not.
	 * @param  HttpClient|null      $client    An instance of a psr-7 http client implementation or null when the
     *                                         HttpClientDiscovery should be used to find an instance.
	 * @param  MessageFactory|null  $factory   An instance of a psr-7 message factory implementation or null when
     *                                         the MessageFactoryDiscovery should be used to find an instance.
	 */
	public function __construct(
		string $serverUri,
		string $apiKey = null,
		$level = Logger::DEBUG,
		$bubble = true,
		HttpClient $client = null,
		MessageFactory $factory = null
	) {
		$this->setServerUri($serverUri);
		$this->setApiKey($apiKey);

		parent::__construct(
			$this->options,
			$client,
			$factory,
			$level,
			$bubble
		);
	}

	/**
	 * Gets the seq server uri.
	 *
	 * @return string|null
	 */
	public function getServerUri()
	{
		$uri = $this->getUri();

		if (!empty($uri)) {
			return str_replace(SeqHandler::SEQ_API_URI, '', $uri);
		}

		return null;
	}

	/**
	 * Sets the seq server uri.
	 *
	 * @param  string|null $uri Uri to the seq server instance e.g. 'http://seq-server' or null to disable the
	 *                          {@see SeqHandler}.
	 * @return self
	 */
	public function setServerUri(string $uri = null)
	{
		if (!empty($uri)) {
			if (!SeqHandler::endsWith($uri, '/')) {
				$uri = $uri  . '/';
			}

			$uri = $uri . SeqHandler::SEQ_API_URI;
		}

		$this->setUri($uri);

		return $this;
	}

	/**
	 * Gets the Seq API key.
	 *
	 * @return string|null
	 */
	public function getApiKey()
	{
		return $this->getHeader('X-Seq-ApiKey');
	}

	/**
	 * Sets the Seq API key to authenticate or tag messages from the logger.
	 *
	 * @param  string|null $apiKey The Seq API key or null.
	 * @return self
	 */
	public function setApiKey(string $apiKey = null)
	{
		$this->popHeader($apiKey);

		if (!empty($apiKey)) {
			$this->pushHeader('X-Seq-ApiKey', $apiKey);
		}

		return $this;
	}

	/**
     * Sets the formatter.
     *
     * @param  FormatterInterface $formatter The formatter of type SeqBaseFormatter.
     * @return self
     */
    public function setFormatter(FormatterInterface $formatter)
    {
    	if (!($formatter instanceof SeqBaseFormatter)) {
    		throw new \InvalidArgumentException('SeqBaseFormatter expected, got ' . gettype($formatter) . ' / ' . get_class($formatter));
    	}

        $this->formatter = $formatter;

        $this->pushHeader('Content-Type', $formatter->getContentType());

        return $this;
    }

	/**
     * Gets the default formatter.
     *
     * @return \Msschl\Monolog\Formatter\SeqCompactJsonFormatter
     */
    protected function getDefaultFormatter() : FormatterInterface
    {
        $formatter = new SeqCompactJsonFormatter();

        $this->pushHeader('Content-Type', $formatter->getContentType());

        return $formatter;
    }

    /**
     * Checks whether a string ends with a specific string or not.
     *
     * @param  string      $haystack The string to check.
     * @param  string|null $needle   The searched value.
     * @return bool
     */
    private static function endsWith(string $haystack, string $needle = null)
	{
    	$length = strlen($needle);

    	return $length === 0 || (substr($haystack, -$length) === $needle);
	}
}