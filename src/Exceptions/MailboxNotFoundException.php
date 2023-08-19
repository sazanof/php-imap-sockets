<?php

namespace Sazanof\PhpImapSockets\Exceptions;

class MailboxNotFoundException extends \Exception
{
	public function __construct(string $message = "Mailbox does not exists", int $code = 0, ?\Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
