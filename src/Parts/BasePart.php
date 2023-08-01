<?php

namespace Sazanof\PhpImapSockets\Parts;

class BasePart
{
	protected string $type;
	protected string $subtype;
	protected string $text;
	protected string $mimeType;
	private string $re;

	public function __construct(string $type, string $text)
	{
		$this->text = $text;
		$this->type = $type;
		$this->detectSubType();
		$this->mimeType = "$this->type/$this->subtype";
	}

	/**
	 * @return void
	 */
	protected function detectSubType(): void
	{
		if (preg_match('/"' . $this->type . '" "(.*?)"/', $this->text, $matches)) {
			$this->subtype = $matches[1];
		}
	}

	/**
	 * @param string $value
	 * @return string|null
	 */
	protected function setValue(string $value): ?string
	{
		return $value === 'NIL' ? null : $this->trimValue($value);
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
}
