<?php

namespace Sazanof\PhpImapSockets\Models;

use Sazanof\PhpImapSockets\Parts\AttachmentPart;
use Sazanof\PhpImapSockets\Parts\TextPart;

class BodyStructure
{
	private string $structRegExp = '/BODYSTRUCTURE \((.*)\)/';
	private string $groupRegExp = '/\(((?>[^()]+)|(?R))*\)/';
	private string $bracesRegExp = '/(?=\(((?:[^()]++|\((?1)\))++)\))/';
	private string $parseOneSectionRe = '/\((.+)\) "(related|alternative|mixed)" \((.*?)\) (.*?) (.*?) (.*?)$/i';
	private string $parseOneTextRe = '/\("text" "(.*?)" \((.+?)\) (.*?) (.*?) "(.*?)" (\d+|NIL) (\d+|NIL) (.*?) (.*?) (.*?) (.*?)\)/i';
	private string $parseOneFileRe = '/\("(image|video|application)" "(.*?)" \((.+?)\) (.*?) (.*?) "(.*?)" (\d+|NIL) (\d+|NIL) \((.*?)\) (.*?) (.*?)\)/';

	protected ?MultiPart $multiPart = null;// сделать коренной мультипарт, чтобы в него в цикле группы перебирать

	protected const TYPES = ['MIXED', 'ALTERNATIVE', 'RELATED'];

	//[^()]{1,}+(?:\([^()]*\)[^()]+)*

	public function __construct(string $responseLine)
	{
		if (preg_match($this->structRegExp, $responseLine, $matches)) {
			$groups = $this->groups($matches[1]);
			foreach ($groups as $group) {
				if (is_null($this->multiPart)) {
					$this->multiPart = $this->analizeBodyParts($group);
				} else {
					$this->analizeBodyParts($group, $this->multiPart);
				}

			}
		}
	}

	/**
	 * @return MultiPart|null
	 */
	public function getMultiPart(): ?MultiPart
	{
		return $this->multiPart;
	}

	protected function groups(string $string)
	{
		$groups = [];
		if (preg_match_all($this->groupRegExp, $string, $matches)) {
			foreach ($matches[0] as $match) {
				if (str_starts_with($match, '(')) {
					$groups[] = $match;
				}
			}
		}
		return $groups;
	}

	protected function analizeBodyParts(string $string, MultiPart $parentMultipart = null)
	{
		//dump('Try analize with ' . $string);
		if (str_starts_with($string, '((')) {
			if (preg_match('/\((.*)\)/', $string, $matches)) {
				if (str_starts_with($matches[1], '("')) {
					if (strpos($matches[1], 'text') === 2) {
						// We get a multipart
						if (preg_match($this->parseOneSectionRe, $matches[1], $multipart)) {
							$nextPart = $multipart[0];
							$multipartSubtype = $multipart[2];
							$multipartBoundary = $multipart[3];
							$multipartDisposition = $multipart[4];
							$multipartLanguage = $multipart[5];
							$multipartLocation = $multipart[6];

							$newMpAnyway = new MultiPart($multipart);
							$parentMultipart = $parentMultipart instanceof MultiPart ? $parentMultipart : $newMpAnyway;
							$next = str_replace(" \"$multipartSubtype\" ($multipartBoundary) $multipartDisposition $multipartLanguage $multipartLocation", '', $nextPart);
							if ($parentMultipart !== $newMpAnyway) {
								$parentMultipart->addChild($this->analizeBodyParts($next, $newMpAnyway));
							} else {
								$this->analizeBodyParts($next, $parentMultipart);
							}
						}
					} else {
						dd('file multipart detected');
					}
				}
			}
		} else {
			// (" come
			//We get single part;
			if (str_starts_with($string, '("text')) {
				if (preg_match($this->parseOneTextRe, $string, $matches)) {
					$partToDelete = $matches[0];
					$parentMultipart = $parentMultipart instanceof MultiPart ? $parentMultipart : new MultiPart($matches);
					$parentMultipart->getParts()->add(new TextPart($matches));
					$string = str_replace($partToDelete, '', $string);
					if (!empty($string)) {
						$this->analizeBodyParts($string, $parentMultipart);
					}
				}
			} else {
				if (preg_match($this->parseOneFileRe, $string, $matches)) {
					$parentMultipart = $parentMultipart instanceof MultiPart ? $parentMultipart : new MultiPart($matches);
					$attachment = new AttachmentPart($matches);
					if (!$attachment->isInline()) {
						if (is_null($parentMultipart))
							$parentMultipart->plusOneToAttachmentsCount();
						if (!$parentMultipart->isAttachmentsExists()) {
							$parentMultipart->setAttachmentsExists(true);
						}
					}

					$parentMultipart->getParts()->add($attachment);
				}
			}
		}
		return $parentMultipart;
	}
}
