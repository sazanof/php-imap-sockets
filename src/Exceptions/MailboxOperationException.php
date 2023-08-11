<?php

namespace Sazanof\PhpImapSockets\Exceptions;

class MailboxOperationException extends \Exception
{
	public function __construct(string $message = "Error creating folder", int $code = 0, ?\Throwable $previous = null)
	{
		if (preg_match('/^(\d+) (BAD|NO) (.+?)\./', $message, $m)) {
			$message = $m[3];
		}
		parent::__construct($message, $code, $previous);
	}
}
