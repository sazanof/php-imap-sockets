<?php

namespace Sazanof\PhpImapSockets\Response;

class SearchResponse
{
	protected array $nums = [];

	/**
	 * @param Response $response
	 */
	public function __construct(Response $response)
	{
		if ($response->isOk()) {
			$last = $response->line(count($response->lines()) - 2);
			if (preg_match('/\* SEARCH (.+)\r\n/', $last, $matches)) {
				$this->nums = array_map('intval', explode(' ', $matches[1]));
			}
		}
		return $this;
	}

	public function msgNums()
	{
		return $this->nums;
	}
}
