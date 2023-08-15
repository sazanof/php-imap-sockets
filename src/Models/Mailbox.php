<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Models;

use ReflectionException;
use Sazanof\PhpImapSockets\Collections\BodyStructureCollection;
use Sazanof\PhpImapSockets\Collections\Collection;
use Sazanof\PhpImapSockets\Collections\MailboxCollection;
use Sazanof\PhpImapSockets\Commands\ExamineCommand;
use Sazanof\PhpImapSockets\Commands\FetchCommand;
use Sazanof\PhpImapSockets\Commands\SearchCommand;
use Sazanof\PhpImapSockets\Connection;
use Sazanof\PhpImapSockets\Exceptions\MailboxOperationException;
use Sazanof\PhpImapSockets\Exceptions\NoResultsException;
use Sazanof\PhpImapSockets\Query\FetchQuery;
use Sazanof\PhpImapSockets\Query\SearchQuery;
use Sazanof\PhpImapSockets\Response\ExamineResponse;
use Sazanof\PhpImapSockets\Response\Response;
use Sazanof\PhpImapSockets\Response\SearchResponse;
use Sazanof\PhpImapSockets\Traits\PrepareArgument;

class Mailbox
{
	use PrepareArgument;

	protected Response $response;
	protected ?Connection $connection = null;
	protected Collection $attributes;
	protected string $name;
	protected string $delimiter;
	protected string $path;
	protected string $originalPath;
	protected bool $hasChildren = false;
	protected bool $isTrash = false;
	protected bool $isDrafts = false;
	protected bool $isSent = false;
	protected bool $isJunk = false;
	protected bool $isArchive = false;
	protected ?MailboxCollection $children = null;
	protected int $exists = 0;
	protected int $recent = 0;
	protected int $uidnext = 0;
	protected int $uidvalidity = 0;
	protected int $unseen = 0;

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
		$this->setOriginalPath(trim($matches[3], '"'));
		$pathArray = explode($this->getDelimiter(), $this->getPath());
		$this->setName(!empty($pathArray) ? end($pathArray) : $this->getPath());
	}

	public function examine()
	{
		$response = $this->getConnection()->examine($this->getOriginalPath());
		$this->uidvalidity = $response->uidvalidiry;
		$this->uidnext = $response->uidnext;
		$this->unseen = $response->unseen;
		$this->recent = $response->recent;
		$this->exists = $response->exists;

		return $this;
	}

	public function select()
	{
		$this->connection->select($this->getOriginalPath());
		return $this;
	}

	/**
	 * @param string $folder
	 * @param bool $insideCurrent
	 * @return bool
	 * @throws MailboxOperationException
	 * @throws ReflectionException
	 */
	public function create(string $folder, bool $insideCurrent = false): bool
	{
		$name = $insideCurrent ? $this->getPath() . $this->getDelimiter() . $folder : $folder;
		return $this->getConnection()->createMailbox($name);
	}

	/**
	 * @param string $folder
	 * @param bool $insideCurrent
	 * @return bool
	 * @throws ReflectionException
	 * @throws MailboxOperationException
	 */
	public function delete(string $folder, bool $insideCurrent = false): bool
	{
		$name = $insideCurrent ? $this->getOriginalPath() . $this->getDelimiter() . $folder : $folder;
		return $this->getConnection()->deleteMailbox($name);
	}

	/**
	 * @param string $currentName
	 * @param string $newName
	 * @return bool
	 * @throws MailboxOperationException
	 * @throws ReflectionException
	 */
	public function rename(string $currentName, string $newName): bool
	{
		return $this->getConnection()->renameMailbox($currentName, $newName);
	}

	/**
	 * @param string|null $name
	 * @return bool
	 * @throws MailboxOperationException
	 * @throws ReflectionException
	 */
	public function subscribe(string $name = null): bool
	{
		$name = is_null($name) ? $this->getOriginalPath() : $name;
		return $this->getConnection()->subscribeMailbox($name);
	}

	public function unsubscribe(string $name = null): bool
	{
		$name = is_null($name) ? $this->getOriginalPath() : $name;
		return $this->getConnection()->unsubscribeMailbox($name);
	}

	/**
	 * @return bool
	 * @throws ReflectionException
	 */
	public function close(): bool
	{
		return $this->getConnection()->closeMailbox();
	}

	/**
	 * @return bool
	 * @throws ReflectionException
	 */
	public function expunge(): bool
	{
		return $this->getConnection()->expungeMailbox();
	}

	/**
	 * @param array $msgNums
	 * @param array $flags
	 * @param bool $append
	 * @param bool $silent
	 * @return Response
	 * @throws ReflectionException
	 */
	public function store(array $msgNums, array $flags, bool $append = false, bool $silent = false): Response
	{
		return $this->getConnection()->store($msgNums, $flags, $append, $silent);
	}

	/**
	 * @param SearchQuery $query
	 * @return SearchResponse
	 * @throws ReflectionException
	 * @throws NoResultsException
	 */
	public function search(SearchQuery $query): SearchResponse
	{
		return new SearchResponse(
			$this->connection->command(SearchCommand::class, [$query])
		);
	}

	/**
	 * @param array $nums
	 * @param FetchQuery $query
	 * @return Response
	 * @throws ReflectionException
	 */
	public function fetch(array $nums, FetchQuery $query): Response
	{
		return $this->connection->command(FetchCommand::class, [$nums, $query]);
	}

	/**
	 * @param array $nums
	 * @return BodyStructureCollection
	 * @throws ReflectionException
	 */
	public function getBodyStructure(array $nums): BodyStructureCollection
	{
		$query = new FetchQuery();
		return $this->connection->command(FetchCommand::class, [$nums, $query->bodystructure()])->asCollection(BodyStructureCollection::class);
	}

	/**
	 * @return bool
	 */
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

	/**
	 * @return int
	 */
	public function getUnseen(): int
	{
		return $this->unseen;
	}

	/**
	 * @return int
	 */
	public function getExists(): int
	{
		return $this->exists;
	}

	/**
	 * @return int
	 */
	public function getRecent(): int
	{
		return $this->recent;
	}

	/**
	 * @return int
	 */
	public function getUidnext(): int
	{
		return $this->uidnext;
	}

	/**
	 * @return int
	 */
	public function getUidvalidity(): int
	{
		return $this->uidvalidity;
	}

	/**
	 * @return string
	 */
	public function getOriginalPath(): string
	{
		return $this->originalPath;
	}

	/**
	 * @param string $originalPath
	 */
	public function setOriginalPath(string $originalPath): void
	{
		$this->originalPath = $originalPath;
	}
}
