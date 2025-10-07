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

    protected $emailQueueModel;
    protected $emailService;

    public function __construct($logger = null, $commands = null, $emailQueueModel = null, $emailService = null)
    {
        parent::__construct($logger, $commands);
        $this->emailQueueModel = $emailQueueModel ?? new EmailQueueModel();
        $this->emailService = $emailService ?? \Config\Services::email();
    }

    public function run(array $params)
    {
        $emails = $this->emailQueueModel->where('sent', 0)->findAll(50);

        if (empty($emails)) {
            CLI::write('No emails to send.', 'yellow');
            return;
        }

        foreach ($emails as $row) {
            $this->emailService->clear();
            $this->emailService->setTo($row['to']);
            $this->emailService->setMailType('html');
            $this->emailService->setSubject($row['subject']);
            $this->emailService->setMessage($row['message']);

            try {
                if ($this->emailService->send()) {
                    $this->emailQueueModel->update($row['id'], [
                        'sent'     => 1,
                        'sent_at'  => date('Y-m-d H:i:s'),
                        'attempts' => $row['attempts'] + 1,
                    ]);
                    CLI::write("Sent to {$row['to']}", 'green');
                } else {
                    $this->emailQueueModel->update($row['id'], [
                        'attempts' => $row['attempts'] + 1,
                    ]);
                    CLI::write("Failed to send to {$row['to']}", 'red');
                    CLI::write($this->emailService->printDebugger(['headers', 'subject', 'body']), 'yellow');
                }
            } catch (\Exception $e) {
                $this->emailQueueModel->update($row['id'], [
                    'attempts' => $row['attempts'] + 1,
                ]);
                CLI::write("Error sending to {$row['to']}: " . $e->getMessage(), 'red');
            }
        }
    }
}