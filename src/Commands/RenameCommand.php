<?php

namespace Sazanof\PhpImapSockets\Commands;

use Sazanof\PhpImapSockets\Traits\PrepareArgument;

class RenameCommand extends Command
{
	protected string $name = 'RENAME';

	use PrepareArgument;

	public function __construct(string $currentName, string $newName)
	{
		$this->setArguments($this->addQuotes($currentName) . ' ' . $this->addQuotes($newName));
	}
}
