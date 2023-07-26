<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Collections;

use Sazanof\PhpImapSockets\Connection;
use Sazanof\PhpImapSockets\Models\Mailbox;
use Sazanof\PhpImapSockets\Response;
use Sazanof\PhpImapSockets\Traits\FromResponse;

class MailboxCollection extends Collection
{
	use FromResponse;

	/**
	 * @var string $tag
	 */
	protected string $tag;

	protected ?Connection $connection = null;

	/**
	 * @var array|null
	 */
	protected ?array $lines;

	public function __construct(Response $response, Connection $connection = null)
	{
		$this->lines = $this->getLines($response);
		if (!is_null($this->lines)) {
			foreach ($this->lines as $line) {
				$mailbox = new Mailbox($line);
				$mailbox->setConnection($connection)->examine();
				$this->add($mailbox);
			}
		}
	}
}
