<?php

namespace Sazanof\PhpImapSockets\Parts;

use Sazanof\PhpImapSockets\Models\BodyStructureOld;

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
			case BodyStructureOld::TYPE_TEXT:
				$this->part = new TextPart($type, $this->text);
				break;
			case BodyStructureOld::TYPE_IMAGE:
			case BodyStructureOld::TYPE_VIDEO:
			case BodyStructureOld::TYPE_APPLICATION:
				$this->part = new AttachmentPart($type, $this->text);
				break;
		}
	}


}
