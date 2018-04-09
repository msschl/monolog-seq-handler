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
            $normalized = array();

            $count = 1;
            foreach ($data as $key => $value) {
                if ($count++ >= 1000) {
                    $normalized['...'] = 'Over 1000 items, aborting normalization';
                    break;
                }

                switch ($key) {
                    case 'message':
                        $normalized['@m'] = $value;
                        if (!(strpos($value, '{') === false)) {
                            $normalized['@mt'] = $value;
                        }
                        break;

                    case 'datetime':
                        if ($value instanceof \DateTime) {
                            $value = $value->format(DateTime::ISO8601);
                        }
                        $normalized['@t'] = $value;
                        break;

                    case 'level':
                        $normalized['@l'] = $this->logLevelMap[$value];
                        $normalized['LogLevelCode'] = $value;
                        break;
                    case 'level_name':
                        break;

                    case 'extra':
                        if (is_array($value)) {
                            $normalizedArray = $this->normalize($value);

                            if ($this->extractExtras) {
                                $normalized = array_merge($normalizedArray, $normalized);
                            } else {
                                $normalized['Extra'] = $normalizedArray;
                            }
                        } else {
                            $normalized[is_int($key) ? $key : SeqCompactJsonFormatter::ConvertSnakeCaseToPascalCase($key)] = $this->normalize($value);
                        }
                        break;

                    case 'context':
                        $exception = $this->extractException($value);
                        $normalizedArray = $this->normalize($value);

                        if ($this->extractContext) {
                            $normalized = array_merge($normalizedArray, $normalized);
                        } else {
                            $normalized['Context'] = $normalizedArray;
                        }

                        if ($exception !== null) {
                            if (($exception instanceof Exception || $exception instanceof Throwable)) {
                                $exception = $this->normalizeException($exception);
                            }

                            $normalized['@x'] = $exception;
                        }
                        break;

                    default:
                        $normalized[is_int($key) ? $key : SeqCompactJsonFormatter::ConvertSnakeCaseToPascalCase($key)] = $this->normalize($value);
                        break;
                }
            }

            return $normalized;
        }

        if ($data instanceof \Exception || $data instanceof \Throwable) {
            return $this->normalizeException($data);
        }

        return $data;
    }
}