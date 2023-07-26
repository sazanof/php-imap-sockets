<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets;

class Socket
{
	/**
	 * Underlying stream socket.
	 *
	 * @var null|resource
	 */
	protected $resource = null;

	/**
	 * Network protocol.
	 *
	 * @var string
	 */
	protected $protocol;

	/**
	 * Server name.
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
	 * Stream socket context.
	 *
	 * @var array
	 */
	protected $context = [];

	public function __construct(string $host, int $port)
	{
		$this->host = $host;
		$this->port = $port;
	}

	public static function create(string $host, int $port)
	{
		return new self($host, $port);
	}

	/**
	 * Enable SSL/TLS.
	 */
	public function tls(array $options = ['verify_peer_name' => false]): self
	{
		$this->protocol = 'tls';
		$this->context['ssl'] = $options;

		return $this;
	}

	/**
	 * Open the socket connection to the host.
	 */
	public function open(): self
	{
		$this->resource = stream_socket_client(
			$this->getAddress(),
			$errno,
			$errstr,
			$timeout = 15,
			STREAM_CLIENT_CONNECT,
			$this->getContext()
		);

		if (!$this->resource) {
			throw new ConnectionException("Could not connect to host ($errno) $errstr");
		}

		return $this;
	}

	/**
	 * Get the fully qualified host address including protocol and port number.
	 *
	 * @return string
	 */
	public function getAddress(): string
	{
		$address = $this->host . ':' . $this->port;

		if ($this->protocol) {
			$address = $this->protocol . '://' . $address;
		}

		return $address;
	}

	/**
	 * Create and return the stream socket context.
	 *
	 * @return null|resource
	 */
	protected function getContext()
	{
		return $this->context ? stream_context_create($this->context) : null;
	}

	/**
	 * Read a response from the socket.
	 *
	 * @return string
	 */
	public function read(): string
	{
		return fgets($this->resource);
	}

	/**
	 * Write a message on the socket.
	 *
	 * @param string $string
	 * @return self
	 */
	public function write(string $string): self
	{
		fwrite($this->resource, $string);

		return $this;
	}

	/**
	 * Close the socket.
	 *
	 * @return void
	 */
	public function close(): void
	{
		if ($this->resource) {
			fclose($this->resource);
			$this->resource = null;
		}
	}
}
