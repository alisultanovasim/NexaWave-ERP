<?php


namespace Modules\Hr\Traits;


use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Modules\Hr\Entities\Employee\Employee;

trait HasCuratorRelation
{
    public function curator(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            Employee::class,
            'id',
            'id',
            'curator_id',
            'user_id'
        )->select(['users.id', 'users.name', 'users.surname']);
    }
}
