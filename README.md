# CodeIgniter4 Email Queue

A CodeIgniter 4 addin to manage and send queued emails via CLI. Designed for cron-based dispatching.

## Features

- CLI command `email:sendqueue` to process unsent emails
- Migration for `email_queue` table
- Retry logic with attempt tracking

## Installation

```bash
composer require 0ruialvel0/codeigniter4-emailqueue
```
## Usage

1. Run the migration:

```bash
php spark migrate CreateEmailQueueTable
```

2. Schedule the command via cron:

```bash
php spark email:sendqueue
```

Some notes on the cron job:

1. The script has CLI output, ensure to pipe the output to the ether.
2. The interval at which the script will send emails out is the frequency at which cron triggers it. See what makes sense for your use case.

```bash
# Add this to cron to process the sending of emails every 1 min and make output disappear:
* * * * * php /var/www/html/commanew/spark email:sendqueue >> /dev/null 2>&1
```

## Configuration

Ensure your email settings are configured in app/Config/Email.php.

## Practical example

Assuming a proper CI4 installation with a database alredy linked to it.

1. Install the package.

## License

MIT