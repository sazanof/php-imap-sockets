<?php

namespace Sazanof\PhpImapSockets\Commands;

class StoreCommand extends Command
{
	protected string $name = 'STORE';

	public const STORE_SILENT = 'SILENT';
	public const APPEND_FLAGS = '+';

	public function __construct(array $msgNumbers, array $flags, bool $append = false, bool $silent = false)
	{
		$command = 'FLAGS';
		if ($append == self::APPEND_FLAGS) {
			$command = self::APPEND_FLAGS . $command;
		}
		if ($silent) {
			$command .= '.' . self::STORE_SILENT;
		}
		$this->setArguments(implode(',', $msgNumbers) . ' ' . $command . ' (' . implode(' ', $flags) . ')');
	}
}
