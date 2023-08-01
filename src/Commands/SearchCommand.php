<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Commands;

use Sazanof\PhpImapSockets\Query\SearchQuery;

class SearchCommand extends Command
{
	protected string $name = 'SEARCH';

	public function __construct(SearchQuery $query)
	{
		$this->setArguments($query->toQueryString());
	}
}
