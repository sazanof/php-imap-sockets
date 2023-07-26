<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Exceptions;

use Sazanof\PhpImapSockets\Response;

class ImapException extends \Exception
{
	public function __construct($message = null, $code = 0, \Exception $previous = null)
	{
		if ($message instanceof Response) {
			$message = $message->error();
		}

		parent::__construct($message, $code, $previous);
	}
}
