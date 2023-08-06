<?php

namespace Sazanof\PhpImapSockets\Collections;

use Sazanof\PhpImapSockets\Models\Header;

class MessageHeadersCollection extends Collection
{
	/**
	 * @param string $key
	 * @return Header|null
	 */
	public function getHeader(string $key)
	{
		$header = $this->find(function ($header) use ($key) {
			return $header->getKey() === $key;
		});
		return !empty($header) ? $header[0] : null;
	}
}
