<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Models;

class Message
{
	protected int $messageUid;
	protected string $from;
	protected string $to;
	protected string $subject;

	public function __construct()
	{

	}
}
