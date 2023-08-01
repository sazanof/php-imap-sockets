<?php

namespace Sazanof\PhpImapSockets\Response;

class BodyStructureResponse
{
	public function __construct(string $bodyStructureToParse)
	{
		if (preg_match('/.*\(BODY\w{0,9} (.*)\)/', $bodyStructureToParse, $matches)) {
			dd($matches[1]);
		}
	}
}
