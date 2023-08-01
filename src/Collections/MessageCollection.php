<?php

namespace Sazanof\PhpImapSockets\Collections;

use Sazanof\PhpImapSockets\Response\Response;

class MessageCollection extends Collection
{
	private Response $response;

	public function __construct(Response $response)
	{
		$this->response = $response;
		dump($response->lines());
	}
}
