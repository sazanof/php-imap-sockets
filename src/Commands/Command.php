<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Commands;

class Command
{
	/**
	 * @var string
	 */
	protected string $name = '';

	/**
	 * @var string
	 */
	protected string $arguments = '';


	/**
	 * @param string $arguments
	 * @return Command
	 */
	public function setArguments(string $arguments): static
	{
		$this->arguments = $arguments;
		return $this;
	}

	/**
	 * @param string $name
	 * @return Command
	 */
	public function setName(string $name): static
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Create a command string
	 * @return string
	 */
	protected static function create(array $args = null)
	{
		$instance = new static(...$args);
		return $instance->arguments === '' ? $instance->name : $instance->name . ' ' . $instance->arguments;
	}


}
