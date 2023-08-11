<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets;

use Exception;
use ReflectionException;
use ReflectionMethod;
use Sazanof\PhpImapSockets\Collections\MailboxCollection;
use Sazanof\PhpImapSockets\Commands\Command;
use Sazanof\PhpImapSockets\Commands\CreateCommand;
use Sazanof\PhpImapSockets\Commands\DeleteCommand;
use Sazanof\PhpImapSockets\Commands\ExamineCommand;
use Sazanof\PhpImapSockets\Commands\ListCommand;
use Sazanof\PhpImapSockets\Commands\LoginCommand;
use Sazanof\PhpImapSockets\Commands\LogoutCommand;
use Sazanof\PhpImapSockets\Commands\LsubCommand;
use Sazanof\PhpImapSockets\Commands\NoopCommand;
use Sazanof\PhpImapSockets\Commands\RenameCommand;
use Sazanof\PhpImapSockets\Commands\SelectCommand;
use Sazanof\PhpImapSockets\Commands\SubscribeCommand;
use Sazanof\PhpImapSockets\Commands\UnsubscribeCommand;
use Sazanof\PhpImapSockets\Exceptions\ConnectionException;
use Sazanof\PhpImapSockets\Exceptions\MailboxOperationException;
use Sazanof\PhpImapSockets\Exceptions\LoginFailedException;
use Sazanof\PhpImapSockets\Models\Mailbox;
use Sazanof\PhpImapSockets\Response\ExamineResponse;
use Sazanof\PhpImapSockets\Response\Response;
use Sazanof\PhpImapSockets\Response\SelectResponse;
use Sazanof\PhpImapSockets\Socket;

/**
 * @see INTERNET MESSAGE ACCESS PROTOCOL - VERSION 4rev1 <https://tools.ietf.org/html/rfc3501#page-24>
 */
class Connection
{
	const SSL_PORT = 993;

	/**
	 * Server host name.
	 *
	 * @var string
	 */
	protected $host;

	/**
	 * Server port.
	 *
	 * @var int
	 */
	protected $port;

	/**
	 * Connection socket.
	 *
	 * @var null|Socket
	 */
	protected $socket;

	/**
	 * SSL options for socket.
	 *
	 * @var array
	 */
	protected $sslOptions = [];

	/**
	 * IMAP command tag.
	 *
	 * It is a unique incremental identifier for each IMAP command sent to the server.
	 *
	 * @var int
	 */
	protected $commandTag = 1;

	/**
	 * Response received from last command sent.
	 *
	 * @var Response
	 */
	protected $lastResponse;

	protected bool $debug = false;

	/**
	 * Make a new Connection instance.
	 *
	 * @param string $host
	 * @param int $port
	 */
	public function __construct(string $host, int $port = 993)
	{
		$this->host = $host;
		$this->port = $port;
	}

	/**
	 * Get a new instance and open it.
	 *
	 * @return self
	 */
	public static function create(): self
	{
		return (new static(...func_get_args()))->open();
	}

	/**
	 * Open the connection.
	 *
	 * @return self
	 * @throws ConnectionException if connection fails
	 */
	public function open(): self
	{
		if ($this->socket) {
			return $this;
		}

		try {
			$this->createSocket()->open();
		} catch (Exception $e) {
			throw new ConnectionException('Invalid host');
		}

		return $this;
	}

	public function enableDebug()
	{
		$this->debug = true;
		return $this;
	}

	public function disableDebug()
	{
		$this->debug = false;
		return $this;
	}

	/**
	 * Create a new socket.
	 *
	 * @return Socket
	 * @throws ConnectionException
	 */
	public function createSocket()
	{
		$this->socket = new Socket($this->host, $this->port);

		if ($this->port == static::SSL_PORT) {
			$this->socket->tls($this->sslOptions);
		}

		return $this->socket;
	}

	/**
	 * Set the connection socket.
	 *
	 * @param Socket $socket
	 * @return self
	 */
	public function withSocket(Socket $socket): self
	{
		$this->socket = $socket;

		return $this;
	}

	/**
	 * Do not verify the SSL certificate.
	 *
	 * @return self
	 */
	public function doNotVerifySslCert(): self
	{
		$this->sslOptions['verify_peer'] = false;
		$this->sslOptions['verify_peer_name'] = false;

		return $this;
	}

	/**
	 * Get connection socket.
	 *
	 * @return Socket
	 */
	public function getSocket(): Socket
	{
		return $this->socket;
	}

	/**
	 * Close the socket connection to the IMAP server.
	 *
	 * @return void
	 */
	public function close(): void
	{
		if ($this->socket) {
			$this->socket->close();
			$this->socket = null;
		}
	}

	/**
	 * Is the connection open?
	 *
	 * @return bool
	 */
	public function isOpen(): bool
	{
		return !is_null($this->socket);
	}

	/**
	 * Is the connection closed?
	 *
	 * @return bool
	 */
	public function isClosed(): bool
	{
		return !$this->isOpen();
	}

	public function __destruct()
	{
		$this->close();
	}

	/**
	 * Send an IMAP command to the server.
	 *
	 * @param string $commandClassname
	 * @param array $arguments
	 * @return Response
	 * @throws ReflectionException
	 */
	public function command(string $commandClassname, array $arguments = []): Response
	{
		$method = new ReflectionMethod($commandClassname, 'create');
		$commandString = $method->invoke(null, $arguments);
		if ($this->debug) {
			echo 'COMMAND STRING IS: ' . $commandString . PHP_EOL;
		}
		$tag = sprintf('%08d', $this->commandTag);
		$imapCommand = "{$tag} {$commandString}\r\n";
		$lines = [];

		$this->socket->write($imapCommand);

		while ($line = $this->socket->read()) {
			$lines[] = $line;
			if (str_starts_with($line, $tag)) {
				break;
			}
		}

		$this->lastResponse = new Response($tag, $lines);
		$this->commandTag++;

		if ($this->debug) {
			foreach ($lines as $line) {
				echo $line . PHP_EOL;
			}
		}

		return $this->lastResponse;
	}

	/**
	 * Get the response received from the last command sent.
	 *
	 * @return Response
	 */
	public function lastResponse(): Response
	{
		return $this->lastResponse;
	}

	/**
	 * Login using credentials.
	 *
	 * @param string $login
	 * @param string $password
	 * @return self
	 * @throws LoginFailedException
	 */
	public function login(string $login, string $password): self
	{
		$response = $this->command(LoginCommand::class, [$login, $password]);

		if ($response->isNotOk()) {
			throw new LoginFailedException($response);
		}

		return $this;
	}

	/**
	 * Logout and close the connection.
	 *
	 * @return void
	 */
	public function logout(): void
	{
		$this->command(LogoutCommand::class);
		$this->close();
	}

	/**
	 * List mailboxes with given criteria
	 * @param string $root
	 * @param string $searchQuery
	 * @return MailboxCollection
	 * @throws ReflectionException
	 * @throws ConnectionException
	 */
	public function listMailboxes(string $root = '', string $searchQuery = '*'): MailboxCollection
	{
		return new MailboxCollection($this->command(ListCommand::class, [$root, $searchQuery]), $this);
	}

	/**
	 * @param string $root
	 * @param string $searchQuery
	 * @return MailboxCollection
	 * @throws ConnectionException
	 * @throws ReflectionException
	 */
	public function lsubMailboxes(string $root = '', string $searchQuery = '*'): MailboxCollection
	{
		return new MailboxCollection($this->command(LsubCommand::class, [$root, $searchQuery]), $this);
	}

	/**
	 * @param string $path
	 * @return Mailbox|null
	 * @throws ReflectionException
	 */
	public function getMailboxByPath(string $path)
	{
		return $this->listMailboxes($path, '%')->first();
	}

	/**
	 * List mailboxes as tree whith children
	 * @param string $startPath
	 * @return MailboxCollection
	 * @throws ReflectionException
	 */
	public function listMailboxesTree(string $startPath = ''): MailboxCollection
	{
		$mailboxCollection = new MailboxCollection(
			$this->command(ListCommand::class, [$startPath, '%']),
			$this
		);
		$mailboxCollection->map(function ($mailbox) {
			/** @var Mailbox $mailbox */
			if ($mailbox->hasChildren()) {
				$mailbox->setChildren($this->listMailboxesTree($mailbox->getPath() . $mailbox->getDelimiter()));
			}
		});
		return $mailboxCollection;
	}

	/**
	 * @param $mailboxName
	 * @return SelectResponse|null
	 * @throws ReflectionException
	 */
	public function select($mailboxName): ?SelectResponse
	{
		$response = $this->command(SelectCommand::class, [$mailboxName]);
		return $response->isOk() ? new SelectResponse($response) : null;
	}

	/**
	 * @return bool
	 * @throws ReflectionException
	 */
	public function noop(): bool
	{
		return $this->command(NoopCommand::class)->isOk();
	}

	/**
	 * @param string $path
	 * @return ExamineResponse
	 * @throws ReflectionException
	 */
	public function examine(string $path): ExamineResponse
	{
		return $this->command(ExamineCommand::class, [$path])->asCollection(ExamineResponse::class);
	}

	/**
	 * @param string $pathOrName
	 * @return bool
	 * @throws MailboxOperationException
	 * @throws ReflectionException
	 */
	public function createMailbox(string $pathOrName): bool
	{
		$response = $this->command(CreateCommand::class, [$pathOrName]);
		if ($response->isNotOk()) {
			throw new MailboxOperationException($response->lastLine());
		}
		return $response->isOk();
	}

	/**
	 * @param string $pathOrName
	 * @return bool
	 * @throws MailboxOperationException
	 * @throws ReflectionException
	 */
	public function deleteMailbox(string $pathOrName): bool
	{
		$response = $this->command(DeleteCommand::class, [$pathOrName]);
		if ($response->isNotOk()) {
			throw new MailboxOperationException($response->lastLine());
		}
		return $response->isOk();
	}

	/**
	 * @param string $currentName
	 * @param string $newName
	 * @return bool
	 * @throws MailboxOperationException
	 * @throws ReflectionException
	 */
	public function renameMailbox(string $currentName, string $newName): bool
	{
		$response = $this->command(RenameCommand::class, [$currentName, $newName]);
		if ($response->isNotOk()) {
			throw new MailboxOperationException($response->lastLine());
		}
		return $response->isOk();
	}

	/**
	 * @param string $name
	 * @return bool
	 * @throws MailboxOperationException
	 * @throws ReflectionException
	 */
	public function subscribeMailbox(string $name): bool
	{
		$response = $this->command(SubscribeCommand::class, [$name]);
		if ($response->isNotOk()) {
			throw new MailboxOperationException($response->lastLine());
		}
		return $response->isOk();
	}

	/**
	 * @param string $name
	 * @return bool
	 * @throws MailboxOperationException
	 * @throws ReflectionException
	 */
	public function unsubscribeMailbox(string $name): bool
	{
		$response = $this->command(UnsubscribeCommand::class, [$name]);
		if ($response->isNotOk()) {
			throw new MailboxOperationException($response->lastLine());
		}
		return $response->isOk();
	}
}
