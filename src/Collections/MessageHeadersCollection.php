<?php

namespace Sazanof\PhpImapSockets\Collections;

use Sazanof\PhpImapSockets\Models\Header;

class MessageHeadersCollection extends Collection
{
	/**
	 * @param string $key
	 * @return array|Header[]
	 */
	public function getValue(string $key)
	{
		return $this->find(function ($header) use ($key) {
			return $header->getKey() === $key;
		});
	}
}
