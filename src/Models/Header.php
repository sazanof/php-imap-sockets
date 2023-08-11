<?php

namespace Sazanof\PhpImapSockets\Models;

class Header
{
	protected string $key;
	protected ?string $value;
	protected ?string $charset = null;

	public function __construct(string $headerLine)
	{
		if (preg_match('/^(.*?): (.+?)$/', $headerLine, $matches)) {
			$this->key = strtolower($matches[1]);
			////////// TODO WRAP THIS SEPARATE
			if ($this->isUtf8($matches[2])) {
				if (preg_match('/(^|)=\?.*?\?Q|q\?/', $matches[2], $_m)) {
					$this->charset = $this->clearCharset($_m[0]);
					$this->value = quoted_printable_decode(
						preg_replace('/(^|)=\?.*?\?.\?|\?=/', '', $matches[2])
					);
				} elseif (preg_match('/(^|)=\?.*?\?B|b\?/', $matches[2], $_m)) {
					$this->charset = $this->clearCharset($_m[0]);
					$this->value = iconv_mime_decode($matches[2], 0, "UTF-8");
				} else {
					$this->value = imap_utf8($matches[2]);
				}
			} else {
				if (preg_match('/(^|)=\?.*?\?.\?/', $matches[2], $out)) {
					$this->charset = $this->clearCharset($out[0]);
					$this->value = iconv_mime_decode($matches[2], 0, "UTF-8");
				} else {
					// strings without charset
					$this->charset = mb_detect_encoding($matches[2]);
					$val = @iconv($this->charset, 'UTF-8', $matches[2]);
					$this->value = is_string($val) ? $val : $matches[2];
				}
			}
			/////////////////// IN METHOD WHICH DETECT DECODING
		} elseif (preg_match('/^(.+?): \r\n/', $headerLine, $matches)) {
			// if comes string aka "Subject: \r\n" - no subject
			$this->key = strtolower($matches[1]);
			$this->value = null;
		} else {
			preg_match('/^(.+?):$/', $headerLine, $matches);
			$this->key = $matches[1];
			$this->value = null;
			$this->charset = null;
		}
	}

	public function clearCharset(string $text)
	{
		return str_replace(['=', '?Q', '?B', '?'], '', strtoupper($text));
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
		return $this->key ?? '';
	}

	/**
	 * @return ?string
	 */
	public function getValue(): ?string
	{
		return $this->value;
	}

	/**
	 * @return string|null
	 */
	public function getCharset(): ?string
	{
		return $this->charset;
	}
}
