<?php

namespace Sazanof\PhpImapSockets\Response;

class SearchResponse
{
	protected array $uids = [];

	/**
	 * @param Response $response
	 */
	public function __construct(Response $response)
	{
		if ($response->isOk()) {
			$last = $response->line(count($response->lines()) - 2);
			if (preg_match('/\* SEARCH (.+)\r\n/', $last, $matches)) {
				$this->uids = array_map('intval', explode(' ', $matches[1]));
			}
		}
		return $this;
	}

	public function uids()
	{
		return $this->uids;
	}
}
