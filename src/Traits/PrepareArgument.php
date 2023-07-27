<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Traits;

trait PrepareArgument
{
	/**
	 * @param string $argument
	 * @return string
	 */
	protected function addQuotes(string $argument): string
	{
		if ((str_contains($argument, ' ') && strlen($argument) > 0) || strlen($argument) === 0) {
			$argument = '"' . $argument . '"';
		}
		return $argument;
	}

	/**
	 * @param string $argument
	 * @return false|string
	 */
	protected function imapUtf8ToMutf7(string $argument): bool|string
	{
		return imap_utf8_to_mutf7($argument);
	}
}
