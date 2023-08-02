<?php

namespace Sazanof\PhpImapSockets\Models;

use Sazanof\PhpImapSockets\Parts\BodyStructurePart;

/**
 * https://tools.ietf.org/html/rfc3501#section-7.4.2
 *
 * multipart:
 *    - parts array
 *    - subtype
 *    + params array
 *    + disposition array
 *    + language
 *    + location
 *
 * basic:
 *    - type
 *    - subtype
 *    - params array
 *    - content id
 *    - description
 *    - encoding
 *    - size
 *    + body MD5
 *    + disposition array
 *    + language
 *    + location
 *
 * text:
 *    - type
 *    - subtype
 *    - params array
 *    - content id
 *    - description
 *    - encoding
 *    - size
 *    - size in text lines
 *    + body MD5
 *    + disposition array
 *    + language
 *    + location
 *
 * message/rfc822:
 *    - type
 *    - subtype
 *    - params array
 *    - content id
 *    - description
 *    - encoding
 *    - size
 *    - envelope structure
 *    - body structure
 *    - size in text lines
 *    + body MD5
 *    + disposition array
 *    + language
 *    + location
 */
class BodyStructureOld
{

	public const TYPE_TEXT = 'text';
	public const TYPE_IMAGE = 'image';
	public const TYPE_APPLICATION = 'application';
	public const TYPE_VIDEO = 'video';
	public const TYPE_BOUNDARY = 'boundary';
	public const TYPES = [
		self::TYPE_TEXT,
		self::TYPE_APPLICATION,
		self::TYPE_BOUNDARY,
		self::TYPE_IMAGE,
		self::TYPE_VIDEO
	];
	protected const SUBTYPES = ['MIXED', 'MESSAGE', 'DIGEST', 'ALTERNATIVE', 'RELATED', 'REPORT', 'SIGNED', 'ENCRYPTED', 'FORM DATA'];

	protected string $structRegExp = '/BODYSTRUCTURE \((.*)\)\)/';
	protected string $groupRegExp = '/\(((?>[^()]+)|(?R))*\)/';
	protected string $bracesRegExp = '/(?=\(((?:[^()]++|\((?1)\))++)\))/';
	protected mixed $bodyStructure;
	protected array $structArray = [];

	public function __construct(string $text)
	{
		if (preg_match($this->structRegExp, $text, $matches)) {
			$this->bodyStructure = $matches[1];
		}
		$arr = $this->executeStringPart($this->bodyStructure);
		$this->structArray = [];
		foreach ($arr as $item) {
			if (preg_match_all($this->bracesRegExp, $item, $matches)) {
				foreach ($matches[1] as $match) {
					if (
						str_starts_with($match, '"') &&
						preg_match('/' . implode('|', self::TYPES) . '/', $match, $matchMatches)
					) {
						$this->structArray[] = new BodyStructurePart($matchMatches[0], $match);
					}
				}
			}
		}
	}

	protected function executeStringPart(string $text)
	{
		if (preg_match($this->groupRegExp, $text, $matches)) {
			$this->structArray[] = $matches[0];
			$text = str_replace($matches[0], '', $text);
			$this->executeStringPart($text);
		}
		return $this->structArray;
	}
}

