<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Models;

use Sazanof\PhpImapSockets\Collections\Collection;
use Sazanof\PhpImapSockets\Collections\MailboxCollection;
use Sazanof\PhpImapSockets\Commands\ExamineCommand;
use Sazanof\PhpImapSockets\Connection;
use Sazanof\PhpImapSockets\Response;

class Mailbox
{
	protected Response $response;
	protected ?Connection $connection = null;
	protected Collection $attributes;
	protected string $name;
	protected string $delimiter;
	protected string $path;
	protected bool $hasChildren = false;
	protected bool $isTrash = false;
	protected bool $isDrafts = false;
	protected bool $isSent = false;
	protected bool $isJunk = false;
	protected bool $isArchive = false;
	protected ?MailboxCollection $children = null;

	const SPECIAL_ATTRIBUTES = [
		'haschildren' => ['\haschildren'],
		'hasnochildren' => ['\hasnochildren'],
		'template' => ['\template', '\templates'],
		'inbox' => ['\inbox'],
		'sent' => ['\sent'],
		'drafts' => ['\draft', '\drafts'],
		'archive' => ['\archive', '\archives'],
		'trash' => ['\trash'],
		'junk' => ['\junk', '\spam'],
	];

	public function __construct(string $line)
	{
		$line = trim($line);
		$this->attributes = new Collection();
		$this->parseLine($line);
		$this->hasChildren = $this->hasChildren();
		$this->isArchive = $this->isArchive();
		$this->isDrafts = $this->isDrafts();
		$this->isJunk = $this->isJunk();
		$this->isSent = $this->isSent();
		$this->isTrash = $this->isTrash();
	}

	public function getChildren()
	{
		return $this->children;
	}

	/**
	 * @param MailboxCollection|null $children
	 */
	public function setChildren(?MailboxCollection $children): void
	{
		$this->children = $children;
	}

	public function parseAttributes(string $attributesString)
	{
		foreach (explode(' ', $attributesString) as $attribute) {
			$this->attributes->add(strtolower($attribute));
		}
	}

	public function parseLine(string $line)
	{
		preg_match('/\((.*)\) "(.)" (.+)/', $line, $matches);
		$this->parseAttributes($matches[1]);
		$this->setDelimiter($matches[2]);
		$path = $this->decodeName($matches[3]);
		$path = rtrim($path, '"');
		$path = ltrim($path, '"');
		$this->setPath($path);
		$pathArray = explode($this->getDelimiter(), $this->getPath());
		$this->setName(!empty($pathArray) ? end($pathArray) : $this->getPath());
	}

	public function hasChildren()
	{
		return !empty(array_intersect(self::SPECIAL_ATTRIBUTES['haschildren'], $this->attributes->toArray()));
	}

	public function isTrash()
	{
		return !empty(array_intersect(self::SPECIAL_ATTRIBUTES['trash'], $this->attributes->toArray()));
	}

	public function isSent()
	{

		return !empty(array_intersect(self::SPECIAL_ATTRIBUTES['sent'], $this->attributes->toArray()));
	}

	public function isDrafts()
	{
		return !empty(array_intersect(self::SPECIAL_ATTRIBUTES['drafts'], $this->attributes->toArray()));
	}

	public function isJunk()
	{
		return !empty(array_intersect(self::SPECIAL_ATTRIBUTES['junk'], $this->attributes->toArray()));
	}

	public function isArchive()
	{
		return !empty(array_intersect(self::SPECIAL_ATTRIBUTES['archive'], $this->attributes->toArray()));
	}

	public function decodeName(string $name)
	{
		return imap_mutf7_to_utf8($name);
	}

	public function examine()
	{
		$this->getConnection()->enableDebug()->command(ExamineCommand::class, [$this->getPath()]);
	}

	/**
	 * @param Collection $attributes
	 */
	public function setAttributes(Collection $attributes): void
	{
		$this->attributes = $attributes;
	}

	/**
	 * @param string $name
	 */
	public function setName(string $name): void
	{
		$this->name = $name;
	}

	/**
	 * @param string $path
	 */
	public function setPath(string $path): void
	{
		$this->path = $path;
	}

	/**
	 * @param string $delimiter
	 */
	public function setDelimiter(string $delimiter): void
	{
		$this->delimiter = $delimiter;
	}

	/**
	 * @param Connection $connection
	 * @return Mailbox
	 */
	public function setConnection(Connection $connection): static
	{
		$this->connection = $connection;
		return $this;
	}

	/**
	 * @param Response $response
	 */
	public function setResponse(Response $response): void
	{
		$this->response = $response;
	}

	/**
	 * @return Collection
	 */
	public function getAttributes(): Collection
	{
		return $this->attributes;
	}

	/**
	 * @return string
	 */
	public function getPath(): string
	{
		return $this->path;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getDelimiter(): string
	{
		return $this->delimiter;
	}

	/**
	 * @return Connection
	 */
	public function getConnection(): Connection
	{
		return $this->connection;
	}

	/**
	 * @return Response
	 */
	public function getResponse(): Response
	{
		return $this->response;
	}
}
