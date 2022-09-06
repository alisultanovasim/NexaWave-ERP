<?php

namespace Modules\Storage\Http\Controllers;

use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Modules\Hr\Entities\Employee\Employee;
use Modules\Plaza\Entities\Kind;
use Modules\Storage\Entities\Demand;
use Modules\Storage\Entities\DemandAssignment;
use Modules\Storage\Entities\Product;
use Modules\Storage\Entities\ProductColor;
use Modules\Storage\Entities\ProductKind;
use Modules\Storage\Entities\ProductModel;
use Modules\Storage\Entities\ProductTitle;
use Modules\Storage\Entities\Unit;

class DemandController extends Controller
{
    use ApiResponse, ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request, [
            'per_page' => ['nullable', 'integer'],
//            'product_id' => ['nullable', 'integer'],
//            'employee_id' => ['required', 'integer']
        ]);
        $per_page=$request->per_page ?? 10;

        $demandCreatedByUser=Demand::query()
            ->where([
                'employee_id'=>$this->getEmployeeId($request->company_id),
                'company_id'=>$request->company_id,
                'status'=>Demand::STATUS_WAIT
            ])
            ->paginate($per_page);
//        array_merge($demands->toArray(),$demandFromDemandsTable->toArray());
//        $demands=DemandAssignment::query()
//            ->where('employee_id',Auth::user()->id)
//            ->with([
//                'demand:id,name,employee_id,price_approx'
//                ])
//            ->paginate($per_page);
        return $this->dataResponse(['createdByUserDemands'=>$demandCreatedByUser],200);



//        $demands = Demand::with([
//            'product:id,product_mark,product_model,amount,kind_id,title_id,unit_id',
//            'product.kind',
//            'product.model',
//            'product.unit:id,name',
//            'product.title:id,name',
//            'employee:id,user_id,tabel_no',
//            'employee.user:id,name,surname',
//        ])
//            ->withCount([
//                'assignment as assigned_tome' => function($q) use ($request){
//                    $q->whereHas('items' , function ($q) use ($request){
//                        $q->where('employee_id' , $request->get('auth_employee_id'));
//                    });
//                }
//            ])
//            ->where('company_id', $request->get('company_id'));
//
//        if ($request->has('product_id'))
//            $demands->where('product_id', $request->get('product_id'));
//
//        if ($request->has('employee_id'))
//            $demands->where('employee_id', $request->get('employee_id'));
//
//        if ($request->has('want_from'))
//            $demands->where('want_till', ">=", $request->get('employee_id'));
//
//        if ($request->has('want_to'))
//            $demands->where('want_till', "<=", $request->get('employee_id'));
//
//        if ($request->has('status'))
//            $demands->where('status', $request->get('status'));
//
//        $demands = $demands->orderBy('id', 'desc')->paginate($request->get('per_page'), [
//            'id', 'product_id', 'description', 'created_at', 'want_till', 'employee_id'
//        ]);

//        return $this->successResponse($demands);
    }

    public function directedToUserDemandList(Request $request): \Illuminate\Http\JsonResponse
    {
        $per_page=$request->per_page ?? 10;
        $department_id=DB::table('employee_contracts')
//            ->leftJoin('departments','departments.id','=','employee_contracts.department_id')
            ->where('employee_contracts.employee_id',$this->getEmployeeId($request->company_id))
            ->distinct()
            ->get('department_id')->toArray();

        switch ($department_id[0]->department_id){
            case 1:
                $progress_status=4;
                break;
            case 8:
                $progress_status=3;
                break;
            case 15:
                $progress_status=2;
        }

        $demands=DemandAssignment::query()
            ->where([
                'employee_id'=>$this->getEmployeeId($request->company_id)
            ])
            ->with([
                        'demand.product.model',
                    ])
            ->whereHas('demand',function ($q1) use ($progress_status){
                $q1->where([
                    'status'=>Demand::STATUS_WAIT,
                    'progress_status'=>$progress_status]);
            })
            ->paginate($per_page);

        return $this->dataResponse($demands,200);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->validate($request,[
            'name' => ['required', 'string'],
            'demand_description' => ['nullable', 'string'],
            'amount' => ['required', 'integer'],
            'price_approx' => ['required', 'integer'],
            'productInfo'=>['required','array'],
            'productInfo.*.storage_id'=>['required','integer',Rule::exists('storages','id')],
            'productInfo.*.unit_id'=>['required','integer',Rule::exists('units','id')],
            'productInfo.*.title' => ['required', 'string', 'min:1'],//
            'productInfo.*.kind' => ['required', 'string', 'min:1'],//
            'productInfo.*.product_mark' => ['required','string'],
            'productInfo.*.model' => ['required', 'string','min:1'],
        ]);


        //todo check title and kind_id
        $title = ProductTitle::firstOrCreate(
            [
                'name'   => $request->productInfo[0]['title'],
            ],
            [
                'name'     => $request->productInfo[0]['title'],
                'company_id' => $request->get('company_id'),
            ]
            );


        $kind=ProductKind::firstOrCreate(
            [
                'name'   => $request->productInfo[0]['kind'],
                'title_id'   => $title->id,
            ],
            [
                'name'     => $request->productInfo[0]['kind'],
                'company_id' => $request->get('company_id'),
                'unit_id' =>1,
                'title_id' => $title->id,
            ]
        );

            $model=ProductModel::query()->firstOrCreate(
            [
                'name'   => $request->productInfo[0]['model'],
                'kind_id'   => $kind->id,
            ],
            [
                'name'     => $request->productInfo[0]['model'],
                'kind_id'     => $kind->id,
            ]
        );
        $productFind=Product::query()
            ->where([
                'model_id'=>$model->id,
                'product_mark'=>$request->productInfo[0]['product_mark']
            ]);

            $productCheck=$productFind->get();
         if (!count($productCheck)) {
                $product = new Product();
                $product->title_id = $title->id;
                $product->kind_id = $kind->id;
                $product->initial_amount = $request->amount;
                $product->storage_id = $request->productInfo[0]['storage_id'];
                $product->company_id = $request->company_id;
                $product->state_id = 1;
                $product->unit_id = $request->productInfo[0]['unit_id'];
                $product->status = Product::STATUS_ACTIVE;
                $product->model_id = $model->id;
                $product->product_mark = $request->productInfo[0]['product_mark'];
                $product->save();
            }
            else{
                    $productFind->increment('initial_amount',1);
                    $product=$productFind->get();
            }

        $employee_id = Employee::where([
            ['user_id' , Auth::id()],
            ['company_id' , $request->get('company_id')]
        ])->first(['id']);

        $demand=Demand::create([
            'name' => $request->name,
            'price_approx' => $request->price_approx,
            'description' => $request->description,
            'product_id' => $product[0]['id'],
            'amount' => $request->amount,
            'employee_id' => $employee_id->id,
            'company_id' => $request->company_id,
            'status' =>Demand::STATUS_WAIT,
            'progress_status' =>1
        ]);


        return $this->successResponse($demand);
    }

    public function show(Request $request, $id)
    {
        $demands = Demand::with([
            'product',
            'product.color' ,
            'product.state' ,
            'product.kind',
            'product.unit',
            'product.title',
            'employee',
            'assignment',
            'assignment.employee',
            'assignment.items',
            'product.model',
            'employee.user',
            'product.buy_from_country' ,
            'product.made_in_country'
        ])
            ->where('company_id', $request->get('company_id'))
            ->where('id', $id)
            ->first();
        if (!$demands)
            return $this->errorResponse(trans('response.demandNotFound'), 404);
        return $this->successResponse($demands);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, array_merge([
            'demand_description' => ['nullable', 'string'],
            'want_till' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'update_product' => ['required', 'nullable']
        ], ProductController::getUpdateRules()));

        $demand = Demand::where('company_id', $request->get('company_id'))
            ->where('id', $id)
            ->where('employee_id', $request->get('auth_employee_id'))
            ->first(['id', 'status', 'product_id']);

        if (!$demand)
            return $this->errorResponse(trans('response.demandNotFound'), 404);

        if ($demand->status != Demand::STATUS_WAIT)
            return $this->errorResponse(trans('response.cannotUpdateStatusError'), 422);


        if ($request->get('update_product')) {
            //todo check title and kind_id
            Product::where('id', $demand->product_id)
                ->update($request->only([
                    'unit_id',
                    'less_value',
                    'quickly_old',
                    'title_id',
                    'kind_id',
                    'state_id',
                    'description',
                    'amount',
                    'storage_id',
                    'product_model',
                    'product_mark',
                    'product_no',
                    'color_id',
                    'main_funds',
                    'inv_no',
                    'exploitation_date',
                    'size',
                    'made_in_country',
                    'buy_from_country',
                    'make_date',
                    "company_id",
                ]));
        }

        $demand->update($request->only([
            'description',
            'want_till'
        ]));

        return $this->successResponse('ok');


    }

    public function delete(Request $request,$id)
    {

        $demand = Demand::where('company_id', $request->get('company_id'))
            ->where('id', $id)
            ->first(['id', 'status', 'product_id','employee_id']);

        if (!isset($demand))
            return $this->errorResponse(trans('response.demandNotFound'), 404);
        if ($demand->status != Demand::STATUS_WAIT)
            return $this->errorResponse(trans('response.demandStatusIsNotWait'), 404);
        if ($demand->employee_id!=$this->getEmployeeId($request->company_id))
            return $this->errorResponse(trans('response.userDontHaveAnAccessToDelete'),422);
        DB::beginTransaction();
        $product=Product::query()->where('id', $demand->product_id)->get();
        if ($product){
            if ($product[0]['initial_amount']>1)
                Product::query()->where('id', $demand->product_id)->decrement('initial_amount',1);
            else if ($product[0]['initial_amount']==1)
            {
                Product::query()->where('id', $demand->product_id)->decrement('initial_amount',1);
                Product::query()->where('id', $demand->product_id)->delete();
            }
        }
        Demand::query()->where('id',$id)->delete();

        DB::commit();
        return $this->successResponse('ok');

    }

    public function reject($id): \Illuminate\Http\JsonResponse
    {
            $demand=Demand::query()->where('id',$id)->first(['id']);
            if (!$demand){
                return $this->errorResponse('There is not exist demand',404);
            }
            $demand->update(['status'=>Demand::STATUS_REJECTED]);
            return $this->successResponse('The demand rejected!',200);
    }

    public function confirm($demandId): \Illuminate\Http\JsonResponse
    {
        $demand=Demand::query()->findOrFail($demandId);

        $user=User::query()
            ->where('id',Auth::id())
            ->with('roles')
            ->get();
        $roleIds=[];
        foreach ($user[0]['roles'] as $role){
            array_push($roleIds,$role['id']);
        }
        if (in_array(8,$roleIds)){
            if ($demand->status==1){
                $demand->update(['status'=>Demand::STATUS_CONFIRMED]);
                $demand->increment('progress_status',1);
                $message=trans('response.theDemandConfirmed');
                $code=200;
            }
            else{
                $message=trans('response.theDemandAlreadyAccepted');
                $code=400;
            }
        }
        else{
            return $this->errorResponse(['message'=>trans('response.theUserDontHaveAccess')],422);
        }
      return $this->successResponse($message,$code);
    }

    public function getEmployeeId($companyId)
    {
        return Employee::query()
            ->where([
                'user_id'=>Auth::id(),
                'company_id'=>$companyId
            ])
            ->first()['id'];
    }
}
