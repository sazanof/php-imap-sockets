<?php

namespace Sazanof\PhpImapSockets\Response;

use Sazanof\PhpImapSockets\Models\Mailbox;

class SingleMailboxResponse
{
	protected Mailbox $mailbox;

	public function __construct(string $path, Response $response)
	{
		foreach ($response->lines() as $line) {
			if (preg_match('/\* LIST \((.*?)\) (.*?) "(.*?)"/', $line, $matches)) {
				if ($matches[3] === $path) {
					$this->mailbox = new Mailbox($line);
				}
			}
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
