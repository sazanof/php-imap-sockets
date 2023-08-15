<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Response;

class ExamineResponse
{
	public int $exists = 0;
	public int $recent = 0;
	public int $uidnext = 0;
	public int $uidvalidiry = 0;
	public int $unseen = 0;

	public function __construct(Response $response)
	{
		if ($response->isOk() && count($response->lines()) > 0) {
			foreach ($response->lines() as $line) {
				if (preg_match('/\* (\d+) EXISTS/', $line, $exists) > 0) {
					$this->exists = (int)$exists[1];
				}
				if (preg_match('/\* (\d+) RECENT/', $line, $recent) > 0) {
					$this->recent = (int)$recent[1];
				}
				if (preg_match('[UNSEEN (\d*)]', $line, $unseen) > 0) {
					$this->unseen = (int)$unseen[1];
				}
				if (preg_match('[UIDNEXT (\d*)]', $line, $uidnext) > 0) {
					$this->uidnext = (int)$uidnext[1];
				}
				if (preg_match('[UIDVALIDITY (\d*)]', $line, $uidvalidity) > 0) {
					$this->uidvalidiry = (int)$uidvalidity[1];
				}
			}
		}
		return $this;
	}
}
