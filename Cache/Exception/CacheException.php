<?php

namespace Sruuua\Cache\Exception;
// namespace Sruuua\Cache\Exception;s

use Sruuua\Cache\Interface\CacheExceptionInterface;

/**
 * Exception interface for all exceptions thrown by an Implementing Library.
 */
class CacheException extends \Exception implements CacheExceptionInterface, \Throwable
{
}
