<?php

namespace Sazanof\PhpImapSockets\Query;

use Sazanof\PhpImapSockets\Collections\Collection;
use Sazanof\PhpImapSockets\Traits\PrepareArgument;

class FetchQuery
{
	use PrepareArgument;

	public const BODY_TEXT = 'TEXT';
	public const BODY_HEADER = 'HEADER';
	public const BODY_HEADER_FIELDS = 'HEADER.FIELDS';
	public const BODY_HEADER_FIELDS_NOT = 'HEADER.FIELDS.NOT';
	public const MIME = 'MIME';
	public const RFC822_TEXT = 'RFC822.TEXT';

	protected Collection $parameters;

	public function __construct()
	{
		$this->parameters = new Collection();
	}

	public function body(string $part = null, ?array $fields = null, $leaveUnseen = true)
	{
		$body = !$leaveUnseen ? 'BODY' : 'BODY.PEEK';
		if (is_array($fields) && !empty($fields)) {
			$fields = ' (' . implode(' ', $fields) . ')';
		}
		$this->parameters->add($body . '[' . $part . $fields . ']');
		return $this;
	}

	/**
	 * @return Collection
	 */
	public function getParameters(): Collection
	{
		return $this->parameters;
	}

	public function bodystructure()
	{
		$this->parameters->add('BODYSTRUCTURE');
		return $this;
	}

	public function enveloope()
	{

	}

	public function fast()
	{

	}

	public function flags()
	{
		$this->parameters->add('FLAGS');
		return $this;
	}

	public function full()
	{

	}

	public function innerdate()
	{

	}

	public function rfc822()
	{

	}

	public function rfc822Header()
	{
		$this->parameters->add('RFC822.HEADER');
		return $this;
	}

	public function rfc822Size()
	{
	}

	public function rfc822Text()
	{
		$this->parameters->add(self::RFC822_TEXT);
		return $this;
	}

	public function uid()
	{
		$this->parameters->add('UID');
		return $this;
	}

	public function toQueryString()
	{
		return implode(' ', $this->parameters->toArray());
	}

	public function clear()
	{
		$this->parameters->empty();
		return $this;
	}
}
