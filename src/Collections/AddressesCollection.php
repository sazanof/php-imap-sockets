<?php

namespace Sazanof\PhpImapSockets\Collections;

use Sazanof\PhpImapSockets\Models\Address;

class AddressesCollection extends Collection
{
	public function __construct(string $headerString, array $items = [])
	{
		parent::__construct($items);

		$addresses = imap_rfc822_parse_adrlist($headerString, Address::NONE_EXISTENT);
		if (!empty($addresses)) {
			foreach ($addresses as $address) {
				if ($address->host == Address::NONE_EXISTENT) continue;
				$this->add(
					new Address($address)
				);
			}
		}
	}
}
