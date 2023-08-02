<?php
/**
 * @copyright Copyright (c) 2023, Mikhail Sazanov <m@sazanof.ru>
 * @author Mikhail Sazanov <m@sazanof.ru>
 * @license GNU AGPL version 3 or any later version
 */

namespace Sazanof\PhpImapSockets\Collections;

class Collection
{
	protected array $collection = [];

	public function __construct(array $items = [])
	{
		$this->collection = $items;
	}

	public function add(mixed $item)
	{
		$this->collection[] = $item;
	}

	public function empty()
	{
		$this->collection = [];
	}

	public function unset()
	{
		unset($this->collection);
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

	public function map(\Closure $closure)
	{
		array_map(function ($item) use ($closure) {
			$closure($item);
		}, $this->collection);
		return $this;
	}

	public function find(\Closure $closure, $type = ARRAY_FILTER_USE_BOTH)
	{
		return array_values(array_filter($this->collection, function ($value, $key) use ($closure) {
			return $closure($value, $key);
		}, $type));
	}

	public function toArray()
	{
		return $this->collection;
	}

	public function first()
	{
		return count($this->collection) > 0 ? $this->collection[0] : null;
	}
}
