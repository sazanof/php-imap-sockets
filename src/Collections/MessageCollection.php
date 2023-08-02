<?php

namespace Sazanof\PhpImapSockets\Collections;

use Sazanof\PhpImapSockets\Connection;
use Sazanof\PhpImapSockets\Models\Mailbox;
use Sazanof\PhpImapSockets\Models\Message;
use Sazanof\PhpImapSockets\Query\FetchQuery;
use Sazanof\PhpImapSockets\Response\Response;

class MessageCollection extends Collection
{
	protected array $msgNums;
	protected FetchQuery $query;
	protected Connection $connection;
	protected Mailbox $mailbox;

	public function __construct($msgNums, Mailbox $mailbox)
	{
		$this->msgNums = $msgNums;
		$this->query = new FetchQuery();
		$this->mailbox = $mailbox;
		$this->connection = $mailbox->getConnection();

		$this->findUids();
		$this->parseBodyStructure();
		$headers = $this->findHeaders();
		$this->map(function (/** @var Message $item */ $item) use ($headers) {
			/** @var array $_headers */
			$_headers = $headers->getHeaders()[$item->getNum()];
			$item->setHeaders($_headers);
		});
		dd($this->collection);
	}

	public function findUids()
	{
		$this->mailbox->fetch($this->msgNums, $this->query->uid());
		$r = $this->connection->lastResponse();
		if ($r->isOk()) {
			foreach ($r->lines() as $item) {
				if (preg_match('/\* (\d+) FETCH \(UID (\d+)\)/', $item, $matches) && $r->lastLine() !== $item) {
					$message = new Message();
					$message->setUid((int)$matches[2]);
					$message->setNum((int)$matches[1]);
					$this->add($message);
				}
			}
		}
	}

	public function findHeaders()
	{
		$this->mailbox->fetch(
			$this->msgNums,
			$this->query->clear()->body(
				FetchQuery::BODY_HEADER_FIELDS, [
					'From',
					'To',
					'Date',
					'Message-ID',
					'Reply-To',
					'Return-Path',
					'Subject',
					'Content-Type'
				]
			)
		);
		return new HeadersCollection($this->connection->lastResponse());

	}

	public function parseBodyStructure()
	{
		$response = $this->mailbox->getBodyStructure($this->msgNums);
		dd($response);
	}
}
