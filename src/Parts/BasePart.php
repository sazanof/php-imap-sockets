<?php

namespace Sazanof\PhpImapSockets\Parts;

use Sazanof\PhpImapSockets\Collections\Collection;

class BasePart
{
	protected string $type;
	protected string $subtype;
	protected string $mimeType;
	protected string $section;
	protected Collection $children;

	public function __construct(array $matches, string $section)
	{
		$this->children = new Collection();
		$this->setSection($section);
		$this->type = $matches[1];
		$this->subtype = $matches[2];
		$this->mimeType = "$matches[1]/$matches[2]";
	}

	/**
	 * @param Collection $children
	 */
	public function setChildren(Collection $children): void
	{
		$this->children = $children;
	}

	public function addChild(mixed $child)
	{
		$this->children->add($child);
	}

	/**
	 * @return Collection
	 */
	public function getChildren(): Collection
	{
		return $this->children;
	}

	public function hasChildren()
	{
		return $this->getChildren()->count() > 0;
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

	protected function setValue(string $value): ?string
	{
		return $value === 'NIL' ? null : $this->trimValue(
			$this->isUtf8($value) ? imap_utf8($value) : $value
		);
	}

	/**
	 * @param $value
	 * @return string
	 */
	private function trimValue($value): string
	{
		$value = rtrim($value, '"');
		return ltrim($value, '"');
	}

	protected function fillValuesFromMatches()
	{

	}

	protected function isUtf8(string $string)
	{
		return str_starts_with(strtoupper($string), '=?UTF-8');
	}
}
