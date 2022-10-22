<?php

namespace Modules\Storage\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DemandDraftItem extends Model
{
    use SoftDeletes;
    protected $table='demand_draft_items';
    protected $fillable=[
        'amount',
        'title',
        'title_id',
        'kind',
        'kind_id',
        'model',
        'model_id',
        'mark',
    ];
}
