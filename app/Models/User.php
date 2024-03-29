<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\HasApiTokens;
use Modules\Hr\Entities\Employee\Employee;
use Modules\Hr\Entities\Employee\UserDetail;
use Modules\Hr\Entities\User\UserEducation;


class User extends Authenticatable
{
    use Notifiable , HasApiTokens;

    const OFFICE = 3;
    const SUPER_ADMIN = 1;
    const EMPLOYEE = 2;
    const DEV = 4;

    private $userRolesForRequest = [];
    private $userWorkingOfficeId;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'username', 'voen', 'role_id' , 'surname' , 'is_office_user' , 'office_company_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function details(){
        return $this->hasOne(UserDetail::class);
    }
    public function role(){
        return $this->belongsToMany(Role::class,'user_roles')->first();
    }

    public function roles(){
        return $this->belongsToMany(Role::class, 'user_roles');
    }



    public function employment(){
        return $this->hasMany(Employee::class);
    }
    public function scopeCompany($q){
        return $q->whereHas('employment' , function ($q){
            $q->where('company_id' , request('company_id'));
        });
    }
    public function education(){
        return $this->hasMany(UserEducation::class);
    }

    public function getRoleId(){
        if (!$this->roleId){
            $role = UserRole::where([
                'user_id' => $this->getKey(),
                'company_id' => request()->get('company_id')
            ])->first(['role_id as id']);
            $this->roleId = $role->id ?? null;
        }
        return $this->roleId;
    }

    /**
     * @return array
     */
    public function getUserRolesForRequest(): array
    {
        return $this->userRolesForRequest;
    }

    /**
     * @param array $userRolesForRequest
     */
    public function setUserRolesForRequest(array $userRolesForRequest): void
    {
        $this->userRolesForRequest = $userRolesForRequest;
    }

    public function getEmployeeId($company) : int{
        return  Employee::where([
            ['user_id' , '='  , Auth::id()],
            ['company_id' , '='  , $company],
        ])->first(['id'])->id;
    }

    /**
     * @return mixed
     */
    public function getUserWorkingOfficeId()
    {
        return $this->userWorkingOfficeId;
    }

    /**
     * @param mixed $userWorkingOfficeId
     */
    public function setUserWorkingOfficeId($userWorkingOfficeId): void
    {
        $this->userWorkingOfficeId = $userWorkingOfficeId;
    }

}
