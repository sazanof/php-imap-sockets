<?php

namespace Sazanof\PhpImapSockets\Models;

use Sazanof\PhpImapSockets\Collections\Collection;
use Sazanof\PhpImapSockets\Parts\AttachmentPart;
use Sazanof\PhpImapSockets\Parts\TextPart;

class MultiPart
{
	protected string $section;
	protected ?string $subtype;
	protected bool $attachmentsExists = false;
	protected ?int $attachmentsCount = null;
	protected ?string $boundary = null;
	protected Collection $parts;

	public function __construct(array $matches, string $section)
	{
		$this->setSection($section);
		$this->parts = new Collection();
		// quick fix
		$this->subtype = isset($matches[2]) && in_array($matches[2], ['alternative', 'related', 'mixed']) ? $matches[2] : null;
		$this->setBoundary(preg_replace('/"boundary" "|"?/', '', $matches[3]));
	}

	/**
	 * @param string|null $boundary
	 */
	public function setBoundary(?string $boundary): void
	{
		$this->boundary = $boundary;
	}

	/**
	 * @return string|null
	 */
	public function getBoundary(): ?string
	{
		return $this->boundary;
	}

	/**
	 * @param MultiPart|TextPart|AttachmentPart|null $part
	 * @param array $out
	 * @return array|TextPart[]
	 * @todo DRY
	 */
	public function getTextParts(MultiPart|TextPart|AttachmentPart $part = null, array $out = []): array
	{
		/** @var MultiPart|TextPart|AttachmentPart $part */
		$partToForeach = is_null($part) ? $this : $part;
		foreach ($partToForeach->getParts()->items() as $_part) {
			if ($_part instanceof TextPart || ($_part instanceof AttachmentPart && $_part->isInline())) {
				$out[] = $_part;
			} elseif ($_part instanceof MultiPart) {
				$out = $this->getTextParts($_part, $out);
			}
		}
		return $out;
	}

	/**
	 * @param MultiPart|AttachmentPart|null $part
	 * @param array|AttachmentPart[] $out
	 * @return array
	 * @todo DRY
	 */
	public function getAttachmentParts(MultiPart|AttachmentPart $part = null, array $out = []): array
	{
		/** @var MultiPart|AttachmentPart $part */
		$partToForeach = is_null($part) ? $this : $part;
		foreach ($partToForeach->getParts()->items() as $_part) {
			if ($_part instanceof AttachmentPart) {
				$out[] = $_part;
			} elseif ($_part instanceof MultiPart) {
				$out = $this->getAttachmentParts($_part, $out);
			}
		}
		return $out;
	}

	/**
	 * @param string $section
	 */
	public function setSection(string $section): void
	{
		$this->section = $section;
	}

	/**
	 * @return string
	 */
	public function getSection(): string
	{
		return $this->section;
	}

	/**
	 * @param bool $attachmentsExists
	 */
	public function setAttachmentsExists(bool $attachmentsExists): void
	{
		$this->attachmentsExists = $attachmentsExists;
	}

	/**
	 * @param bool|null $attachmentsCount
	 */
	public function setAttachmentsCount(?bool $attachmentsCount): void
	{
		$this->attachmentsCount = $attachmentsCount;
	}

	/**
	 * @return void
	 */
	public function plusOneToAttachmentsCount(): void
	{
		$this->attachmentsCount++;
	}

	/**
	 * @return bool
	 */
	public function isAttachmentsExists(): bool
	{
		return $this->attachmentsExists;
	}

	/**
	 * @return Collection
	 */
	public function getParts(): Collection
	{
		return $this->parts;
	}

	/**
	 * @return string
	 */
	public function getSubtype(): string
	{
		return $this->subtype;
	}
}
