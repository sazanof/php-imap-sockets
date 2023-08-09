<?php

namespace Sazanof\PhpImapSockets\Models;

use Sazanof\PhpImapSockets\Parts\AttachmentPart;
use Sazanof\PhpImapSockets\Parts\BasePart;
use Sazanof\PhpImapSockets\Parts\TextPart;

class BodyStructure
{
	private string $structRegExp = '/BODYSTRUCTURE ?\((.*)?\)/';
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
			if (str_starts_with($matches[1], '"text"')) { // hotfix
				$match = "($matches[1])";
			} else {
				$match = $matches[1];
			}
			$groups = $this->groups($match);
			$i = 1;
			foreach ($groups as $group) {
				if (is_null($this->multiPart)) {
					//$this->analizeBodyParts($group, null, 1, $i);
					$this->multiPart = $this->analizeBodyParts($group, null, 1, $i);
					//$this->multiPart = $this->analizeBodyParts($group, new MultiPart([], 1), 1, $i);
				} else {
					$this->analizeBodyParts($group, $this->multiPart, 1, $i);
				}
				$i++;
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

	protected function analizeBodyParts(string $string, MultiPart $parentMultipart = null, $depth = 1, $level = '')
	{
		$section = explode('.', $level);
		dump($section, $level);
		if (str_starts_with($string, '((')) {
			if (preg_match('/\((.*)\)/', $string, $matches)) {
				if (str_starts_with($matches[1], '("')) {
					if (strpos($matches[1], 'text') === 2) {
						// We get a multipart
						if (preg_match($this->parseOneSectionRe, $matches[1], $multipart)) {
							//dump("depth $depth (root)", $string);

							$nextPart = $multipart[0];
							$multipartSubtype = $multipart[2];
							$multipartBoundary = $multipart[3];
							$multipartDisposition = $multipart[4];
							$multipartLanguage = $multipart[5];
							$multipartLocation = $multipart[6];

							$newMpAnyway = new MultiPart($multipart, '1');
							$parentMultipart = $parentMultipart instanceof MultiPart ? $parentMultipart : $newMpAnyway;
							$next = str_replace(" \"$multipartSubtype\" ($multipartBoundary) $multipartDisposition $multipartLanguage $multipartLocation", '', $nextPart);
							$depth++;
							if ($parentMultipart !== $newMpAnyway) {
								$newMpAnyway->setSection($level);
								//TODO не добавлять children, убрать этот параметр или перенести это в part
								/** @var BasePart $lastPart */
								$section[$depth - 1] = $parentMultipart->getParts()->count();
								$level = implode('.', $section);
								$parentMultipart->getParts()->add(
									$this->analizeBodyParts($next, $newMpAnyway, $depth, $level));
							} else {
								$newMpAnyway = new MultiPart($multipart, $level);
								$section[] = 1;
								$level = implode('.', $section);

								$parentMultipart = $this->analizeBodyParts($next, $newMpAnyway, $depth, $level);
								$this->multiPart = $parentMultipart;
							}
						}
					} else {
						dd('file multipart detected');
					}
				} else {
					//$depth++;
					$parentMultipart = $this->analizeBodyParts($matches[1], $parentMultipart, $depth, $level);
				}
			}
		} else {
			$count = is_null($parentMultipart) ? 1 : $parentMultipart->getParts()->count();

			// (" come
			//We get single part;
			//dump("depth $depth", $string);
			if (str_starts_with($string, '("text')) {
				if (preg_match($this->parseOneTextRe, $string, $matches)) {
					$partToDelete = $matches[0];
					$parentMultipart = $parentMultipart instanceof MultiPart ? $parentMultipart : new MultiPart($matches, $level);
					$parentMultipart->getParts()->add(new TextPart($matches, $level));

					$section[array_key_last($section)]++;
					$level = implode('.', $section);

					$string = str_replace($partToDelete, '', $string);
					if (!empty($string)) {
						$this->analizeBodyParts($string, $parentMultipart, $depth, $level);
					}
				}
			} else {
				if (preg_match($this->parseOneFileRe, $string, $matches)) {
					$parentMultipart = $parentMultipart instanceof MultiPart ? $parentMultipart : new MultiPart($matches, $level);
					$attachment = new AttachmentPart($matches, $level);
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
