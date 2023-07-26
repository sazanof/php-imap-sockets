<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Commands;

class ExamineCommand extends Command
{
	protected string $name = 'EXAMINE';

	public function __construct(string $root)
	{

		$root = '"' . $root . '"';
		$this->setArguments(imap_utf8_to_mutf7($root));
	}
}
