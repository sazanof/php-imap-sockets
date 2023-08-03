<?php

namespace Sazanof\PhpImapSockets\Models;

use Sazanof\PhpImapSockets\Collections\Collection;

class MultiPart
{
	protected ?string $subtype;
	protected Collection $parts;
	protected Collection $children;

	public function __construct(array $matches)
	{
		$this->parts = new Collection();
		$this->children = new Collection();
		// quick fix
		$this->subtype = in_array($matches[2], ['alternative', 'related', 'mixed']) ? $matches[2] : null;
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
