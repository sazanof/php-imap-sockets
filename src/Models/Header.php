<?php

namespace Sazanof\PhpImapSockets\Models;

class Header
{
	protected string $key;
	protected string $value;

	public function __construct(string $headerLine)
	{
		if (preg_match('/^(.+?): (.+?);?\r\n/', $headerLine, $matches)) {
			$this->key = strtolower($matches[1]);
			$this->value = $this->isUtf8($matches[2]) ? imap_utf8($matches[2]) : $matches[2];
		}
	}

	public function isUtf8(string $string)
	{
		return str_starts_with(strtoupper($string), '=?UTF-8');
	}

	/**
	 * @return string
	 */
	public function getKey(): string
	{
		return $this->key;
	}

	/**
	 * @return string
	 */
	public function getValue(): string
	{
		return $this->value;
	}
}
