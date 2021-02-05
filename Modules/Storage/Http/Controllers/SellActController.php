<?php

namespace Modules\Storage\Http\Controllers;

use App\Traits\ApiResponse;
use App\Traits\Query;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Storage\Entities\Demand;
use Modules\Storage\Entities\SellAct;
use Modules\Storage\Entities\SellActDemand;

class SellActController extends Controller
{
    use ApiResponse, ValidatesRequests , Query;

    public function index(Request $request)
    {
        $this->validate($request, [
            'per_page' => ['nullable', 'integer', 'min:1'],
            'is_filter' => ['required', 'boolean']
        ]);

        $acts = SellAct::with(['supplier:id,name'])->company()->orderBy('id' , 'desc');

        if ($request->has('title'))
            $acts->where('title', 'like', $request->get('title') . "%");

        if ($request->has('title'))
            $acts->where('act_no', 'like', $request->get('act_no') . "%");

        if ($request->get('is_filter')) {
            $acts = $acts->limit(50)->get([
                'id', 'title', 'act_no'
            ]);
        } else {
            $acts = $acts->paginate($request->get('per_page'));
        }


        return $this->dataResponse($acts);

    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'act_no' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'demands' => ['nullable', 'array'],
            'price' => ['nullable', 'numeric'],
            'demands.*.id' => ['required_with:demands', 'integer'],
            'demands.*.amount' => ['required_with:demands', 'numeric'],
            'supplier_id' => ['required', 'integer']
        ]);

        $act = SellAct::create($request->only([
            'act_no',
            'title',
            'price',
            'supplier_id',
            'description',
            'company_id'
        ]));

        if ($request->has('demands')) {
            $data = [];
            foreach ($request->get('demands') as $demand)
                $data[] = [
                    'demand_id' => $demand['id'],
                    'amount' => $demand['amount'],
                    'sell_act_id' => $act->id
                ];
            SellActDemand::insert($data);
        }

        return $this->successResponse('ok');
    }

    public function show($id)
    {
        $acts = SellAct::with(['demands', 'demands.product' , 'supplier'])->company()
            ->where('id', $id)
            ->first();

        if (!$acts) return $this->errorResponse(trans('response.notFound'), 404);

        return $this->dataResponse($acts);

    }


    public function update(Request $request, $id)
    {
        $acts = SellAct::with(['demands', 'demands.products'])->company()
            ->where('id', $id)
            ->first(['id']);

        if (!$acts) return $this->errorResponse(trans('response.notFound'), 404);


        $acts->fill($request->only([
            'act_no',
            'title',
            'price',
            'sublayer_id',
            'description',
        ]))
            ->save();


        return $this->successResponse('ok');

    }

    public function delete($id)
    {
        $acts = SellAct::with(['demands', 'demands.products'])->company()
            ->where('id', $id)
            ->first(['id']);

        if (!$acts) return $this->errorResponse(trans('response.notFound'), 404);


        $acts->delete();

        return $this->successResponse('ok');
    }

    public function addDemand(Request $request, $id)
    {
        $this->validate($request, [
            'demands' => ['required', 'array'],
            'demands.*.id' => ['required_with:demands', 'integer'],
            'demands.*.amount' => ['required_with:demands', 'numeric'],
        ]);
        $data = [];
        foreach ($request->get('demands') as $demand)
            $data[] = [
                'demand_id' => $demand['id'],
                'amount' => $demand['amount'],
                'sell_act_id' => $id,
            ];

        if ($notExists = $this->companyInfo(
            $request->get('company_id'),
            [
                'sell_act_id' => $id,
                'demand_id' => array_column($data, 'demand_id')
            ]
        ))
            return $this->errorResponse($notExists);


        if (Demand::whereIn('id', array_column($data, 'demand_id'))->where('status' , '!=' , Demand::STATUS_ACCEPTED)->exists())
            return $this->errorResponse(trans('response.statusNotValid'));

        SellActDemand::insert($data);
        return $this->successResponse('ok');

    }
    public function updateDemand(Request $request, $id)
    {
        $this->validate($request, [
            'amount' => ['required', 'numeric'],
            'demand_id' => ['required', 'integer'],
        ]);

        SellActDemand::whereHas('sell_act' , function ($q) use ($id){
            $q->where('id' , $id)
                ->company();
        })
            ->where('id' , $request->get('demand_id'))
            ->exists();

        SellActDemand::where('id' , $request->get('demand_id'))
            ->update($request->only('amount'));
        return $this->successResponse('ok');

    }
    public function removeDemand(Request $request , $id)
    {
        $this->validate($request, [
            'demand_id' => ['required' ,'integer'],
        ]);

        $bool = SellAct::company()
            ->where('id' , $id)
            ->exists();
        if (!$bool)
            return $this->errorResponse(trans('response.sellActNotFound') , 404);

        SellActDemand::where('sell_act_id' , $id)
            ->where('demand_id' , $request->get('demand_id'))
            ->delete();


        return $this->successResponse('ok');
    }
}
