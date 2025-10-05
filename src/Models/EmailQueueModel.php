<?php

namespace EmailQueueModule\Models;

use CodeIgniter\Model;

class EmailQueueModel extends Model
{
    protected $table            = 'email_queue';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'to',
        'subject',
        'message',
        'headers',
        'attachments',
        'sent',
        'attempts',
        'created_at',
        'sent_at',
    ];
}
