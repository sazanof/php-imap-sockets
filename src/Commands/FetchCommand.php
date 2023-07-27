<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Commands;

class FetchCommand extends Command
{
	protected string $name = 'FETCH';

	public function __construct(array $uids)
	{
		$this->setArguments(implode(',', $uids));
	}
}
