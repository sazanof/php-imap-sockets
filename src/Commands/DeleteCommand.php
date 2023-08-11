<?php

namespace Sazanof\PhpImapSockets\Commands;

use Sazanof\PhpImapSockets\Traits\PrepareArgument;

class DeleteCommand extends Command
{
	use PrepareArgument;

	protected string $name = 'DELETE';

	public function __construct(string $pathOrName)
	{

		$this->setArguments($this->addQuotes($pathOrName));
	}
}
