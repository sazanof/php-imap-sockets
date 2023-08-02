<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Models;

use Sazanof\PhpImapSockets\Collections\Collection;
use Sazanof\PhpImapSockets\Collections\MessageHeadersCollection;

class Message
{
	protected int $uid;
	protected int $num;
	protected string $from;
	protected string $to;
	protected string $subject;
	protected MessageHeadersCollection $headers;
	protected BodyStructureOld $bodyStructure;

	public function __construct()
	{

	}

	/**
	 * @param int $uid
	 */
	public function setUid(int $uid): void
	{
		$this->uid = $uid;
	}

	/**
	 * @return int
	 */
	public function getUid(): int
	{
		return $this->uid;
	}

	/**
	 * @param string $subject
	 */
	public function setSubject(string $subject): void
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
	 * @param string $from
	 */
	public function setFrom(string $from): void
	{
		$this->from = $from;
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
	 * @param array|MessageHeadersCollection $headers
	 */
	public function setHeaders(array|MessageHeadersCollection $headers): void
	{
		$this->headers = $headers instanceof MessageHeadersCollection ? $headers : new MessageHeadersCollection($headers);
		$s = $this->getHeaders()->getValue('subject');
		if (!empty($s)) {
			$this->setSubject($s[0]->getValue());
		}
	}

	/**
	 * @return Collection
	 */
	public function getHeaders(): MessageHeadersCollection
	{
		return $this->headers;
	}

	/**
	 * @param BodyStructureOld $bodyStructure
	 */
	public function setBodyStructure(BodyStructureOld $bodyStructure): void
	{
		$this->bodyStructure = $bodyStructure;
	}

	/**
	 * @return BodyStructureOld
	 */
	public function getBodyStructure(): BodyStructureOld
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
