<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Collections;

use Sazanof\PhpImapSockets\Connection;
use Sazanof\PhpImapSockets\Exceptions\ConnectionException;
use Sazanof\PhpImapSockets\Models\Mailbox;
use Sazanof\PhpImapSockets\Response\Response;
use Sazanof\PhpImapSockets\Traits\FromResponse;

class MailboxCollection extends Collection
{
	use FromResponse;

	/**
	 * @var array|null
	 */
	protected ?array $lines;

	/**
	 * @param Response $response
	 * @param Connection|null $connection
	 * @throws ConnectionException
	 */
	public function __construct(Response $response, Connection $connection = null)
	{
		if (is_null($connection)) {
			throw new ConnectionException('Connection must be specified!');
		}
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
