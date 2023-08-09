<?php

namespace Sazanof\PhpImapSockets\Response;

use Sazanof\PhpImapSockets\Exceptions\NoResultsException;
use Sazanof\PhpImapSockets\Exceptions\StorageException;

class SearchResponse
{
	protected array $nums = [];

	/**
	 * @param Response $response
	 * @throws NoResultsException
	 */
	public function __construct(Response $response)
	{
		if ($response->isOk()) {
			$last = $response->line(count($response->lines()) - 2);
			if (preg_match('/\* SEARCH (.+)\r\n/', $last, $matches)) {
				$this->nums = array_map('intval', explode(' ', $matches[1]));
			} else {
				throw new NoResultsException();
			}
		}
		return $this;
	}

	public function setOrderDirection($orderDirection = 'DESC')
	{
		strtoupper($orderDirection) === 'DESC' ? arsort($this->nums) : asort($this->nums);
		$this->nums = array_values($this->nums);
		return $this;
	}

	public function msgNums()
	{
		return $this->nums;
	}
}
