<?php

namespace Sazanof\PhpImapSockets\Commands;

class AuthenticateCommand extends Command
{
	protected string $name = 'AUTHENTICATE';

	public function __construct($username, $password)
	{
		$connectionString = 'XOAUTH2 ' . base64_encode("user=$username\1auth=Bearer $password\1\1");
		$this->setArguments($connectionString);
	}

}
