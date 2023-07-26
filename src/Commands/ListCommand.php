<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Commands;

class ListCommand extends Command
{

	protected string $name = 'LIST';
	protected string $arguments = '*';
	protected string $root = '""';
	protected string $search = '*';

	public function __construct(string $root, string $search)
	{
		$this->root = $root;
		$this->search = $search;
		$this->setArguments($this->root . ' ' . $this->search);
	}
}
