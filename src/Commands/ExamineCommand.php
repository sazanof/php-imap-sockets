<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Commands;

use Sazanof\PhpImapSockets\Traits\PrepareArgument;

class ExamineCommand extends Command
{
	use PrepareArgument;

	protected string $name = 'EXAMINE';

	public function __construct(string $root)
	{

		$this->setArguments($this->addQuotes($root));
	}
}
