<?php

namespace Modules\Hr\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Hr\Entities\Paragraph;
use Modules\Hr\Entities\ParagraphField;

class ParagraphController extends Controller
{
    use ValidatesRequests, ApiResponse;

    public function index(Request $request)
    {
        $this->validate($request , [
            'load_details' => ['nullable' , 'boolean']
        ]);
        $paragraphs = Paragraph::query();

        if ($request->get('load_details')){
            $paragraphs->with('fields');
        }
        $paragraphs = $paragraphs->get();
        return $this->successResponse($paragraphs);
    }

    public function show(Request $request, $id)
    {
        return $this->successResponse(
            Paragraph::with('fields')
                ->findOrFail($id)
        );
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => ['nullable', 'string', 'max:255'],
            'can_update' => ['nullable', 'boolean'],
            'fields' => ['nullable', 'array'],
            'fields.*.field' => ['required_with:fields', 'string', 'max:255'],
            'fields.*.name' => ['required_with:fields', 'string', 'max:255']
        ]);
        if (!$request->only(['name' , 'can_update' , 'fields']))
            return $this->errorResponse(trans('response.nothingToUpdate') , 400);

        if (
        !Paragraph::where('id', $id)->exists()
        ) {
            return $this->successResponse(trans('response.notFound'), 404);
        }



        Paragraph::where('id'  , $id)->update($request->only(['name' , 'can_update']));


        if ($request->has('fields')){
            ParagraphField::where('id', $id)->delete();


            $data = [];

            foreach ($request->get('fields') as $v) {
                array_push($data, [
                    'paragraph_id' => $id,
                    'field' => $v['field'],
                    'name' => $v['name'],
                ]);
            }


            ParagraphField::insert($data);
        }

        return $this->successResponse('ok');

    }


    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
            'can_update' => ['required', 'boolean'],
            'fields' => ['required', 'array'],
            'fields.*.field' => ['required_with:fields', 'string', 'max:255'],
            'fields.*.name' => ['required_with:fields', 'string', 'max:255']
        ]);

        $p = Paragraph::create($request->only(['name' , 'can_update']));


        $data = [];

        foreach ($request->get('fields') as $v) {
            array_push($data, [
                'paragraph_id' => $p->id,
                'field' => $v['field'],
                'name' => $v['name'],
            ]);
        }

        ParagraphField::insert($data);

        return $this->successResponse('ok');

    }
}
