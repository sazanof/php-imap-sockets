<?php

namespace Sazanof\PhpImapSockets\Commands;

use Sazanof\PhpImapSockets\Traits\PrepareArgument;

class SubscribeCommand extends Command
{
	protected string $name = 'SUBSCRIBE';

	public function __construct(string $name)
	{
		$this->setArguments($name);
	}
}
