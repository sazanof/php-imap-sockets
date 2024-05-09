<?php

namespace Sazanof\PhpImapSockets\Models;

class Header
{
	protected string $key;
	protected ?string $value;
	protected ?string $charset = null;

	public function __construct(string $headerLine)
	{
		if (preg_match('/^(.*?):(.+?)$/', $headerLine, $matches)) {
			$this->key = strtolower(ltrim($matches[1]));
			////////// TODO WRAP THIS SEPARATE
			if ($this->isUtf8($matches[2])) {
				if (preg_match('/(^|)=\?.*?\?Q|q\?/', $matches[2], $_m)) {
					$this->charset = $this->clearCharset($_m[0]);
					/*$this->value = quoted_printable_decode(
						preg_replace('/(^|)=\?.*?\?.\?|\?=/', '', $matches[2])
					);*/
					$this->value = $this->imapMimeHeaderDecode($matches[2]);
					if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
						$this->value = str_replace('_', ' ', $this->value);
					}
				} elseif (preg_match('/(^|)=\?.*?\?B|b\?/', $matches[2], $_m)) {
					$this->charset = $this->clearCharset($_m[0]);
					$this->value = $this->imapMimeHeaderDecode($matches[2]);
				} else {
					$this->value = imap_utf8($matches[2]);
				}
			} else {
				if (preg_match('/(^|)=\?.*?\?.\?/', $matches[2], $out)) {
					$this->charset = $this->clearCharset($out[0]);
					$this->value = $this->imapMimeHeaderDecode($matches[2]);
				} else {
					// strings without charset
					$this->charset = mb_detect_encoding($matches[2]);
					$val = @iconv($this->charset, 'UTF-8', $matches[2]);
					$this->value = is_string($val) ? $val : $matches[2];
				}
			}
			$this->value = trim($this->value, ' ');
			/////////////////// IN METHOD WHICH DETECT DECODING
		} elseif (preg_match('/^(.+?): \r\n/', $headerLine, $matches)) {
			// if comes string aka "Subject: \r\n" - no subject
			$this->key = strtolower($matches[1]);
			$this->value = null;
		} else {
			preg_match('/^(.+?):$/', $headerLine, $matches);
			if (is_null($matches[1])) {
				dd($headerLine);
			}
			$this->key = $matches[1];
			$this->value = null;
			$this->charset = null;
		}
	}

	public function imapMimeHeaderDecode(string $header)
	{
		$header = imap_mime_header_decode($header);
		$split = '';
		foreach ($header as $h) {
			$split .= $h->text;
		}
		return $split;
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
