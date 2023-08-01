<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Response;

use Sazanof\PhpImapSockets\Collections\MessageCollection;
use Sazanof\PhpImapSockets\Models\Message;

class Response
{
	/**
	 * Formatted IMAP command tag.
	 *
	 * @var string
	 */
	protected $tag = '';

	/**
	 * Lines returned by IMAP server in the response.
	 *
	 * @var string[]
	 */
	protected $lines = [];

	public function __construct(string $tag = '', array $lines = [])
	{
		$this->tag = $tag;
		$this->lines = $lines;
	}

	/**
	 * Split a multi-part response into an array of responses for each part.
	 *
	 * @return Response[]
	 */
	public function explode()
	{
		$responses = []; // responses to return
		$part = []; // lines in the current part

		// Read all the lines
		foreach ($this->lines as $line) {
			// Accumulate the lines for the current part
			$part[] = $line;
			// Save the response when the end of the part is reached
			if ($line == ")\r\n") {
				$responses[] = new Response($this->tag, $part);
				$part = [];
			}
		}

		return $responses;
	}

	/**
	 * Get the lines in the response.
	 *
	 * @return array
	 */
	public function lines(): array
	{
		return $this->lines;
	}

	/**
	 * Get a line from a the response at a given index.
	 *
	 * @param int $index
	 * @return string empty string when $index is out of bounds
	 */
	public function line(int $index): string
	{
		return $this->lines[$index] ?? '';
	}

	/**
	 * Get the last line in the response.
	 *
	 * @return string
	 */
	public function lastLine(): string
	{
		$lineCount = count($this->lines);

		return $lineCount > 0 ? $this->lines[$lineCount - 1] : '';
	}

	public function isOk(): bool
	{
		$ok = "{$this->tag} OK";

		return str_starts_with($this->lastLine(), $ok);
	}

	public function isNotOk(): bool
	{
		return !$this->isOk();
	}

	/**
	 * Get the error from the last IMAP response.
	 *
	 * @return string
	 */
	public function error()
	{
		return $this->lastLine();
	}

	/**
	 * @param $className
	 * @return mixed
	 * @throws \ReflectionException
	 */
	public function as($className)
	{
		$class = new \ReflectionClass($className);
		return $class->newInstance($this->line(0));
	}

	public function asCollection(string $collectionClassName)
	{
		$class = new \ReflectionClass($collectionClassName);
		return $class->newInstance($this);
	}
}
