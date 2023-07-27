<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets;

use Sazanof\PhpImapSockets\Collections\Collection;
use Sazanof\PhpImapSockets\Traits\PrepareArgument;

class Query
{
	use PrepareArgument;

	protected Collection $parameters;
	protected ?string $charset = null;

	public function __construct(string $charset = 'UTF-8')
	{
		$this->parameters = new Collection();
		if ($charset) {
			$this->charset = $charset;
			$this->charset($charset);
		}
	}

	public function query()
	{
		return $this;
	}

	public function charset(string $charset)
	{
		$this->charset = $charset;
		$this->addParamWithValue('CHARSET', $charset);
	}

	public function all()
	{
		$this->parameters->add('ALL');
		return $this;
	}

	public function answered()
	{
		$this->parameters->add('ANSWERED');
	}

	public function bcc(string $address)
	{
		$this->parameters->add('BCC' . $address);
	}

	public function before(string|\DateTime $date)
	{
		$this->addParamWithValue('BEFORE', $date);
		return $this;
	}

	public function body(string $text)
	{
		$this->addParamWithValue('BODY', $text, false);
		return $this;
	}

	public function cc(string $text)
	{
		$this->addParamWithValue('CC', $text);
		return $this;
	}

	public function deleted()
	{
		$this->parameters->add('DELETED');
		return $this;
	}

	public function draft()
	{
		$this->parameters->add('DRAFT');
		return $this;
	}

	public function flagged()
	{
		$this->parameters->add('FLAGGED');
		return $this;
	}

	public function from(string $address)
	{
		$this->addParamWithValue('FROM', $address);
		return $this;
	}

	public function header(string $field, string $text)
	{
		$this->addParamWithValue('HEADER', "$field $text", false, false);
		return $this;
	}

	public function keyword(string $flag)
	{
		$this->addParamWithValue('KEYWORD', $flag);
		return $this;
	}

	public function larger(int $size)
	{
		$this->addParamWithValue('LARGER', $size);
		return $this;
	}

	public function new()
	{
		$this->parameters->add('NEW');
		return $this;
	}

	public function not(string $text)
	{
		$this->addParamWithValue('NOT', $text);
		return $this;
	}

	public function old()
	{
		$this->parameters->add('OLD');
		return $this;
	}

	public function on(string|\DateTime $date)
	{
		$this->addParamWithValue('ON', $date);
	}

	public function or(array $searches)
	{
		/*
		 * $searches = ['to'=>'address1@example.com','cc'=>'address2@example.com','body'=>'Test string must be quoted']
		 */
		$value = '';
		$count = -1;
		foreach ($searches as $key => $val) {
			// todo - make $val array
			if (is_array($val)) {
				foreach ($val as $str) {
					$value .= ' ' . strtoupper($key) . ' ' . $this->addQuotes($str);
					$count++;
				}
			} else {
				$value .= ' ' . strtoupper($key) . ' ' . $this->addQuotes($val);
				$count++;
			}

		}
		$or = str_repeat('OR ', $count);
		$this->addParamWithValue(rtrim($or), ltrim($value), false, false);
		return $this;
	}

	public function recent()
	{
		$this->parameters->add('RECENT');
		return $this;
	}

	public function seen()
	{
		$this->parameters->add('SEEN');
		return $this;
	}

	public function sentBefore(string|\DateTime $date)
	{
		$this->addParamWithValue('SENTBEFORE', $date);
		return $this;
	}

	public function sentOn(string|\DateTime $date)
	{
		$this->addParamWithValue('SENTON', $date);
		return $this;
	}

	public function sentSince(string|\DateTime $date)
	{
		$this->addParamWithValue('SENTSINCE', $date);
		return $this;
	}

	public function since(string|\DateTime $date)
	{
		$this->addParamWithValue('SINCE', $date);
		return $this;
	}

	public function smaller(int $size)
	{
		$this->addParamWithValue('SMALLER', $size);
	}

	public function subject(string $subject)
	{
		$this->addParamWithValue('SUBJECT', imap_utf8($subject), false);
	}

	public function text(string $text)
	{
		$this->addParamWithValue('TEXT', $text);
	}

	public function to(string $address)
	{
		$this->addParamWithValue('TO', $address);
		return $this;
	}

	public function uid(array $uids)
	{
		$this->addParamWithValue('UID', implode(',', $uids));
		return $this;
	}

	public function unanswered()
	{
		$this->parameters->add('UNANSWERED');
		return $this;
	}

	public function undeleted()
	{
		$this->parameters->add('UNADELETED');
		return $this;
	}

	public function unflagged()
	{
		$this->parameters->add('UNFLAGGED');
		return $this;
	}

	public function unkeyword(string $keyword)
	{
		$this->addParamWithValue('UNKEYWORD', $keyword);
		return $this;
	}

	public function unseen()
	{
		$this->parameters->add('UNSEEN');
		return $this;
	}

	public function toQueryString()
	{
		return implode(' ', $this->parameters->toArray());
	}

	private function addParamWithValue(string $command, string|\DateTime $value, $convert = true, $quotes = true)
	{
		if ($value instanceof \DateTime) {
			$value = $value->format('d-M-Y');
		}
		if ($quotes) {
			$value = $this->addQuotes($value);
		}
		if ($convert) {
			$value = $this->imapUtf8ToMutf7($value);
		}
		$this->parameters->add("$command $value");
	}
}
