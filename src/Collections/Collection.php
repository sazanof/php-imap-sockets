<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Collections;

class Collection
{
	protected array $collection;

	public function add(mixed $item)
	{
		$this->collection[] = $item;
	}

	public function remove()
	{

	}

	public function indexOf()
	{

	}

	public function get()
	{

	}

	public function map()
	{

	}

	public function find()
	{

	}

	public function toArray()
	{
		return $this->collection;
	}
}
