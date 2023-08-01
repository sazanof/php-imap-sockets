<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Commands;

use Sazanof\PhpImapSockets\Query\FetchQuery;

class FetchCommand extends Command
{
	protected string $name = 'FETCH';

	public function __construct(array $uids, FetchQuery $query)
	{
		$this->setArguments(implode(',', $uids) . ' (' . $query->toQueryString() . ')');
	}
}
