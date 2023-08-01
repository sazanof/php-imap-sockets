<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Commands;

use Sazanof\PhpImapSockets\Traits\PrepareArgument;

class ListCommand extends Command
{

	use PrepareArgument;

	protected string $name = 'LIST';
	protected string $arguments = '*';
	protected string $root = '';
	protected string $search = '*';

	public function __construct(string $root, string $search)
	{
		$this->root = $root;
		$this->search = $search;
		if (str_contains($this->root, ' ') || str_contains($this->root, '-') || strlen($this->root) === 0) {
			$this->root = '"' . $this->addQuotes($this->root) . '"';
		}
		$this->setArguments($this->root . ' ' . $this->search);
	}
}
