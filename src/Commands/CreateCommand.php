<?php

namespace Sazanof\PhpImapSockets\Commands;

use Sazanof\PhpImapSockets\Traits\PrepareArgument;

class CreateCommand extends Command
{
	protected string $name = 'CREATE';

	use PrepareArgument;

	public function __construct(string $pathOrName)
	{

		$this->setArguments($this->addQuotes($pathOrName));
	}
}
