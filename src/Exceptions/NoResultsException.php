<?php

namespace Sazanof\PhpImapSockets\Exceptions;

class NoResultsException extends \Exception
{
	public function __construct(string $message = "The search results is empty", int $code = 0, ?\Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
