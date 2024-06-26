<?php

namespace Sazanof\PhpImapSockets\Collections;

use Sazanof\PhpImapSockets\Connection;
use Sazanof\PhpImapSockets\Models\Mailbox;
use Sazanof\PhpImapSockets\Models\Message;
use Sazanof\PhpImapSockets\Query\FetchQuery;

class MessageCollection extends Collection
{
	protected array $msgNums;
	protected FetchQuery $query;
	protected Connection $connection;
	protected Mailbox $mailbox;

	public function __construct($msgNums, Mailbox $mailbox)
	{
		parent::__construct();
		$this->msgNums = array_values($msgNums);
		$this->query = new FetchQuery();
		$this->mailbox = $mailbox;
		$this->connection = $mailbox->getConnection();

		$this->findUids();
		$flags = $this->findFlags();
		$bodyStructure = $this->parseBodyStructure();
		$headers = $this->findHeaders();
		$this->map(function (/** @var Message $item */ $item) use ($headers, $bodyStructure, $flags) {
			/** @var array $_headers */
			$msgNum = $item->getNum();
			$_headers = $headers->getHeaders()[$msgNum];
			$item->setHeaders($_headers);
			$item->setFlags($flags->get($msgNum));
			$item->setBodyStructure($bodyStructure->getItem($msgNum)->getMultiPart());
			$item->setMailbox($this->mailbox);
			//dd($item->getBody());
		});
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

	/**
	 * @return FlagsCollection
	 */
	public function findFlags(): FlagsCollection
	{
		return $this->mailbox->fetch($this->msgNums, $this->query->clear()->flags())->asCollection(FlagsCollection::class);
	}

	public function findHeaders(array $fields = [
		'From',
		'To',
		'Cc',
		'Bcc',
		'Date',
		'Message-ID',
		'Resent-Message-Id',
		'Messageid',
		'Reply-To',
		'References',
		'In-Reply-To',
		'Return-Path',
		'Subject',
		'Content-Type'
	])
	{
		$this->mailbox->fetch(
			$this->msgNums,
			$this->query->clear()->body(
				FetchQuery::BODY_HEADER_FIELDS, $fields
			)
		);
		return new HeadersCollection($this->connection->lastResponse());
	}

	/**
	 * @return BodyStructureCollection
	 */
	public function parseBodyStructure(): BodyStructureCollection
	{
		return $this->mailbox->getBodyStructure($this->msgNums);
	}
}
