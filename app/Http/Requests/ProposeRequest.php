<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProposeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'demand_name'=>['required','string','min:2','max:77'],
            'company_name'=>['required','string','min:2','max:77'],
            'company_id'=>['required','integer'],
            'price'=>['required','integer'],
            'offer_file'=>['required','mimes:pdf','max:2048'],
            'description'=>['nullable','string'],


        ];
    }
    public function messages()
    {
        return parent::messages(); // TODO: Change the autogenerated stub

    }
}
