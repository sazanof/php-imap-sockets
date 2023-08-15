<?php

namespace Sazanof\PhpImapSockets\Response;

class BodyResponse
{
	protected string $content = '';

	public function __construct($response)
	{
		if ($response->isOk()) {
			foreach ($response->lines() as $line) {
				if ($line !== ")\r\n" && $line !== $response->lastLine() && $line !== $response->line(0)) {
					$this->content .= trim($line, ' ');
				}
			}
		}
	}

	/**
	 * @return string
	 */
	public function getContent(): string
	{
		return $this->content;
	}
}
