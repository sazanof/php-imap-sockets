![Logo](https://raw.githubusercontent.com/sazanof/php-imap-sockets/main/files/logo.png)

# PHP IMAP SOCKETS

**NEW library for working with email, using sockets.**

## Features

- Dependency free
- Body structure analyze via [BODYSTRUCTURE] command
- Flexible search and fetch
- Attachments support (inline too)
- Flags management
- Pagination

### Todo

- Sorting

## Install via composer

To install this project run

```bash
  composer require sazanof/php-imap-sockets
```

## Website (comming soon)

[Documentation](https://sazanof.ru)

## Basic usage

### Connection

```php
use \Sazanof\PhpImapSockets\Models\Connection;

// create new connection
$connection = new Connection('imap.example.com');
// open connection and enable debug
$connection->open()->enableDebug();
// login
$connection->login('USERNAME', 'PASSWORD');
```

### Query

```php
use \Sazanof\PhpImapSockets\Models\SearchQuery;

$query = new SearchQuery();
$query->all();
// OR
$query->subject('Test');
// OR
$query->new();
// OR
$query->or([
	'subject'=>[
		'One',
		'Two'
	],
	'since'=>'01-Jan-2023'
]);
// Use clear() method to clear query string
$query->clear();
```

### Mailbox

```php
$path = 'INBOX';
$mailbox = $connection->getMailboxByPath($path)->setConnection($connection)->select();
// array of messages NUMBERS (not UIDS)
$uids = $mailbox->search($query)->msgNums();
// or 
$mailbox->search($query)->setOrderDirection('DESC')->msgNums() // or ASC
```

### Messages

```php
use \Sazanof\PhpImapSockets\Models\MessageCollection;

$collection = new MessageCollection($uids, $mailbox);
// array of "Message"
$items = $collection->items();
```

### Pagination

```php
use \Sazanof\PhpImapSockets\Models\Paginator;

$paginator = new \Sazanof\PhpImapSockets\Models\Paginator($uids, $mailbox, 1, 6);
$messagesPaginated = $p->messages();
```

### Attachments

```php
/** @var \Sazanof\PhpImapSockets\Models\Message $message **/
$attachmentsParts = $message->getBodyStructure()->getAttachmentParts();
foreach ($attachmentsParts as $attachmentsPart) {
    if (!$attachmentsPart->isInline()) {
        // set attachment content to $attachmentsPart
        $attachmentsPart->setContent(
            $message->getAttachment($attachmentsPart->getSection()) // or save locally
        );
    }
}
```

### Text content of message

```php
/** @var \Sazanof\PhpImapSockets\Models\Message $message **/
//get text parts (plain,html) with inline images
$message->getBodyStructure()->getTextParts();
```

### Flags management

```php
use \Sazanof\PhpImapSockets\Models\Message;

//$message
$message->setImportant()->markAsDeleted();
//or
$message->addFlags(['one','two'])
//or
$message->replaceFlags(['one','two'])
//or
$message->clearFlags()
//or
$message->deleteFlags('one');
//trigger save
$message->saveFlags();
```

## Authors

- [@sazanof](https://www.github.com/sazanof)

## License

[MIT](https://choosealicense.com/licenses/mit/)

## Feedback

If you detected any security issues, please reach out to us at m@sazanof.ru

