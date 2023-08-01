<?php

namespace Sazanof\PhpImapSockets\Parts;

use Sazanof\PhpImapSockets\Models\BodyStructure;

class BodyStructurePart
{
	protected string $text;
	protected string $type;
	protected string $charset;

	protected TextPart|AttachmentPart $part;

	public function __construct(string $type, string $text)
	{
		$this->text = $text;
		$this->type = $type;
		switch ($this->type) {
			case BodyStructure::TYPE_TEXT:
				$this->part = new TextPart($type, $this->text);
				break;
			case BodyStructure::TYPE_IMAGE:
			case BodyStructure::TYPE_VIDEO:
			case BodyStructure::TYPE_APPLICATION:
				$this->part = new AttachmentPart($type, $this->text);
				break;
		}
	}


}
