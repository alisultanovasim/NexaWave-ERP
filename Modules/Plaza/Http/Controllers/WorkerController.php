<?php

namespace Modules\Plaza\Http\Controllers;


use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\Employee\Employee;
use Modules\Plaza\Entities\Card;
use Modules\Plaza\Entities\Office;
use Modules\Plaza\Entities\Role;
use Modules\Plaza\Entities\Worker;
use App\Traits\ApiResponse;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

/**
 * Class WorkerController
 * @package Modules\Plaza\Http\Controllers
 */
class WorkerController extends Controller
{
    use  ApiResponse, ValidatesRequests;

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function all(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'name' => 'sometimes|nullable|string|max:255',
            'has_card' => 'sometimes|required|in:1,0'
        ]);
        $workers = Worker::with(['card:id,alias', 'role:id,name', 'office:id,name,company_id'])
            ->whereHas('office', function ($q) use ($request) {
                $q->where("company_id","=",$request->input("company_id"));
        });

        if ($request->has('name') and $request->name) {
            $workers->where('name', 'like', "%{$request->name}%");
        }
        if ($request->has('has_card')) {
            if ($request->has_card) {
                $workers->whereNotNull('card');
            } else {
                $workers->whereNull('card');
            }
        }

        $workers = $workers->get();

        return $this->successResponse($workers);


    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function index(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'role_id' => 'sometimes|required|integer',
            'has_card' => 'sometimes|required|in:0,1'
        ]);


        $workers = Worker::with(['card:id,alias', 'role:id,name', 'office:id,name']);
        if ($request->has('office_id') and $request->get('office_id') != "null") {

            $check = Office::where('company_id', $request->company_id)->where('id', $request->office_id)->exists();
            if (!$check) return $this->errorResponse(trans('apiResponse.unProcess'));

            $workers->where("office_id", $request->office_id);
        }


        if ($request->has('name'))
            $workers->where('name', 'like', "%{$request->name}%");

        if ($request->has('role_id'))
            $workers->where('role_id', $request->role_id);

        if ($request->has('has_card')) {
            if ($request->has_card) {
                $workers->whereNotNull('card');
            } else {
                $workers->whereNull('card');
            }
        }

        $workers = $workers->get();


        return $this->successResponse($workers);

    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function show(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'office_id' => 'required|integer'
        ]);
        $check = Office::where('company_id', $request->company_id)->where('id', $request->office_id)->exists();
        if (!$check) return $this->errorResponse(trans('apiResponse.unProcess'));

        $workers = Worker::with(['card:id,alias', 'role:id,name', 'office:id,name'])->where('id', $id)->where("office_id", $request->office_id)->get();

        return $this->successResponse($workers);

    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'name' => 'required|min:2|max:255',
            'gender' => 'required|in:1,2',
            'office_id' => 'required|integer',
            'role_id' => 'required|integer',

            'description' => 'nullable|max:255',
            'card' => 'sometimes|required|integer'
        ]);
        try {
            $check = Office::where('id', $request->office_id)->where('company_id', $request->company_id)
                ->exists();
            if (!$check) return $this->errorResponse(trans('apiResponse.unProcess'));

            $worker = new Worker();
            if ($request->has('card')) {
                $check = Card::where('company_id', $request->company_id)->where('id', $request->card)->exists();
                if (!$check) return $this->errorResponse(trans('apiResponse.cardNotFound'));

            }
            $worker->fill($request->only('name', 'description', 'office_id', 'role_id', 'gender', 'card'));
            $worker->save();
            return $this->successResponse('OK');
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1452) {
                if (preg_match("/\(\`[a-z\_]+\`\)/", $e->errorInfo[2], $find)) {
                    $info = substr($find[0], 2, -2);
                    return $this->errorResponse([$info => "does not exist"], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
            if ($e->errorInfo[1] == 1062) {
                if (preg_match("/offices_(.*)_unique/", $e->getMessage(), $find)) {
                    return $this->errorResponse([$find[1] => trans('apiResponse.alreadyExists')], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'name' => 'sometimes|required|min:2|max:255',
            'gender' => 'sometimes|required|in:1,2',
            'office_id' => 'required|integer',
            'description' => 'nullable|max:255',
            'role_id' => 'sometimes|required|integer',
            'card' => 'sometimes|nullable|required|integer'
        ]);
        $arr = $request->only('name', 'description', 'office_id', 'role_id', 'gender');
        try {
            $check = Office::where('id', $request->office_id)->where('company_id', $request->company_id)
                ->exists();
            if (!$check) return $this->errorResponse(trans('apiResponse.unProcess'));

            if ($request->has('card')) {
                if ($request->card) {
                    $check = Card::where('company_id', $request->company_id)->where('id', $request->card)->exists();
                    if (!$check) return $this->errorResponse(trans('apiResponse.cardNotFound'));
                    $arr['card'] = $request->card;
                } else {
                    $arr['card'] = null;
                }

            }

            $check = Worker::where('id', $id)
                ->where('office_id', $request->office_id)
                ->update($arr);
            if (!$check) return $this->errorResponse(trans('apiResponse.unProcess'));
            return $this->successResponse('OK');
        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function delete(Request $request, $id)
    {
        $this->validate($request, [

            'company_id' => 'required|integer',
            'office_id' => 'required|integer',
        ]);
        try {
            $check = Office::where('id', $request->office_id)->where('company_id', $request->company_id)
                ->exists();
            if (!$check) return $this->errorResponse(trans('apiResponse.unProcess'));
            $check = Worker::where('id', $id)
                ->where('office_id', $request->office_id)
                ->delete();
            if (!$check) return $this->errorResponse(trans('apiResponse.unProcess'));
            return $this->successResponse('OK');

        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function getRoles(Request $request)
    {
        $this->validate($request, [

            'company_id' => 'required|integer',
            'office_id' => 'required|integer',
        ]);
        try {
            $check = Office::where('company_id', $request->company_id)->where('id', $request->office_id)->exists();
            if (!$check) return $this->errorResponse(trans('apiResponse.unProcess'));
            $roles = Role::where('office_id', $request->office_id)->where('company_id', $request->company_id)->get(['id', 'name']);
            return $this->successResponse($roles);

        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function showRole(Request $request, $id)
    {
        $this->validate($request, [

            'company_id' => 'required|integer',
            'office_id' => 'required|integer',
        ]);
        try {
            $check = Office::where('company_id', $request->company_id)->where('id', $request->office_id)->exists();
            if (!$check) return $this->errorResponse(trans('apiResponse.unProcess'));
            $roles = Role::where('id', $id)->where('office_id', $request->office_id)->where('company_id', $request->company_id)->get(['id', 'name']);
            if (!$roles) return $this->errorResponse(trans('apiResponse.roleNotFound'));
            return $this->successResponse($roles);

        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function storeRole(Request $request)
    {
        $this->validate($request, [

            'company_id' => 'required|integer',
            'office_id' => 'required|integer',
            'name' => 'required|min:2,max:255'
        ]);
        try {
            $check = Office::where('company_id', $request->company_id)->where('id', $request->office_id)->exists();
            if (!$check) return $this->errorResponse(trans('apiResponse.unProcess'));
            Role::create($request->only('office_id', 'company_id', 'name'));
            return $this->successResponse('OK');

        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }


    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function updateRole(Request $request, $id)
    {
        $this->validate($request, [

            'company_id' => 'required|integer',
            'office_id' => 'required|integer',
            'name' => 'required|min:2,max:255'
        ]);
        try {
            $role = Role::where('id', $id)->where('office_id', $request->office_id)->where('company_id', $request->company_id)->update([
                'name' => $request->name
            ]);

            if (!$role) return $this->errorResponse(trans('apiResponse.unProcess'));
            return $this->successResponse('OK');
        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function deleteRole(Request $request, $id)
    {
        $this->validate($request, [

            'company_id' => 'required|integer',
            'office_id' => 'required|integer',
        ]);
        try {
            $role = Role::where('id', $id)->where('office_id', $request->office_id)->where('company_id', $request->company_id)->delete();
            if (!$role) return $this->errorResponse(trans('apiResponse.unProcess'));
            return $this->successResponse('OK');
        } catch (\Exception $exception) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function searchworker(Request $request,$keyword){
        $this->validate($request,[
            'company_id'=>'required',
            'position_id' => 'nullable|numeric'
        ]);

        $employees = Employee::query()->with('user')
            ->whereHas('user',function ($builder) use ($keyword){
                $builder->where('name','like','%'.$keyword.'%')
                    ->orWhere('surname','like','%'.$keyword.'%');
            })
            ->where(function ($q) use ($request) {
                $q->doesntHave("contracts")
                    ->orWhereHas('contracts', function ($query) use ($request) {
                        if ($request->get('department_id')) {
                            $query->where('department_id', $request->get('department_id'));
                        }
                        if ($request->get('sector_id')) {
                            $query->where('sector_id', $request->get('sector_id'));
                        }
                        if ($request->get('section_id')) {
                            $query->where('section_id', $request->get('section_id'));
                        }
                        if ($request->get('position_id')) {
                            $query->where(['position_id' => $request->get('position_id')]);
                        }
                        $query->where(function ($query) {
                            $query->where('end_date', '>', Carbon::now());
                            $query->orWhere('end_date', null);
                        });
                    });
            })
            ->where('company_id', $request->input('company_id'))
            ->get();

//        $p=User::all();
//
//        $full_names = $p->map(function ($person) {
//            return $person->fullname;
//        });
//        $full_names = $p->map->fullname;

        if (!count($employees)>0){
            return response()->json('Not found',404);
        }

        return response()->json($employees,200);
    }
    public function searchcard(Request $request,$key){
        $this->validate($request,[
            'company_id'=>'required'
        ]);
        $alias=Card::query()
            ->where('alias','like','%'.$key.'%')
            ->get('alias');
        if ($alias==null){
            return $this->errorResponse(trans('apiResponse.notFound'),Response::HTTP_NOT_FOUND);
        }
        return $this->dataResponse($alias,Response::HTTP_OK);
    }
}
