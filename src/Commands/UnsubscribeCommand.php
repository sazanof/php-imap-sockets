<?php

namespace Sazanof\PhpImapSockets\Commands;

use Sazanof\PhpImapSockets\Traits\PrepareArgument;

class UnsubscribeCommand extends Command
{
	protected string $name = 'UNSUBSCRIBE';

	public function __construct(string $name)
	{
		$this->setArguments($name);
	}
}
