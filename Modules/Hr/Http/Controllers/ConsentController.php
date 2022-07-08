<?php


namespace Modules\Hr\Http\Controllers;


use App\Models\UserRole;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Modules\Hr\Entities\AcademicDegree;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;
use Modules\Hr\Entities\Consent;
use Modules\Hr\Entities\Employee\Employee;

class ConsentController extends Controller
{
    use ApiResponse ,ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request,[
           'company_id'=>['required'],
           'employee_id'=>['required',Rule::exists('employees','id')],
            'office_id'=>['required']

        ]);
        $employee=Employee::query()
            ->where('employees.id',$request->employee_id)
            ->get();
            foreach ($employee as $value){
                $user_id=$value['user_id'];
            }

            $user_role=UserRole::query()
                ->selectRaw('role_id')
                ->where('user_roles.user_id',$user_id)
                ->get();
        foreach ($user_role as $item) {
                $user_role_id=$item['role_id'];
            }
            if( $user_role_id==8 || $user_role_id==9)
                $consents=Consent::query()
                    ->with('employee.user.details')
                    ->where(['company_id'=>$request->company_id,
                            'office_id'=>$request->office_id
                        ])
                    ->get();
            else{
                $consents=Consent::query()
                    ->where(['company_id'=>$request->company_id,
                        'requester_id'=>$request->employee_id,
                        'office_id'=>$request->office_id
                        ])
                    ->get();
            }
            if ($consents->count())
                return $this->successResponse($consents);
            else return  response()->json(['message'=>'There is no information yet.']);
    }

    public function create(Request $request)
    {
        $this->validate($request,[
           'company_id'=>'required',
           'office_id'=>['required',Rule::exists('offices','id')],
           'requester_id'=>'required|integer',
           'start_date'=>'required|date|before_or_equal:end_date',
           'end_date'=>'required|date|after_or_equal:start_date',
           'work_date'=>'required|date|after_or_equal:start_date',
            'responsible_id'=>['required',Rule::exists('employees','id')],
            'reason'=>'nullable|string|min:3|max:77',
        ]);
//        $consent=new Consent();
//        $consent->company_id=$request->company_id;
//        $consent->start_date=$request->start_date;
//        $consent->requester_id=$request->requester_id;
//        $consent->end_date=$request->end_date;
//        $consent->work_date=$request->work_date;
//        $consent->responsible_id=$request->responsible_id;
//        $consent->status=$request->status;
//        $consent->reason=$request->reason;
//        $consent->save();

        Consent::query()->create($request->all());

        return response()->json(['message'=>'Created'],201);
    }
    public function getResponsibles(){
        $user=DB::table('user_roles')
            ->select('users.name','users.surname','employees.id as employee_id')
            ->leftJoin('employees','employees.user_id','=','user_roles.user_id')
            ->leftJoin('users','users.id','=','employees.user_id')
            ->where('user_roles.role_id','=',8)
            ->orWhere('user_roles.role_id',9)
            ->distinct()
            ->get();
        return $this->successResponse($user);
    }
    public function delete(Request $request,$id){
        $this->validate($request,[
            'company_id'=>'required',
            'office_id'=>'required',
            'employee_id'=>'required'
        ]);

        $employee=Employee::query()
            ->where('employees.id',$request->employee_id)
            ->get();
        foreach ($employee as $value){
            $user_id=$value['user_id'];
        }

        $user_role=UserRole::query()
            ->selectRaw('role_id')
            ->where('user_roles.user_id',$user_id)
            ->get();
        foreach ($user_role as $item) {
            $user_role_id=$item['role_id'];
        }
        if( $user_role_id==8 || $user_role_id==9) {
            Consent::query()
                ->where(['consents.id' => $id,
                    'consents.office_id' => $request->office_id
                ])
                ->delete();
            return response()->json(['message'=>'Deleted'],200);
        }
        return response()->json(['message'=>'Silinmeni yalniz Bas direktor ve Icraci direktor yerine yetire biler']);

    }

    public function allowConsent($consent_id)
    {
        $consent=Consent::query()->findOrFail($consent_id);
        $consent->status=2;
        $consent->save();
        return response()->json(['message'=>'Allowed by Director!'],200);
    }
    public function disAllowConsent($consent_id)
    {
        $consent=Consent::query()->findOrFail($consent_id);
        $consent->status=1;
        $consent->save();
        return response()->json(['message'=>'Disallowed by Director!'],200);
    }

}
