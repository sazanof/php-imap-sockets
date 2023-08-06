<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Models;

use Sazanof\PhpImapSockets\Collections\AddressesCollection;
use Sazanof\PhpImapSockets\Collections\MessageHeadersCollection;

class Message
{
	protected int $uid;
	protected int $num;
	protected string $messageId;
	protected string|Address $from;
	protected string|Address $to;
	protected string|AddressesCollection $cc;
	protected string|AddressesCollection $bcc;
	protected ?string $subject;
	protected \DateTime $date;
	protected MessageHeadersCollection $headers;
	protected ?MultiPart $bodyStructure;
	protected bool $hasAttachments = false;
	protected array $flags;

	public function __construct()
	{

	}

	/**
	 * @param array $flags
	 */
	public function setFlags(array $flags): void
	{
		$this->flags = $flags;
	}

	/**
	 * @param int $uid
	 */
	public function setUid(int $uid): void
	{
		$this->uid = $uid;
	}

	/**
	 * @param string $messageId
	 */
	public function setMessageId(string $messageId): void
	{
		$this->messageId = $messageId;
	}

	/**
	 * @return string
	 */
	public function getMessageId(): string
	{
		return $this->messageId;
	}

	/**
	 * @return int
	 */
	public function getUid(): int
	{
		return $this->uid;
	}

	/**
	 * @param ?string $subject
	 */
	public function setSubject(?string $subject): void
	{
		$this->subject = $subject;
	}


	/**
	 * @return string
	 */
	public function getSubject(): string
	{
		return $this->subject;
	}

	/**
	 * @param string|Address $from
	 * @return void
	 */
	public function setFrom(string|Address $from): void
	{
		$this->from = $from instanceof Address ? $from : Address::firstFromString($from);
	}

	/**
	 * @param string $to
	 */
	public function setTo(string $to): void
	{
		$this->to = $to;
	}

	/**
	 * @param AddressesCollection|string $bcc
	 */
	public function setBcc(AddressesCollection|string $bcc): void
	{
		$this->bcc = $bcc;
	}

	/**
	 * @param AddressesCollection|string $cc
	 */
	public function setCc(AddressesCollection|string $cc): void
	{
		$this->cc = $cc;
	}

	/**
	 * @return AddressesCollection|string
	 */
	public function getBcc(): AddressesCollection|string
	{
		return $this->bcc;
	}

	/**
	 * @return AddressesCollection|string
	 */
	public function getCc(): AddressesCollection|string
	{
		return $this->cc;
	}

	/**
	 * @return array
	 */
	public function getFlags(): array
	{
		return $this->flags;
	}

	/**
	 * @return string
	 */
	public function getFrom(): string
	{
		return $this->from;
	}

	/**
	 * @param int $num
	 */
	public function setNum(int $num): void
	{
		$this->num = $num;
	}

	/**
	 * @return int
	 */
	public function getNum(): int
	{
		return $this->num;
	}

	/**
	 * @param \DateTime $date
	 */
	public function setDate(\DateTime $date): void
	{
		$this->date = $date;
	}

	/**
	 * @return \DateTime
	 */
	public function getDate(): \DateTime
	{
		return $this->date;
	}

	/**
	 * @param array|MessageHeadersCollection $headers
	 * @return void
	 * @throws \Exception
	 */
	public function setHeaders(array|MessageHeadersCollection $headers): void
	{
		$this->headers = $headers instanceof MessageHeadersCollection ? $headers : new MessageHeadersCollection($headers);
		$this->setMessageId(
			$this->getHeaders()->getHeader('message-id')->getValue()
		);
		$this->setSubject(
			$this->getHeaders()->getHeader('subject')->getValue()
		);
		$this->setDate(
			new \DateTime(
				$this->getHeaders()->getHeader('date')->getValue()
			)
		);
		$this->setFrom(
			$this->getHeaders()->getHeader('from')->getValue()
		);

	}

	/**
	 * @return MessageHeadersCollection
	 */
	public function getHeaders(): MessageHeadersCollection
	{
		return $this->headers;
	}

	/**
	 * @param ?MultiPart $bodyStructure
	 */
	public function setBodyStructure(?MultiPart $bodyStructure): void
	{
		$this->bodyStructure = $bodyStructure;
		if (!is_null($bodyStructure)) {
			$this->hasAttachments = $this->bodyStructure->isAttachmentsExists();
		}
	}

	/**
	 * @return MultiPart|null
	 */
	public function getBodyStructure(): ?MultiPart
	{
		return $this->bodyStructure;
	}

	/**
	 * @return string
	 */
	public function getTo(): string
	{
		return $this->to;
	}
}
