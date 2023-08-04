<?php

namespace Sazanof\PhpImapSockets\Collections;

use Sazanof\PhpImapSockets\Response\Response;

class FlagsCollection
{
	protected array $flagArray;

	public function __construct(Response $response)
	{
		if ($response->isOk()) {
			foreach ($response->lines() as $line) {
				if ($line !== $response->lastLine()) {
					if (preg_match('/\* (\d+) FETCH \(FLAGS \((.*?)\)\)/', $line, $m)) {
						$this->flagArray[(int)$m[1]] = explode(' ', strtolower($m[2]));
					}
				}
			}
		}
	}

	/**
	 * @param int $msgNum
	 * @return mixed|null
	 */
	public function get(int $msgNum)
	{
		if (array_key_exists($msgNum, $this->flagArray)) {
			return $this->flagArray[$msgNum];
		}
		return null;
	}
}
