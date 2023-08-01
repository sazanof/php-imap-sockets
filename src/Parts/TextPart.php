<?php

namespace Sazanof\PhpImapSockets\Parts;

use Sazanof\PhpImapSockets\Models\BodyStructure;

class TextPart extends BasePart
{
	protected string $charset;

	public function __construct(string $type, string $text)
	{
		parent::__construct($type, $text);
		$this->detectCharset();
	}

	public function detectCharset()
	{
		if (preg_match('/"charset" "(.+?)"/', $this->text, $matches)) {
			$this->charset = strtoupper($matches[1]);
		}
	}
}
