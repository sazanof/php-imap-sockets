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
					$i = 0;
					$lines[$num] = [];
				}
				if ($line !== $response->lastLine() && $line !== "\r\n" && $line !== ")\r\n") {

					if (preg_match('/^(.*?): (.*?)\r\n/', $line, $matches)) {
						$lines[$num][$i] = trim($line);
						$lastIndex = $i;
					} else {
						if (array_key_exists($lastIndex, $lines[$num])) {
							$lines[$num][$lastIndex] .= trim($line);
						}
					}
				}
				$i++;
			}
			foreach ($lines as $msgNum => $headersArray) {
				foreach ($headersArray as $header) {
					$this->headers[$msgNum][] = new Header($header);
				}
			}
		}
	}

	public function isUtf8(string $string)
	{
		return str_contains(strtoupper($string), '=?UTF-8');
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
