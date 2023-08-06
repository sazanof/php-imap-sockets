<?php

namespace Sazanof\PhpImapSockets\Models;

use Sazanof\PhpImapSockets\Collections\MessageCollection;

class Paginator
{
	protected int $page = 1;
	protected string $name = 'page';
	protected array $uids;
	protected array $uidsByPage;
	protected int $start = 0;
	protected int $limit;
	protected int $total;
	protected int $totalPages;
	protected int $lastPage;
	protected MessageCollection $messages;
	protected Mailbox $mailbox;

	public function __construct(array $uids, Mailbox $mailbox, int $page = 1, int $limit = 10)
	{
		$this->uids = $uids;
		$this->page = $page;
		$this->limit = $limit;
		$this->total = count($uids);
		$this->updateTotalPages();
		$this->lastPage = $this->totalPages;
		if ($this->page > $this->lastPage) {
			throw new \Exception('Page not found');
		}
		$this->updateStart();
		$this->mailbox = $mailbox;
		$this->uidsByPage = array_filter(
			$this->uids,
			function ($key) {
				return $key >= $this->start && $key < ($this->start + $this->limit);
			},
			ARRAY_FILTER_USE_KEY);
	}

	public function messages()
	{
		$this->messages = new MessageCollection($this->uidsByPage, $this->mailbox);
		return $this->messages->items();
	}

	/**
	 * @param int $lastPage
	 */
	private function setLastPage(int $lastPage): void
	{
		$this->lastPage = $lastPage;
	}

	/**
	 * @return int
	 */
	public function getLastPage(): int
	{
		return $this->lastPage;
	}

	/**
	 * @return int
	 */
	public function getTotal(): int
	{
		return $this->total;
	}

	/**
	 * @return int
	 */
	public function getTotalPages(): int
	{
		return $this->totalPages;
	}

	/**
	 * @return void
	 */
	private function updateTotalPages(): void
	{
		$this->totalPages = ceil(count($this->uids) / $this->limit);
	}

	/**
	 * @param string $name
	 */
	public function setName(string $name): void
	{
		$this->name = $name;
	}


	private function updateStart()
	{
		$this->start = $this->page * $this->limit - $this->limit;
	}

	/**
	 * @param int $page
	 * @return $this
	 */
	public function setPage(int $page)
	{
		$this->page = $page;
		$this->updateTotalPages();
		$this->updateStart();

		return $this;
	}

	/**
	 * @param int $limit
	 */
	public function setLimit(int $limit): void
	{
		$this->limit = $limit;
		$this->updateTotalPages();
		$this->updateStart();
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return int
	 */
	public function getStart(): int
	{
		return $this->start;
	}

	/**
	 * @return int
	 */
	public function getPage(): int
	{
		return $this->page;
	}

	/**
	 * @return int
	 */
	public function getLimit(): int
	{
		return $this->limit;
	}

	/**
	 * @return array
	 */
	public function getUids(): array
	{
		return $this->uids;
	}
}
