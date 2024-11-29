<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationEvent extends Model
{
    protected $fillable = ['retailer_event_id', 'data_id', 'change'];

    protected $table = 'retail_ret_events';

    protected $connection = 'retailer_non_prefix';
}
