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
			$newLine = '';
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
					} elseif (!is_null($prevLine)) {
						if ((str_starts_with($line, "\t") || str_starts_with($line, " ")) && !str_contains($line, "\tboundary")) {
							$line = trim($line, "\t\r\n ");
							$newLine .= trim($line, "\t\r\n ");
							$append = false;
						} else {
							if (!empty($newLine)) {
								$newLine = rtrim($prevLine, "\r\n") . "$newLine\r\n";
								if ($this->isUtf8($newLine)) {
									$newLine = $this->clearJoinedStringFromCharset($newLine);
								}
								$lines[array_key_last($lines)] = new Header($newLine);
								$newLine = '';
								$append = false;
							}
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
