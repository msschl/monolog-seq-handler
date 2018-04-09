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
abstract class SeqBaseFormatter extends JsonFormatter
{

    /**
     * The log level map.
     * Maps the monolog log levels to the seq log levels.
     *
     * @var array
     */
    protected $logLevelMap = [
        '100' => 'Debug',
        '200' => 'Information',
        '250' => 'Information',
        '300' => 'Warning',
        '400' => 'Error',
        '500' => 'Error',
        '550' => 'Fatal',
        '600' => 'Fatal',
    ];

    /**
     * Returns a string with the content type for the seq-formatter.
     *
     * @return string
     */
    public abstract function getContentType() : string;

    /**
     * Normalizes an exception to a string.
     *
     * @param  Throwable $e The throwable instance to normalize.
     * @return string
     */
	protected function normalizeException($e) : string
    {
   		$previousText = '';
        if ($previous = $e->getPrevious()) {
            do {
                $previousText .= ', ' . get_class($previous) . '(code: ' . $previous->getCode() . '): ' . $previous->getMessage() . ' at ' . $previous->getFile() . ':' . $previous->getLine();
            } while ($previous = $previous->getPrevious());
        }

        $str = '[object] (' . get_class($e) . '(code: ' . $e->getCode() . '): ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine() . $previousText . ')';
        if ($this->includeStacktraces) {
            $str .= "\n[stacktrace]\n" . $e->getTraceAsString() . "\n";
        }

        return $str;
    }

    /**
     * Extracts the exception from an array.
     *
     * @param  array  &$array The array.
     * @return \Throwable|null
     */
    protected function extractException(array &$array) {
        $exception = $array['exception'] ?? null;

        if ($exception === null) {
            return null;
        }

        unset($array['exception']);

        if (!($exception instanceof \Throwable)) {
            return null;
        }

        return $exception;
    }

    /**
     * Converts a snake case string to a pascal case string.
     *
     * @param  string $value The string to convert.
     * @return string
     */
    protected static function ConvertSnakeCaseToPascalCase(string $value = null) : string {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
    }
}