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
            'user_id',
            'id',
            'curator_id',
            'id'
        )->select(['users.id', 'users.name', 'users.surname']);
    }
}
