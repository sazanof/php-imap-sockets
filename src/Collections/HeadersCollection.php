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
		$num = null;
		$prevLine = null;
		if ($response->isOk()) {
			$i = 0;
			foreach ($response->lines() as $line) {
				$append = true;
				if (preg_match('/\* (\d+) FETCH /', $line, $matches)) {
					$num = (int)$matches[1];
					$this->headers[$num] = [];
					$append = false;
				}
				if ($line !== $response->lastLine() && $line !== "\r\n" && $line !== ")\r\n") {
					if (str_starts_with($line, "\tboundary")) {
						$this->boundary = $this->getBoundary($line);
						continue;
					}
					if (!is_null($prevLine)) {
						if ((str_starts_with($line, "\t") || str_starts_with($line, " ")) && !str_contains($line, "\tboundary")) {
							$newLine = rtrim($prevLine, "\r\n") . ltrim($line, "\t");
							$lines[array_key_last($lines)] = new Header($newLine);
							$append = false;
						}
					}
					if ($append) {
						$lines[] = new Header($line);
						$prevLine = $line;
						$i++;
					}

				} elseif ($line === ")\r\n" && !is_null($num)) {
					$this->headers[$num] = $lines;
					$lines = [];
					$prevLine = null;
				}
			}
		}
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
