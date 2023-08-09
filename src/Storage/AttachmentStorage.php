<?php

namespace Sazanof\PhpImapSockets\Storage;

use Sazanof\PhpImapSockets\Parts\AttachmentPart;

class AttachmentStorage extends Storage
{

	public function saveFile()
	{
		$args = func_get_args();
		if ($args[0] instanceof AttachmentPart) {
			$att = $args[0];

			$body = imap_utf8($att->getContent());
			$this->createFile($att->getFileName(), $body);
		}
	}
}
