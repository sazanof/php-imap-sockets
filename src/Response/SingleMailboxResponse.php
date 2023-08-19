<?php

namespace Sazanof\PhpImapSockets\Response;

use Sazanof\PhpImapSockets\Exceptions\MailboxNotFoundException;
use Sazanof\PhpImapSockets\Exceptions\MailboxOperationException;
use Sazanof\PhpImapSockets\Models\Mailbox;

class SingleMailboxResponse
{
	protected ?Mailbox $mailbox = null;

	/**
	 * @param string $path
	 * @param Response $response
	 * @throws MailboxNotFoundException
	 */
	public function __construct(string $path, Response $response)
	{
		foreach ($response->lines() as $line) {
			if (preg_match('/\* LIST \((.*?)\) (.*?) "?(.*?)"?\r?\n?$/', $line, $matches)) {
				if (trim($matches[3]) === $path) {
					$this->mailbox = new Mailbox($line);
				}
			}
		}
		if ($this->mailbox === null) {
			throw new MailboxNotFoundException("Error getting mailbox with pah: $path");
		}
	}

	/**
	 * @return Mailbox
	 */
	public function getMailbox(): Mailbox
	{
		return $this->mailbox;
	}
}
