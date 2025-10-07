<?php

namespace EmailQueueModule\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use EmailQueueModule\Models\EmailQueueModel;

class EmailQueueHandler extends BaseCommand
{
    protected $group       = 'Email';
    protected $name        = 'email:sendqueue';
    protected $description = 'Send emails from the email_queue table';

    public function run(array $params)
    {
        $emailQueue = new EmailQueueModel();
        $emails = $emailQueue->where('sent', 0)->findAll(50); // limit to 50 per run

        if (empty($emails)) {
            CLI::write('No emails to send.', 'yellow');
            return;
        }

        $email = \Config\Services::email();

        foreach ($emails as $row) {
            $email->clear();
            $email->setTo($row['to']);
            $email->setMailType('html');
            $email->setSubject($row['subject']);
            $email->setMessage($row['message']);

            // TODO: #1 Optionally handle attachments and headers here
            // TODO: #2 Implement max number of attempts before marking as failed
            try {
                if ($email->send()) {
                    $emailQueue->update($row['id'], [
                        'sent'     => 1,
                        'sent_at'  => date('Y-m-d H:i:s'),
                        'attempts' => $row['attempts'] + 1,
                    ]);
                    CLI::write("Sent to {$row['to']}", 'green');
                } else {
                    $emailQueue->update($row['id'], [
                        'attempts' => $row['attempts'] + 1,
                    ]);
                    CLI::write("Failed to send to {$row['to']}", 'red');
                    CLI::write($email->printDebugger(['headers', 'subject', 'body']), 'yellow');
                }
            } catch (\Exception $e) {
                $emailQueue->update($row['id'], [
                    'attempts' => $row['attempts'] + 1,
                ]);
                CLI::write("Error sending to {$row['to']}: " . $e->getMessage(), 'red');
            }
        }
    }
}
