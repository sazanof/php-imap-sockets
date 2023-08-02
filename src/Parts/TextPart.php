<?php

namespace Sazanof\PhpImapSockets\Parts;

use Sazanof\PhpImapSockets\Models\BodyStructureOld;

class TextPart extends BasePart
{
	protected string $charset;

	public function __construct(array $matches)
	{
		parent::__construct($matches);
		$this->type = 'text';//TODO bug in regexp
		$this->mimeType = "text/$matches[1]"; //TODO bug in regexp
		$this->subtype = $matches[1];//TODO bug in regexp
		$this->detectCharset();
	}

	public function detectCharset()
	{
		if (preg_match('/"charset" "(.+?)"/', $this->matches[2], $matches)) {
			$this->charset = strtoupper($matches[1]);
		}
	}
}
