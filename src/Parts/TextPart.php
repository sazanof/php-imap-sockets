<?php

namespace Sazanof\PhpImapSockets\Parts;

class TextPart extends BasePart
{
	protected string $charset;
	protected string $encoding;

	public function __construct(array $matches, string $section)
	{
		parent::__construct($matches, $section);
		$this->type = 'text';//TODO bug in regexp
		$this->mimeType = "text/$matches[1]"; //TODO bug in regexp
		$this->subtype = $matches[1];//TODO bug in regexp
		$this->encoding = $matches[5];
		$this->detectCharset($matches[2]);
	}

	/**
	 * @return string
	 */
	public function getEncoding(): string
	{
		return $this->encoding;
	}

	public function detectCharset(string $charset)
	{
		if (preg_match('/"charset" "(.+?)"/', $charset, $matches)) {
			$this->charset = strtoupper($matches[1]);
		}
	}
}
