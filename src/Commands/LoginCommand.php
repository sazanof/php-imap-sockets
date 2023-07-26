<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Commands;

class LoginCommand extends Command
{
	protected string $name = 'LOGIN';
	protected string $username;
	protected string $password;
	protected string $arguments = '';

	public function __construct($username, $password)
	{
		$this->username = $username;
		$this->password = $password;
		$this->setArguments($username . ' ' . $password);
	}
}
