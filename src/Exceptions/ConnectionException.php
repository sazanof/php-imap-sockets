<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Exceptions;

use Exception;

class ConnectionException extends Exception
{
	public function __construct($message = "Error while connect to socket", $code = 0, \Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
