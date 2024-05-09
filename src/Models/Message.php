<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Models;

use Sazanof\PhpImapSockets\Collections\AddressesCollection;
use Sazanof\PhpImapSockets\Collections\MessageHeadersCollection;
use Sazanof\PhpImapSockets\Parts\AttachmentPart;
use Sazanof\PhpImapSockets\Parts\TextPart;
use Sazanof\PhpImapSockets\Query\FetchQuery;
use Sazanof\PhpImapSockets\Response\AttachmentBodyResponse;
use Sazanof\PhpImapSockets\Response\BodyResponse;

class Message
{
	const FLAG_IMPORTANT = '\flagged';
	const FLAG_DELETED = '\deleted';
	protected int $uid;
	protected int $num;
	protected ?string $messageId;
	protected Address $from;
	protected ?AddressesCollection $to = null;
	protected ?AddressesCollection $cc = null;
	protected ?AddressesCollection $bcc = null;
	protected ?string $subject;
	protected ?\DateTime $date = null;
	protected MessageHeadersCollection $headers;
	protected ?MultiPart $bodyStructure;
	protected ?Mailbox $mailbox = null;
	protected string $boundary;
	protected string $contentType;
	protected bool $hasAttachments = false;
	protected bool $isImportant = false;
	protected array $flags;
	protected ?string $body = null;
	protected ?string $references = null;
	protected ?string $inReplyTo = null;

	public function __construct()
	{

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
			$this->getHeaders()->getHeader('message-id')?->getValue()
		);
		$this->setSubject(
			!is_null($this->getHeaders()->getHeader('subject')) ? $this->getHeaders()->getHeader('subject')->getValue() : null
		);
		try {
			$this->setDate(
				new \DateTime(
					$this->getHeaders()->getHeader('date')?->getValue()
				)
			);
		} catch (\Exception $exception) {
			try {
				$date = trim(preg_replace('#\\(.*\\)#', '', $this->getHeaders()->getHeader('date')->getValue()));
				$this->setDate(
					new \DateTime($date)
				);
			} catch (\Exception $e) {
				dd($e);
			}

		}

		$this->setFrom(
			$this->getHeaders()->getHeader('from')->getValue()
		);
		$this->setTo(
			$this->getHeaders()->getHeader('to')->getValue()
		);

		$this->setReferences(
			$this->getHeaders()->getHeader('references') ? $this->getHeaders()->getHeader('references')->getValue() : null
		);

		$this->setInReplyTo(
			$this->getHeaders()->getHeader('in-reply-to') ? $this->getHeaders()->getHeader('in-reply-to')->getValue() : null
		);
		$contentType = $this->getHeaders()->getHeader('content-type')->getValue();
		if (preg_match('/^(.*?)\/(.*?);boundary="(.*?)"$/', $contentType, $matches)) {
			$this->setBoundary($matches[3]);
			$ct = "$matches[1]/$matches[2]";
		} else {
			$ct = $contentType;
		}
		$this->setContentType($ct);

	}

	/**
	 * @return string|null
	 */
	public function getReferences(): ?string
	{
		return $this->references;
	}

	/**
	 * @param string|null $references
	 */
	public function setReferences(?string $references): void
	{
		$this->references = $references;
	}

	/**
	 * @param string|null $inReplyTo
	 */
	public function setInReplyTo(?string $inReplyTo): void
	{
		$this->inReplyTo = $inReplyTo;
	}

	/**
	 * @return string|null
	 */
	public function getInReplyTo(): ?string
	{
		return $this->inReplyTo;
	}


	/**
	 * @param Mailbox $mailbox
	 */
	public function setMailbox(Mailbox $mailbox): void
	{
		$this->mailbox = $mailbox;
	}

	/**
	 * @param TextPart $part
	 * @return false|string|null
	 * @throws \ReflectionException
	 */
	public function getBody(TextPart $part): bool|string|null
	{
		if (!is_null($this->mailbox)) {
			$q = new FetchQuery();
			$body = $this->mailbox->fetch([$this->num], $q->body($part->getSection()))->asCollection(BodyResponse::class);
			$body = $body->getContent();
			if ($part->getEncoding() === 'quoted-printable') {
				return imap_qprint($body);
			} elseif ($part->getEncoding() === 'base64') {
				return imap_base64($body);
			} else {
				return $body;
			}
		}
		return null;
	}

	/**
	 * @param AttachmentPart $part
	 * @return string
	 * @throws \ReflectionException
	 */
	public function getInlineImage(AttachmentPart $part)
	{
		if (!is_null($this->mailbox)) {
			$q = new FetchQuery();
			$body = $this->mailbox->fetch([$this->num], $q->body($part->getSection()))->asCollection(BodyResponse::class);
			$body = $body->getContent();
			return 'data:' . $part->getMimeType() . ';' . $part->getEncoding() . ',' . str_replace(["\r\n", "\r", "\n"], '', $body);
		}
		return '';
	}

	/**
	 * @return bool|string|null
	 * @throws \ReflectionException
	 */
	public function getHtmlText(): bool|string|null
	{
		if (is_null($this->getBodyStructure())) return null;
		foreach ($this->getBodyStructure()->getTextParts() as $textPart) {
			if ($textPart->getMimeType() === 'text/html') {
				$body = $this->getBody($textPart);
				$contentIds = $this->findContentIds($body);
				if (!empty($contentIds)) {
					// html part contains inline images
					foreach ($contentIds as $cid) {
						$inlinePart = $this->getPartByContentId($cid);
						$inlineContent = $this->getInlineImage($inlinePart);
						$body = str_replace("cid:$cid", $inlineContent, $body);
					}
				}
				return $body;
			}
		}
		return null;
	}

	/**
	 * @param string $cid
	 * @param MultiPart|null $_part
	 * @return AttachmentPart|null
	 */
	public function getPartByContentId(string $cid, MultiPart $_part = null)
	{
		if (is_null($this->getBodyStructure())) return null;
		$parts = is_null($_part) ? $this->getBodyStructure()->getParts() : $_part->getParts();
		/** @var AttachmentPart $part */
		foreach ($parts->items() as $part) {
			if ($part instanceof AttachmentPart) {
				if (strtolower($part->getDisposition()) === 'inline' && $part->getContentId() === "<$cid>") {
					return $part;
				}
			} elseif ($part instanceof MultiPart) {
				return $this->getPartByContentId($cid, $part);
			}
		}
		return null;
	}

	/**
	 * @param string $html
	 * @return mixed|void
	 */
	protected function findContentIds(string $html)
	{
		if (preg_match_all('/src="cid:(.*?)"/', $html, $matches)) {
			return $matches[1];
		}
	}

	/**
	 * @param int|null $length
	 * @return bool|string|null
	 * @throws \ReflectionException
	 */
	public function getPlainText(int $length = null)
	{
		if (is_null($this->getBodyStructure())) return null;
		foreach ($this->getBodyStructure()->getTextParts() as $textPart) {
			if ($textPart->getMimeType() === 'text/plain') {
				return $length > 0 ? $this->trimText($this->getBody($textPart)) : $this->getBody($textPart);
			}
		}
		return null;
	}

	/**
	 * @param string $text
	 * @param int $length
	 * @return string
	 */
	protected function trimText(string $text, int $length = 140)
	{
		$text = str_replace(["\r\n", "\r", "\n"], ' ', $text);
		if (strlen($text) > $length) {
			$offset = ($length - 3) - strlen($text);
			return substr($text, 0, strrpos($text, ' ', $offset)) . '...';
		}
		return $text;
	}

	/**
	 * @return bool
	 */
	public function isHasAttachments(): bool
	{
		return $this->hasAttachments;
	}


	/**
	 * @param array $flags
	 */
	public function setFlags(array $flags): void
	{
		$this->flags = $flags;
		$this->updateIsImportant();
	}

	public function markAsDeleted()
	{
		$this->addFlags(self::FLAG_DELETED);
		return $this;
	}

	/**
	 * @return void
	 */
	public function updateIsImportant(): void
	{
		$this->isImportant = in_array(self::FLAG_IMPORTANT, $this->getFlags());
	}

	/**
	 * @param int $uid
	 */
	public function setUid(int $uid): void
	{
		$this->uid = $uid;
	}

	/**
	 * @param ?string $messageId
	 */
	public function setMessageId(?string $messageId): void
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
	 * @return ?string
	 */
	public function getSubject(): ?string
	{
		return $this->subject;
	}

	/**
	 * @return bool
	 */
	public function isImportant(): bool
	{
		return $this->isImportant;
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
	 * @param AddressesCollection|string $to
	 */
	public function setTo(string|AddressesCollection $to): void
	{
		if (is_string($to)) {
			$to = new AddressesCollection($to);
		}
		$this->to = $to;
	}

	/**
	 * @param string|AddressesCollection $bcc
	 */
	public function setBcc(string|AddressesCollection $bcc): void
	{
		if (is_string($bcc)) {
			$bcc = new AddressesCollection($bcc);
		}
		$this->bcc = $bcc;
	}

	/**
	 * @param string|AddressesCollection $cc
	 */
	public function setCc(string|AddressesCollection $cc): void
	{
		if (is_string($cc)) {
			$cc = new AddressesCollection($cc);
		}
		$this->cc = $cc;
	}

	/**
	 * @return ?AddressesCollection
	 */
	public function getBcc(): ?AddressesCollection
	{
		return $this->bcc;
	}

	/**
	 * @return ?AddressesCollection
	 */
	public function getCc(): ?AddressesCollection
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

	public function addFlags(string|array $flag)
	{
		if (is_array($flag)) {
			$this->flags = array_merge($this->flags, $flag);
		} else {
			if (!in_array($flag, $this->getFlags())) {
				$this->flags[] = $flag;
			}
		}
		return $this;
	}

	public function deleteFlags(string|array $flag)
	{
		if (is_string($flag)) {
			$flag = [$flag];
		}
		$this->flags = array_diff($this->flags, $flag);
		return $this;
	}

	public function clearFlags()
	{
		$this->flags = [];
		return $this;
	}

	public function replaceFlags(string|array $flag)
	{
		if (is_string($flag)) {
			$flag = [$flag];
		}
		$this->flags = $flag;
		return $this;
	}

	public function saveFlags()
	{
		return $this->getMailbox()->store([$this->getNum()], $this->getFlags(), false, true);
	}

	/**
	 * @return $this
	 */
	public function setImportant()
	{
		$this->isImportant = true;
		return $this->addFlags(self::FLAG_IMPORTANT);
	}

	/**
	 * @return $this
	 */
	public function unsetImportant()
	{
		$this->isImportant = false;
		return $this->deleteFlags(self::FLAG_IMPORTANT);
	}


	/**
	 * @return Address
	 */
	public function getFrom(): Address
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
	 * @return string
	 */
	public function getBoundary(): string
	{
		return $this->boundary;
	}

	/**
	 * @return string
	 */
	public function getContentType(): string
	{
		return $this->contentType;
	}

	/**
	 * @return Mailbox|null
	 */
	public function getMailbox(): ?Mailbox
	{
		return $this->mailbox;
	}

	/**
	 * @param string $boundary
	 */
	public function setBoundary(string $boundary): void
	{
		$this->boundary = $boundary;
	}

	/**
	 * @param string $contentType
	 */
	public function setContentType(string $contentType): void
	{
		$this->contentType = $contentType;
	}

	/**
	 * @return ?\DateTime
	 */
	public function getDate(): ?\DateTime
	{
		return $this->date;
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
	 * @return AddressesCollection
	 */
	public function getTo(): AddressesCollection
	{
		return $this->to;
	}

	public function getAttachment(string $part)
	{
		$fq = new FetchQuery();
		return $this->getMailbox()->fetch([$this->getNum()], $fq->body($part))->asCollection(AttachmentBodyResponse::class);
	}
}
