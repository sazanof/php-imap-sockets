<?php

namespace Sazanof\PhpImapSockets\Parts;

class BasePart
{
	protected string $type;
	protected string $subtype;
	protected string $mimeType;
	protected array $matches;

	public function __construct(array $matches)
	{
		$this->matches = $matches;
		$this->type = $matches[1];
		$this->subtype = $matches[2];
		$this->mimeType = "$matches[1]/$matches[2]";
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
