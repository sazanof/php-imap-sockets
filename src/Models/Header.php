<?php

namespace Sazanof\PhpImapSockets\Models;

class Header
{
	protected string $key;
	protected string $value;
	protected string $charset = '';

	public function __construct(string $headerLine)
	{
		if (preg_match('/^(.+?): (.+?);?\r\n/', $headerLine, $matches)) {
			$this->key = strtolower($matches[1]);
			////////// WRAP THIS SEPARATE
			if ($this->isUtf8($matches[2])) {
				if (preg_match('/(^|)=\?.*?\?Q|q\?/', $matches[2], $_m)) {
					$this->charset = strtoupper($_m[1]);
					$this->value = quoted_printable_decode(
						preg_replace('/(^|)=\?.*?\?.\?|\?=$/', '', $matches[2])
					);
				} elseif (preg_match('/(^|)=\?.*?\?B|b\?/', $matches[2], $_m)) {
					$this->charset = strtoupper($_m[1]);
					$this->value = imap_base64(
						$matches[2]);
				} else {
					$this->value = imap_utf8($matches[2]);
				}
			} else {
				$this->value = $matches[2];
			}
			/////////////////// IN METHOD WHICH DETECT DECODING
		}
	}

	public function isUtf8(string $string)
	{
		return str_contains(strtoupper($string), '=?UTF-8');
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
