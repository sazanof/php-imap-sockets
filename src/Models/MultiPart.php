<?php

namespace Sazanof\PhpImapSockets\Models;

use Sazanof\PhpImapSockets\Collections\Collection;

class MultiPart
{
	protected ?string $subtype;
	protected Collection $parts;
	protected Collection $children;
	protected bool $attachmentsExists = false;
	protected ?int $attachmentsCount = null;

	public function __construct(array $matches)
	{
		$this->parts = new Collection();
		$this->children = new Collection();
		// quick fix
		$this->subtype = in_array($matches[2], ['alternative', 'related', 'mixed']) ? $matches[2] : null;
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
	public function getChildren(): Collection
	{
		return $this->children;
	}

	public function addChild(MultiPart $multiPart)
	{
		$this->children->add($multiPart);
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
