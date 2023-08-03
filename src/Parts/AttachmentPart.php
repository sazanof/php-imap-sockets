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
	protected ?string $language = null;
	protected ?string $location = null;


	public function __construct(array $matches)
	{
		parent::__construct($matches);
		$this->prepareName($matches[3]);
		$this->prepareFileName($matches[9]);
		$this->contentId = trim($matches[4], '"');
		$this->size = (int)$matches[7];
		$this->encoding = $matches[6];
		$this->language = $this->setValue($matches[10]);
		$this->location = $this->setValue($matches[11]);
	}

	public function fillValuesFromMatches()
	{

	}

	protected function prepareName(string $name)
	{
		if (preg_match('/"name" "(.*?)"/', $name, $m)) {
			$this->originalName = $m[1];
		}
	}

	protected function prepareFileName(string $filename)
	{
		if (preg_match('/"(.*?)" \("filename" "(.*?)"\)/', $filename, $m)) {
			$this->disposition = strtolower($m[1]);
			$this->isInline = $this->disposition === 'inline';
			$this->fileName = $this->setValue($m[2]);
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
