<?php

namespace Sazanof\PhpImapSockets\Models;

class Address
{
	const NONE_EXISTENT = 'NONE_EXISTENT_DOMAIN';
	protected ?string $name;
	protected string $email;
	protected string $user;
	protected string $host;
	protected string $rfc822String;

	public function __construct(\stdClass|string $rfc822 = null)
	{
		if (!is_null($rfc822)) {
			$this->setName($rfc822->personal ?? null);
			$this->setUser($rfc822->mailbox);
			$this->setHost($rfc822->host);
			$this->setEmail($this->getUser() . '@' . $this->getHost());
			$this->toRfc822String();
		}
	}

	public static function firstFromString(string $string)
	{
		$address = new self();
		$addresses = $address->parseString($string);
		if (!is_null($addresses)) {
			$_address = $addresses[0]->host !== self::NONE_EXISTENT ? $addresses[0] : null; // TODo throw Exception better???
			$address->setHost($_address->host);
			$address->setUser($_address->mailbox);
			$address->setName($_address->personal ?? null);
			$address->setEmail($address->getUser() . '@' . $address->getHost());
			$address->toRfc822String();
		}
		return $address;
	}

	public function parseString(string $string = null)
	{
		$addresses = imap_rfc822_parse_adrlist($string, 'NONE_EXISTENT');
		if (!empty($addresses)) {
			return $addresses;
		} else {
			return null;
		}
	}

	/**
	 * @param string $host
	 */
	public function setHost(string $host): void
	{
		$this->host = $host;
	}

	/**
	 * @param string $user
	 */
	public function setUser(string $user): void
	{
		$this->user = $user;
	}

	/**
	 * @return string
	 */
	public function getHost(): string
	{
		return $this->host;
	}

	/**
	 * @return string
	 */
	public function getUser(): string
	{
		return $this->user;
	}

	/**
	 * @param ?string $name
	 */
	public function setName(?string $name): void
	{
		$this->name = $name;
	}

	/**
	 * @param string $email
	 */
	public function setEmail(string $email): void
	{
		$this->email = $email;
	}

	/**
	 * @return ?string
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getEmail(): string
	{
		return $this->email;
	}

	public function toRfc822String()
	{
		$this->setRfc822String(imap_rfc822_write_address(
			mailbox: $this->getUser(), hostname: $this->getHost(), personal: $this->getName() ?? ''
		));
	}

	/**
	 * @return string
	 */
	public function getRfc822String(): string
	{
		return $this->rfc822String;
	}

	/**
	 * @param string $rfc822String
	 */
	public function setRfc822String(string $rfc822String): void
	{
		$this->rfc822String = $rfc822String;
	}
}
