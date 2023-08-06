<?php

namespace Sazanof\PhpImapSockets\Collections;

use Sazanof\PhpImapSockets\Models\Header;
use Sazanof\PhpImapSockets\Response\Response;

class HeadersCollection extends Collection
{
	protected array $headers = [];
	protected ?string $boundary = null;

	public function __construct(Response $response)
	{
		parent::__construct();
		$lines = [];
		$num = 0;
		if ($response->isOk()) {
			$i = 0;
			$lastIndex = 0;
			$responseLines = $response->lines();
			foreach ($responseLines as $line) {
				if (preg_match('/\* (\d+) FETCH /', $line, $matches)) {
					$num = (int)$matches[1];
					$this->headers[$num] = [];
				}
				if ($line !== $response->lastLine() && $line !== "\r\n" && $line !== ")\r\n") {

					if (preg_match('/^(.*?): (.*?)\r\n/', $line, $matches)) {
						$lines[$i] = trim($line);
						$lastIndex = $i;
					} else {
						if (array_key_exists($lastIndex, $lines)) {
							$lines[$lastIndex] .= trim($line);
						}
					}
				}
				$i++;
			}
			foreach ($lines as $header) {
				$this->headers[$num][] = new Header($header);
			}
		}
	}

	public function isUtf8(string $string)
	{
		return str_contains(strtoupper($string), '=?UTF-8');
	}

	public function clearJoinedStringFromCharset(string $text): string
	{
		$string = preg_replace('/\?=(=\?.*?\?.\?)(.*?)/i', '', $text);

		return $string;
	}

	public function getBoundary($line)
	{
		preg_match("/\tboundary=\"(.+?)\"/", $line, $matches);
		return $matches[1];
	}

	/**
	 * @return array
	 */
	public function getHeaders(): array
	{
		return $this->headers;
	}
}
