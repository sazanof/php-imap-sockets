<?php

namespace Sazanof\PhpImapSockets\Collections;


use Sazanof\PhpImapSockets\Models\BodyStructure;
use Sazanof\PhpImapSockets\Response\Response;

class BodyStructureCollection
{
	protected array $bodyStructureKeyValues;

	public function __construct(Response $response)
	{
		if ($response->isOk()) {
			foreach ($response->lines() as $item) {
				if (preg_match('/\* (\d+) FETCH/', $item, $msgMumMatch)) {
					$this->bodyStructureKeyValues[(int)$msgMumMatch[1]] = new BodyStructure($item);
				}
			}
		}
	}

	/**
	 * @param int $msgNum
	 * @return BodyStructure|null
	 */
	public function getItem(int $msgNum): ?BodyStructure
	{
		return array_key_exists($msgNum, $this->bodyStructureKeyValues) ? $this->bodyStructureKeyValues[$msgNum] : null;
	}
}
