<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Traits;

use Sazanof\PhpImapSockets\Response\Response;

trait FromResponse
{
	public function getLines(Response $response)
	{
		$lines = [];
		if ($response->isOk()) {
			foreach ($response->lines() as $line) {
				if ($response->lastLine() !== $line) {
					$lines[] = $line;
				}
			}
			return $lines;
		}
		// throw?
	}
}
