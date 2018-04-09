<?php

namespace Msschl\Monolog\Formatter;

use DateTime;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\JsonFormatter;
use Throwable;

/**
 * This file is part of the msschl\monolog-seq-handler package.
 *
 * Copyright (c) 2018 Markus Schlotbohm
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */
class SeqCompactJsonFormatter extends SeqBaseFormatter
{

    /**
     * The extract context flag.
     * Whether to extract the context array to the root or not.
     *
     * @var bool
     */
    protected $extractContext;

    /**
     * The extract extras flag.
     * Whether to extract the extras array to the root or not.
     *
     * @var bool
     */
    protected $extractExtras;

    /**
     * Initializes a new instance of the {@see SeqCompactJsonFormatter} class.
     *
     * @param  bool $extractContext Flag that indicates whether to extract the extras array
     *                              to the root or not.
     * @param  bool $extractExtras  Flag that indicates whether to extract the context array
     *                              to the root or not.
     */
	public function __construct(bool $extractContext = true, bool $extractExtras = true)
	{
        $this->appendNewline = false;
        $this->batchMode = JsonFormatter::BATCH_MODE_NEWLINES;
        $this->extractContext = $extractContext;
        $this->extractExtras = $extractExtras;
	}

    /**
     * Returns a string with the content type for the seq-formatter.
     *
     * @return string
     */
    public function getContentType() : string {
        return 'application/vnd.serilog.clef';
    }

    /**
     * Gets whether the flag extract content is set or not.
     *
     * @return bool
     */
    public function getExtractContent() : bool
    {
        return $this->extractContext;
    }

    /**
     * Sets the flag extract content.
     *
     * @param  bool $value The flag.
     * @return self
     */
    public function setExtractContent(bool $value)
    {
        $this->extractContext = $value;

        return $this;
    }

    /**
     * Gets whether the flag extract extras is set or not.
     *
     * @return bool
     */
    public function getExtractExtras()
    {
        return $this->extractExtras;
    }

    /**
     * Sets the flag extract extras.
     *
     * @param  bool $value The flag.
     * @return self
     */
    public function setExtractExtras(bool $value)
    {
        $this->extractExtras = $value;

        return $this;
    }

    /**
     * Normalizes given $data.
     *
     * @param mixed $data The data to normalize.
     * @return mixed
     */
    protected function normalize($data)
    {
        if (is_array($data) || $data instanceof \Traversable) {
            return $this->normalizeArray($data);
        }

        if ($data instanceof \Throwable) {
            return $this->normalizeException($data);
        }

        return $data;
    }

    private function normalizeArray($array)
    {
        $normalized = array();

        $count = 1;
        foreach ($array as $key => $value) {
            if ($count++ >= 1000) {
                $normalized['...'] = 'Over 1000 items, aborting normalization';
                break;
            }

            $normalized = $this->processLogRecord($normalized, $key, $value, $count);
        }

        return $normalized;
    }

    private function processLogRecord($array, $key, $value, /** @scrutinizer ignore-unused */ $count)
    {
        switch ($key) {
            case 'message': return $this->processMessage($array, $value);
            case 'datetime': return $this->processDateTime($array, $value);
            case 'level':
                $array['@l'] = $this->logLevelMap[$value];
                $array['LogLevelCode'] = $value;
                return $array;
            case 'level_name': return $array;
            case 'extra': return $this->processExtras($array, $value);
            case 'context': return $this->processContext($array, $value);
            default:
                $array[is_int($key) ? $key : SeqCompactJsonFormatter::ConvertSnakeCaseToPascalCase($key)] = $this->normalize($value);
                return $array;
        }
    }

    private function processMessage($array, $value)
    {
        $array['@m'] = $value;
        if (!(strpos($value, '{') === false)) {
            $array['@mt'] = $value;
        }

        return $array;
    }

    private function processDateTime($array, $value)
    {
        if ($value instanceof \DateTime) {
            $value = $value->format(DateTime::ISO8601);
        }
        $array['@t'] = $value;

        return $array;
    }

    private function processExtras($array, $value)
    {
        if (is_array($value) && is_array($normalizedArray = $this->normalize($value))) {
            if ($this->extractExtras) {
                $array = array_merge($normalizedArray, $array);
            } else {
                $array['Extra'] = $normalizedArray;
            }
        }

        return $array;
    }

    private function processContext($array, $value)
    {
        if (!is_array($value)) {
            return $array;
        }

        if (is_array($normalizedArray = $this->normalize($value))) {
            $array = $this->extractContext ? array_merge($normalizedArray, $array) : $normalizedArray;
        }

        $exception = $this->extractException($value);
        if ($exception === null) {
            return $array;
        }

        $exception = $this->normalizeException($exception);
        $array['@x'] = $exception;

        return $array;
    }
}