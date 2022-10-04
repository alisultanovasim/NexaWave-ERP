<?php

    namespace Modules\Storage\Entities;

    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\SoftDeletes;
    use Illuminate\Support\Facades\DB;
    use Modules\Hr\Entities\Employee\Employee;

    class DemandItem extends Model
    {
        use SoftDeletes;

        const REJECTED = 0;
        const ACCEPTED = 1;
        const WAIT = 2;

        protected $guarded = ["id"];
        protected $fillable = [
            'amount',
            'title',
            'title_id',
            'kind',
            'kind_id',
            'model',
            'model_id',
            'mark',
        ];

        public function assignment()
        {
            return $this->belongsTo(DemandAssignment::class, 'demand_assignment_id');
        }

        public function employee()
        {
            return $this->belongsTo(Employee::class);
        }

        public function scopeCompany($q)
        {
            return $q->whereHas('assignment', function ($q) {
                $q->company();
            });
        }

        public function demand()
        {
            return $this->belongsTo(Demand::class);
        }

    }
