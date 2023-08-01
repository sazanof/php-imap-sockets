<?php

namespace Sazanof\PhpImapSockets\Parts;

class AttachmentPart extends BasePart
{
	protected string $originalName;
	protected string $fileName;
	protected ?string $description;
	protected ?string $contentId;
	protected int $size = 0;
	protected string $encoding;
	protected string $disposition;
	protected bool $isInline = false;
	private string $re = '/"(.*?)" "(.*?)" \((.*?)\) (.*?|NIL) (.*?|NIL) "(.*?)" (\d+) (.*?|NIL) \("(.+?)" \("(.+?)" "(.+?)"\)\) (.*?|NIL) (.*?|NIL)$/';


	public function __construct(string $type, string $text)
	{
		parent::__construct($type, $text);
		$this->parseRe();
	}

	public function parseRe()
	{
		if (preg_match($this->re, $this->text, $matches)) {
			$this->subtype = $matches[2];
			$this->contentId = $this->setValue($matches[4]);
			$this->description = $this->setValue($matches[5]);
			$this->size = (int)$matches[7];
			$this->disposition = $this->setValue($matches[9]);
			$this->isInline = $this->disposition === 'inline';
			$this->originalName = $this->setValue($matches[11]);
			$this->detectEncoding();
			$this->convertName();
		}
	}

	protected function convertName()
	{
		//TODO replace this with different encoding check & conversion?
		if (str_starts_with($this->originalName, '=?UTF-8')) {
			$this->fileName = imap_utf8($this->originalName);
		} else {
			$this->fileName = $this->originalName;
		}
	}

	protected function detectEncoding()
	{
		if (preg_match('/=\?(.+?)\?/', $this->originalName, $matches)) {
			$this->encoding = strtoupper($matches[1]);
		}
	}
}
