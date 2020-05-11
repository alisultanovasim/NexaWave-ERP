<?php

namespace Modules\Hr\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\Hr\Entities\Inventory;

class InventoryController extends Controller
{
    use ApiResponse, ValidatesRequests;

    protected $inventory;

    public function __construct(Inventory $inventory){
        $this->inventory = $inventory;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function index(Request $request): JsonResponse{
        $this->validate($request, [
            'per_page' => ['sometimes', 'integer'],
            'company_id' =>  ['required' , 'integer'],
        ]);

        $inventories = $this->inventory
        ->companyId($request->get('company_id'))
        ->with([
            'inventoryType:id,name'
        ])
        ->orderBy('id', 'desc')
        ->paginate($request->get('per_page'));

        return $this->dataResponse($inventories);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function create(Request $request): JsonResponse{
        $this->validate($request, $this->getInventoryRules($request));
        $this->saveInventory($request, $this->inventory);
        return $this->successResponse(trans('messages.saved'), 201);
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request, $id): JsonResponse{
        $this->validate($request, $this->getInventoryRules($request));
        $inventory = $this->findOrFailInventory($request, $id);
        $this->saveInventory($request, $inventory);
        return $this->successResponse(trans('messages.saved'), 200);
    }

    /**
     * @param Request $request
     * @param Inventory $inventory
     */
    private function saveInventory(Request $request, Inventory $inventory): void {
        $inventory->fill($request->only(
            [
                'inventory_type_id',
                'presenting_time',
                'delivery_time',
                'number',
                'name',
                'note'
            ]
        ))->save();
    }


    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function destroy(Request $request, $id): JsonResponse {
        $inventory = $this->findOrFailInventory($request, $id);
        $inventory->delete();
        return $this->successResponse(trans('messages.saved'), 200);
    }

    /**
     * @param Request $request
     * @param $id
     * @return mixed
     */
    private function findOrFailInventory(Request $request, $id){
        return $this->inventory->where('id', $id)
            ->companyId($request->get('company_id'))
            ->firstOrFail();
    }


    /**
     * @param Request $request
     * @return array|string[]
     */
    private function getInventoryRules(Request $request): array {
        return  [
            'inventory_type_id' => [
                'required',
                Rule::exists('inventory_types', 'id')->where('company_id', $request->get('company_id'))
            ],
            'name' => 'required|min:2|max:150',
            'number' => 'required|numeric',
            'note' => 'nullable|max:255',
            'presenting_time' => 'required|date',
            'delivery_time' => 'nullable|date'
        ];
    }
}
