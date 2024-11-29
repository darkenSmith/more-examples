<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionEvent extends Model
{
    protected $fillable = ['transaction_event_id', 'sl_transaction_id', 'change'];

    protected $table = 'tr_transaction_events';

    protected $connection = 'retailer_non_prefix';
}
