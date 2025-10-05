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
spark migrate -n EmailQueueModule
```

2. Schedule the command via cron:

```bash
php spark email:sendqueue
```

Some notes on the cron job:

1. The script has CLI output, ensure to pipe the output to the ether.
2. The interval at which the script will send emails out is the frequency at which cron triggers it. See what makes sense for your use case.

```bash
# Add this to cron to process the sending of emails every 1 min and make output disappear (adjust to your spark path):
* * * * * php /var/www/html/spark email:sendqueue >> /dev/null 2>&1
```

## Configuration

Ensure your email settings are configured in app/Config/Email.php.

## Practical example

Assuming a proper fresh CI4 installation with a database already linked to it. We are going to send a queued email every time we reques the home page (this for a practical example only, of course).

1. Install the package.
2. Run the migration to create the email_queue table.
3. Create a view called email_test_view.php and put "Hello" inside of it.
4. On the Home controller add

```php
// Test of Email Queue addin
// Put this in the queue to send out an email
$emailQueueModel = new \EmailQueueModule\Models\EmailQueueModel();
$email = 'test@example.com';
$emailQueueModel->insert([
	'to' => $email,
	'subject' => 'You’ve been Invited to Join SPOT',
	'message' => view('email_test_view', []),
	'created_at' => date('Y-m-d H:i:s'),
]);
```
verify that the entry showed up on the email_queue table.

5. Run 

```bash
spark email:sendqueue
```
It should send your email out if you have the email sender configured properly. Whenever it is not sent successfully, it will increment the attempts column until it can send the email at which point, it will update the sent_at column and leave it forever in the database unless (recommended) you implment another service to clean the table. I may write the service to clean up if there is demand.

## License

MIT