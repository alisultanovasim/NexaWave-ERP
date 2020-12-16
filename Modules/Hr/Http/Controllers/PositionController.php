<?php


namespace Modules\Hr\Http\Controllers;


use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Hr\Entities\Positions;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class PositionController extends Controller
{
    use ApiResponse, ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request , [
            'company_id' => ['required' , 'integer'],
            'paginateCount' => ['sometimes' , 'required' , 'integer'],
        ]);
        $positions = Positions::where(function ($q) use ($request){
//            $q->whereNull('company_id');
                $q->where('company_id' , $request->get('company_id'));
        });
        if ($request->get('is_filter'))
            $positions = $positions->get();
        else
            $positions = $positions->paginate($request->get('paginateCount'));
        return $this->dataResponse($positions);
    }

    public function create(Request $request)
    {
        $error = $this->validateRequest($request->all());
        if ($error)
            return $this->errorResponse($error, 422);
        try
        {
            DB::beginTransaction();
            $saved = true;
            $position = new Positions();
            $position->fill([
                'name' => $request->get('name'),
                'short_name' => $request->get('short_name'),
                'note' => $request->get('note'),
                'is_closed' => $request->get('is_closed'),
                'company_id' => $request->get('company_id'),
                'position' => $request->get('position')
            ]);

            if ($position->getAttribute('is_closed'))
                $position->fill([
                    'closing_date' => $request->get('closing_date')
                ]);
            $position->save();

            DB::commit();
        }
        catch (\Exception $exception)
        {
            $saved = false;
            DB::rollBack();
        }
        return $saved
            ? $this->successResponse(['position_id' => $position->id], 201)
            : $this->errorResponse(trans('messages.not_saved'));
    }

    public function update(Request $request, $id)
    {
        $error = $this->validateRequest($request->all());
        if ($error)
            return $this->errorResponse($error, 422);
        try
        {
            DB::beginTransaction();
            $saved = true;
            $position = Positions::where('id', $id)->where(function ($query) use ($request){
//                $query->whereNull('company_id')
                $query->where('company_id' , $request->get('company_id'));
            })->exists();
            if (!$position)
                return $this->errorResponse(trans('messages.not_found'), 404);
            Positions::where('id', $id)->update([
                'name' => $request->get('name'),
                'short_name' => $request->get('short_name'),
                'note' => $request->get('note'),
                'closing_date' => $request->get('closing_date'),
                'is_closed' => $request->get('is_closed'),
                'position' => $request->get('position')
            ]);
            DB::commit();
        }
        catch (\Exception $e)
        {
            $saved = false;
            DB::rollBack();
        }
        return $saved
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }

    public function destroy(Request $request, $id)
    {
        return Positions::where('id', $id)->where('company_id' , $request->get('company_id'))->delete()
            ? $this->successResponse(trans('messages.saved'))
            : $this->errorResponse(trans('messages.not_saved'));
    }

    protected function validateRequest($input){
        $validationArray = [
            'name' => 'required|max:255',
            'short_name' => 'required|max:100',
            'note' => 'max:1000',
            'closing_date' => 'date',
            'is_closed' => 'boolean',
            'company_id' => ['required' , 'integer'],
            'position' => 'nullable|numeric'
        ];
        $validator = \Validator::make($input, $validationArray);

        if($validator->fails())
            return $validator->errors();
        return null;
    }
}
