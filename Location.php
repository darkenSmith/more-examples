<?php

namespace App\Models;

use MichaelAChrisco\ReadOnly\ReadOnlyTrait;
use Cybertill\Framework\Database\Model\CTModel;

class Location extends CTModel
{
    // Location is accessed through the core schema. Allow read only access to the table, initially.
    use ReadOnlyTrait;

    // Required as Eloquent appends 's' to the model name when auto mapping to a table. This is the location table in the
    // core product.
    protected $table = "data_location";

    // Required as API prefix prepends "po_" to default table name.
    protected $connection = 'retailer_non_prefix';

    /**
     * @var array
     */
    protected $appends = [
        'reference'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function locationGroup() {
        return $this->belongsTo(LocationGroup::class, 'location_group_id', 'id');
    }

    public function country() {
        return $this->belongsTo(Country::class);
    }

    public function tills() {
        return $this->hasMany(Till::class);
    }

    /**
     * This is needed for the $appends
     * @return string
     */
    public function getReferenceAttribute(): string {
        return $this->id . '-' . $this->created_at->format('ym');
    }

}
