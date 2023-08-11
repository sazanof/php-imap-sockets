<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Commands;

use Sazanof\PhpImapSockets\Traits\PrepareArgument;

class SelectCommand extends Command
{
	use PrepareArgument;

	protected string $name = 'SELECT';

	public function __construct(string $folder)
	{
		$this->setArguments($folder);
	}
}
