<?php

namespace Sazanof\PhpImapSockets\Models;

use Sazanof\PhpImapSockets\Parts\AttachmentPart;
use Sazanof\PhpImapSockets\Parts\TextPart;

class BodyStructure
{
	protected string $structRegExp = '/BODYSTRUCTURE \((.*)\)/';
	protected string $groupRegExp = '/\(((?>[^()]+)|(?R))*\)/';
	protected string $bracesRegExp = '/(?=\(((?:[^()]++|\((?1)\))++)\))/';
	protected string $parseOneSectionRe = '/\((.+)\) "(related|alternative|mixed)" \((.*?)\) (.*?) (.*?) (.*?)$/i';
	protected string $parseOneTextRe = '/\("text" "(.*?)" \((.+?)\) (.*?) (.*?) "(.*?)" (\d+|NIL) (\d+|NIL) (.*?) (.*?) (.*?) (.*?)\)/i';
	protected string $parseOneFileRe = '/\("(image|video|alternative)" "(.*?)" \((.+?)\) (.*?) (.*?) "(.*?)" (\d+|NIL) (\d+|NIL) \((.*?)\) (.*?) (.*?)\)/';

	protected const TYPES = ['MIXED', 'ALTERNATIVE', 'RELATED'];

	//[^()]{1,}+(?:\([^()]*\)[^()]+)*

	public function __construct(string $responseLine)
	{
		if (preg_match($this->structRegExp, $responseLine, $matches)) {
			$groups = $this->groups($matches[1]);
			dd($this->analizeBodyParts($groups[0]));
		}

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
			//level++
			if (preg_match('/\((.*)\)/', $string, $matches)) {
				if (str_starts_with($matches[1], '("')) {
					if (strpos($matches[1], 'text') === 2) {
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
					} elseif (strpos($matches[1], 'image') === 2) {
						dd('image multipart detected');
					}
				}
			}
		} else {
			// (" come
			if (str_starts_with($string, '("text')) {
				if (preg_match($this->parseOneTextRe, $string, $matches)) {
					$partToDelete = $matches[0];
					$partSubtype = $matches[1];
					$partCharset = $matches[2];
					$partContentId = $matches[3];
					$partDescription = $matches[4];
					$partEncoding = $matches[5];
					$partSize = (int)$matches[6];
					$partLinesCount = (int)$matches[7];
					$partBodyMd5 = (int)$matches[8];
					$partDisposition = $matches[9];
					$partLanguage = $matches[10];
					$partLocation = $matches[11];
					$parentMultipart->getParts()->add(new TextPart($matches));
					//dump('We get single part = ' . $partToDelete);
					$string = str_replace($partToDelete, '', $string);
					if (!empty($string)) {
						$this->analizeBodyParts($string, $parentMultipart);
					}
				}
			} elseif (str_starts_with($string, '("image')) {
				if (preg_match($this->parseOneFileRe, $string, $matches)) {
					$parentMultipart->getParts()->add(new AttachmentPart($matches));
				}
			}
		}
		return $parentMultipart;
	}
}
