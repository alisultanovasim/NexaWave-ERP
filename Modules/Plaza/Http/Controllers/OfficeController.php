<?php


namespace Modules\Plaza\Http\Controllers;


use App\Models\User;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Hash;
use Modules\Plaza\Entities\Contact;
use Modules\Plaza\Entities\Contract;
use Modules\Plaza\Entities\Document;
use Modules\Plaza\Entities\Floor;
use Modules\Plaza\Entities\Location;
use Modules\Plaza\Entities\Office;
use Modules\Plaza\Entities\OfficeUser;
use Modules\Plaza\Entities\Worker;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Illuminate\Routing\Controller;
class OfficeController extends Controller
{
    use ApiResponse  , ValidatesRequests;

    public function index(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'per_page' => 'sometimes|required|integer',
            'floor_id' => 'sometimes|required|integer',
            'name' => 'sometimes|required|string|max:255',
            'min_amount' => 'sometimes|required|numeric',
            'max_amount' => 'sometimes|required|numeric',
            'trash' => 'sometimes|integer|in:0,1' ,
            'order_by' => 'sometimes|required|string|max:255',
            'direction' => 'sometimes|required|in:desc,asc'
        ]);
        try {

//with('location', 'location.floor:id,number');

            $offices = Office::where('company_id', $request->company_id);

            if ($request->has('floor_id')) {
                $offices->whereHas('location', function ($q) use ($request) {
                    $q->where('floor_id', $request->floor_id);
                });
            }
            if ($request->has('min_amount')) {
                $offices->where("per_month", ">=", $request->min_amount);
            }
            if ($request->has('max_amount')) {
                $offices->where("per_month", "<=", $request->max_amount);
            }
            if ($request->has('name')) {
                $offices->where("offices.name", "like", $request->name . "%");
            }
            if ($request->has('trash')) {
                if (!$request->trash) $offices->withTrashed();
                else $offices->onlyTrashed();
            }
            if ($request->has('order_by')){
                $direction = $request->direction ?? 'DESC' ;
                $offices->orderBy($request->order_by, $direction);
            }else
                $offices->orderBy('id', 'DESC');

            if (!$request->filter){
                $columns = ['*'];
                $offices->with('location', 'location.floor:id,number');
            }else
                $columns = ['id', 'name'];

            $offices = $offices->paginate($request->per_page ?? 10 , $columns);

            return $this->successResponse($offices);

        }
        catch (QueryException $ex){
            if($ex->errorInfo[1] == 1054){
                return $this->errorMessage(['order_by' => ['not valid data']], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);

        }
        catch (Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'name' => 'required|min:3|max:255',
//            'description' => 'sometimes|required',

            'email' => 'sometimes|required|array',
            'email.*.contact' => 'required_with:email|email',
            'email.*.name' => 'sometimes|required|min:2|max:255',

            'entity' => 'required|integer|in:1,2',

            'voen' => 'sometimes|required',

            'contract' => 'sometimes|required|mimes:pdf,doc,docx',

            'phone' => 'sometimes|required|array',
            'phone.*.name' => 'sometimes|required|min:2|max:255',
            'phone.*.contact' => 'required_with:phone|regex:/^\+?[0-9]{12}$/',

            'image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,svg',
            'start_time' => 'required|date|date_format:Y-m-d',
            'month_count' => 'required|integer',
            'payed_month_count' => 'sometimes|required|integer|lte:month_count',

            'location' => 'required|array',

            'location.*.size' => 'required|numeric',
            'location.*.floor_id' => 'required|integer',
            'location.*.number' => 'sometimes|required|integer',
            'location.*.schema' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,svg,pdf,docx,doc',
            'per_month' => 'required|numeric',

            'agree_at' => 'sometimes|required|date|date_format:Y-m-d',

            'documents' => 'sometimes|required|array',
            'documents.*' => 'sometimes|required|mimes::jpeg,png,jpg,gif,svg,pdf,docx,doc,txt,xls,xlsx'
        ]);
        try {
            DB::beginTransaction();

            $office = new Office();
            $office->fill($request->only('agree_at','entity', 'voen', 'per_month', 'company_id', 'name', 'description', 'start_time', 'month_count', 'payed_month_count'));

            if ($request->hasFile('image'))
                $office->image = $this->uploadImage($request->company_id, $request->image);


            $office->save();

            if ($request->hasFile('contract')) {
                Contract::create([
                    'office_id' => $office->id,
                    'contract' => $this->uploadImage($request->company_id, $request->contract, 'contracts'),
                    'versions' => "[]"
                ]);
            }

            $contacts = [];
            if ($request->has('email'))
                foreach ($request->email as $email)
                    $contacts[] = [
                        'office_id' => $office->id,
                        'name' =>isset($email['name'])?$email['name']:null,
                        'contact' => $email['contact'],
                        'type' => config('plaza.office.contact.email')
                    ];

            if ($request->has('phone'))
                foreach ($request->phone as $phone)
                    $contacts[] = [
                        'office_id' => $office->id,
                        'name' => isset($phone['name'])?$phone['name']:null,
                        'contact' => $phone['contact'],
                        'type' => config('plaza.office.contact.phone'),
                    ];

            if ($contacts) DB::table('offices_contacts')->insert($contacts);


            $locations = [];
            foreach ($request->location as $location) {
                $check = Floor::where('id', $location['floor_id'])
                    ->where(DB::raw('common_size - sold_size'), ">=", $location['size'])
                    ->update([
                        'sold_size' => DB::raw("sold_size + {$location['size']}")
                    ]);
                if (!$check) {
                    DB::rollBack();
                    return $this->errorResponse(trans('apiResponse.sizeError'));
                }
                $arr = [
                    'floor_id' => $location['floor_id'],
                    'size' => $location['size'],
                    'number' => isset($location['number'])?$location['number']:null,
                    'office_id' => $office->id,

                ];

                if (isset($location['schema'])) {
                    $arr['schema'] = $this->uploadImage($request->company_id, $location['schema'], 'locations');
                }
                $locations[] = $arr;
            }

            Location::insert($locations);



            if ($request->has('documents')){
                $documents = [];

                foreach ($request->documents as $document){
                    $documents[] = [
                        'office_id' => $office->id,
                        'url' => $this->uploadImage($request->company_id, $document, 'documents')
                    ];
                }

                Document::insert($documents);

            }


            DB::commit();
            return $this->successResponse("OK");
        } catch (QueryException $e) {
            DB::rollBack();
            if ($e->errorInfo[1] == 1452) {
                if (preg_match("/\(\`[a-z\_]+\`\)/", $e->errorInfo[2], $find)) {
                    $info = substr($find[0], 2, -2);
                    return $this->errorResponse([$info => "does not exist"], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
            if ($e->errorInfo[1] == 1062) {
                if (preg_match("/offices_(.*)_unique/", $e->getMessage(), $find)) {
                    if (preg_match("/number/", $find[1], $find)) {
                        return $this->errorResponse(["number" => trans('apiResponse.alreadyExists')], Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                    return $this->errorResponse([$find[1] => trans('apiResponse.alreadyExists')], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function uploadImage($company_id, $file, $str = 'offices')
    {
        if ($file instanceof UploadedFile) {

//            $filename = time() . rand(0, 100) . "." . $file->extension();
//
//            $file->move(base_path('public/' . $dir . '/' . $str), $filename);
//
//            return $dir . '/' . $str . "/" . $filename;
            $filename = time() . rand(0, 100) . "." . $file->extension();
            $file->move(base_path("public/documents/$company_id/$str"), $filename);
            return "$company_id/$str/$filename";
        }

        return null;
    }

    public function show(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer'
        ]);
        try {
            $office = Office::with([ 'contract', 'contact' , 'documents' ])->where([
                'id' => $id,
                'company_id' => $request->company_id
            ])->first();

            if (!$office)
                return $this->errorResponse(trans('apiResponse.unProcess'));
            $office->load(['location', 'location.floor:id,number']);
            $office->workers_count = DB::table('office_workers')
                ->where("office_id", $office->id)->count();
            return $this->successResponse($office);
        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'name' => 'sometimes|required|min:3|max:255',

            'agree_at' => 'sometimes|required|date|date_format:Y-m-d',

            'image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,svg',
            'schema' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,svg',
            'start_time' => 'sometimes|required|date|date_format:Y-m-d',
            'month_count' => 'sometimes|required|integer',
            'contract' => 'sometimes|required|mimes:pdf,doc,docx',
            'voen' => 'sometimes|required',
            'payed_month_count' => 'sometimes|required|integer',


            'email' => 'sometimes|required|array',
            'email.*.contact' => 'sometimes|required|email',
            'email.*.name' => 'sometimes|required|min:2|max:255',

            'phone' => 'sometimes|required|array',
            'phone.*.name' => 'sometimes|required|min:2|max:255',
            'phone.*.contact' => 'sometimes|required|regex:/^\+?[0-9]{12}$/',



            'documents' => 'sometimes|required|array',
            'documents.*' => 'sometimes|required|mimes::jpeg,png,jpg,gif,svg,pdf,docx,doc,txt,xls,xlsx'
        ]);

        $company_id = $request->company_id;
        try {
            $office = Office::where([
                'id' => $id,
                'company_id' => $company_id
            ])->first();
            if (!$office)
                return $this->errorResponse('apiResponse.officeNotFound');
            if ($request->has('payed_month_count')) {
                if ($request->has('month_count'))
                    if ($request->payed_month_count > $request->month_count) return $this->errorResponse(['payed_month_count' => 'less than month_count']);
                    else {
                        if ($request->payed_month_count > $office->month_count) return $this->errorResponse(['payed_month_count' => 'less than month_count']);
                    }
            }


            $office->fill($request->only('name', 'description', 'start_time', 'end_time', 'voen', 'payed_month_count' , 'agree_at'));

            if ($request->hasFile('image')) {
                $filename = $this->uploadImage($company_id, $request->image);
                if ($office->image)
                    File::delete(base_path('public/' . $request->company_id . "/floors/" . $office->avatar));
                $office->image = $filename;
            }

            if ($request->hasFile('schema')) {
                $filename = $this->uploadImage($company_id, $request->schema);
                if ($office->schema)
                    File::delete(base_path('public/' . $request->company_id . "/floors/" . $office->schema));
                $office->schema = $filename;
            }

            if ($request->hasFile('contract')) {
                $contract = Contract::where('office_id', $id)->first();
                if (!$contract) {
                    Contract::create([
                        'office_id' => $office->id,
                        'contract' => $this->uploadImage($request->company_id, $request->contract, 'contracts'),
                        'versions' => "[]"
                    ]);


                } else {
                    $versions = json_decode($contract->versions, true);
                    $addingVersions = [
                        "contract" => $contract->contract
                    ];
                    $versions = $versions ?? [];
                    array_push($versions, $addingVersions);
                    $contract->contract = $this->uploadImage($request->company_id, $request->contract, 'contract');
                    $contract->versions = json_encode($versions);
                    $contract->save();
                }
            }

            DB::table('offices_contacts')->where('office_id', $office->id)->delete();
            $contacts = [];
            if ($request->has('email'))
                foreach ($request->email as $email)
                    $contacts[] = [
                        'office_id' => $office->id,
                        'name' => $email['name'],
                        'contact' => $email['contact'],
                        'type' => config('plaza.office.contact.email')
                    ];

            if ($request->has('phone'))
                foreach ($request->phone as $phone)
                    $contacts[] = [
                        'office_id' => $office->id,
                        'name' => $phone['name'],
                        'contact' => $phone['contact'],
                        'type' => config('plaza.office.contact.phone'),

                    ];

            if ($contacts) DB::table('offices_contacts')->insert($contacts);



            if ($request->has('documents')){
                $documents = [];

                foreach ($request->documents as $document){
                    $documents[] = [
                        'office_id' => $office->id,
                        'url' => $this->uploadImage($request->company_id, $document, 'documents')
                    ];
                }

                Document::insert($documents);

            }

            $office->save();
            return $this->successResponse("OK");
        } catch (Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function delete(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
        ]);
        $company_id = $request->company_id;
        try {
            DB::beginTransaction();
            $office = Office::where([
                'id' => $id,
                'company_id' => $company_id
            ])->first();

            if (!$office)
                return $this->errorResponse('apiResponse.officeNotFound');

            $history = json_decode($office->history ?? '[]');

            $lastHistory = [
                'locations' => Location::where('office_id', $id)->get(),
                'contacts' => Contact::where('office_id', $id)->get(),
                'workers' => Worker::where('office_id')->get(),
                'data' => $office->only(['image', 'entity', 'start_at', 'month_count', 'payed_month_count', 'voen', 'description', 'name'])
            ];
            array_push($history, $lastHistory);
            $office->history = $history;
            $office->fill([
                'start_time' => null,
                'month_count' => 0,
                'payed_month_count' => 0
            ]);
            $office->delete();

            Location::where('office_id', $id)->delete();
            Contact::where('office_id', $id)->delete();
            Worker::where('office_id')->delete();

//            DB::statement("DELETE FROM offices_locations WHERE offices_locations.office_id = {$id} ; DELETE FROM office_workers WHERE office_workers.office_id = {$id} ; DELETE FROM offices_contacts WHERE offices_contacts.office_id = {$id} ; ");

            DB::commit();
            return $this->successResponse("OK");
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * start locations
     */
    public function locationAdd(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'floor_id' => 'required|integer',
            'size' => 'required|numeric',
            'number' => 'sometimes|required|integer',
            'schema' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,svg',
        ]);
        $company_id = $request->company_id;
        try {
            DB::beginTransaction();
            $check = Office::where([
                'id' => $id,
                'company_id' => $company_id
            ])->exists();
            if (!$check) {
                return $this->errorResponse('apiResponse.officeNotFound');
            }

            $floor = Floor::where([
                'id' => $request->floor_id,
                'company_id' => $company_id
            ])->first();
            if (!$floor)
                return $this->errorResponse('apiResponse.officeNotFound');


            if ($floor->common_size - $floor->sell_size < $request->size) {
                DB::rollBack();
                return $this->errorResponse(trans('apiResponse.sizeError'));
            }
            $floor->update(['sold_size' => $floor->sold_size + $request->size]);

            $arr = $request->only('floor_id', 'size', 'number');

            $arr['office_id'] = $id;

            if ($request->has('schema')) {
                $arr['schema'] = $this->uploadImage($request->company_id, $request->schema, 'locations');
            }

            $location = Location::create($arr);

            DB::commit();
            return $this->successResponse(['location' => $location]);
        } catch (QueryException   $e) {
            if ($e->errorInfo[1] == 1062) {
                return $this->errorResponse(trans('apiResponse.useUpdateOrChangeFloorId', ['floor' => $request->floor_id]));
            }
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    public function locationUpdate(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'location_id' => 'required|integer',
            'floor_id' => 'sometimes|required|integer',
            'size' => 'sometimes|required|numeric',
            'number' => 'sometimes|integer',
            'schema' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif,svg',
        ]);
        $arr = $request->only('size', 'floor_id', 'number', 'schema');
        if (!$arr)
            return $this->errorResponse(trans('apiResponse.nothing'));

        $company_id = $request->company_id;

        $hasSize = $request->has('size');
        $hasFloor_id = $request->has('floor_id');

        try {
            DB::beginTransaction();
            $check = Office::where([
                'id' => $id,
                'company_id' => $company_id
            ])->exists();
            if (!$check)
                return $this->errorResponse('apiResponse.officeNotFound');

            $location = Location::where([
                'id' => $request->location_id,
                'office_id' => $id
            ])->first();


            if (!$location) return $this->errorResponse('apiResponse.officeNotFound');

            if ($hasFloor_id) {
                $newFloor = Floor::where([
                    'id' => $request->floor_id,
                    'company_id' => $company_id
                ])->first();
                if (!$newFloor) return $this->errorResponse(trans('apiResponse.floorNotFound'));
            }


            if ($hasSize and $hasFloor_id) {

                if ($newFloor->common_size - $newFloor->sold_size < $request->size)
                    return $this->errorResponse(trans('apiResponse.sizeError'));

                $check = Floor::where('id', $location->floor_id)->update([
                    'sold_size' => DB::raw("sold_size - {$location->size}")
                ]);
                $newFloor->update([
                    'sold_size' => DB::raw("sold_size + {$request->size}")
                ]);
                if (!$check) return $this->errorResponse(trans('apiResponse.unProcess'));


            }

            if ($hasSize and !$hasFloor_id) {
                $locationFloor = Floor::where('id', $location->floor_id)->first();
                if ($locationFloor->common_size - $locationFloor->sold_size < $request->size)
                    return $this->errorResponse(trans('apiResponse.sizeError'));
                $locationFloor->update([
                    'sold_size' => DB::raw("sold_size - {$location->size} + $request->size")
                ]);
            }

            if (!$hasSize and $hasFloor_id) {
                if ($newFloor->common_size - $newFloor->sold_size < $location->size)
                    return $this->errorResponse(trans('apiResponse.sizeError'));

                $newFloor->update([
                    'sold_size' => DB::raw("sold_size + {$location->size}")
                ]);
                $location->floor_id = $newFloor->id;
            }


            $location->fill($arr);


            if ($request->has('schema'))
                $location->schema = $this->uploadImage($request->company_id, $request->schema, 'locations');


            $location->save();

            DB::commit();
            return $this->successResponse("OK");
        } catch (Exception $e) {
            DB::rollBack();
            $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function locationDestroy(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'location_id' => 'required|integer',
        ]);
        $company_id = $request->company_id;
        try {
            DB::beginTransaction();

            $check = Office::where([
                'id' => $id,
                'company_id' => $company_id
            ])->exists();

            if (!$check)
                return $this->errorResponse('apiResponse.officeNotFound');

            $location = Location::where([
                'id' => $request->location_id,
                'office_id' => $id
            ])->first();

            if (!$location)
                return $this->errorResponse('apiResponse.unProcess');

           Floor::where('id' , $location->floor_id)->update([
               'sold_size' => DB::raw("sold_size - {$location->size}")
           ]);

            $location->delete();

            DB::commit();
            return $this->successResponse('OK');

        } catch (Exception $e){
            DB::rollBack();

            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * end location
     */

    /**
     * start documents
     */
    public function documentAdd(Request $request, $id){
        $this->validate($request , [
            'company_id' => 'required|integer',

            'documents' => 'required|array',
            'documents.*' => 'required|mimes::jpeg,png,jpg,gif,svg,pdf,docx,doc,txt,xls,xlsx'
        ]);
        try{

            $office = $this->officeExists($id , $request->company_id );
            if (!$office) return  $this->errorResponse('apiResponse.officeNotFound');
            $documents = [];
            foreach ($request->documents as $document){
                $documents[] = [
                    'office_id' => $id,
                    'url' => $this->uploadImage($request->company_id ,$document , 'documents' )
                ];
            }
            Document::insert($documents);
            return $this->successResponse('OK');
        }catch (\Exception $e){
            return $this->errorResponse(trans('apiResponse.tryLater'),Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function documentUpdate(Request $request, $id){
        $this->validate($request , [
            'company_id' => 'required|integer',

            'document_id' => 'required|integer',
            'document'=> 'required|mimes::jpeg,png,jpg,gif,svg,pdf,docx,doc,txt,xls,xlsx'
        ]);
        try{
            $office = $this->officeExists($id , $request->company_id);
            if (!$office) return  $this->errorResponse('apiResponse.officeNotFound');

            $check = Document::where([ ['office_id' , $id] , ['id' , $request->document_id]])->update([
                'url' => $this->uploadImage($request->company_id ,$request->document , 'documents' )
            ]);

            if (!$check) return $this->errorResponse('apiResponse.documentNotFound');

            return $this->successResponse('OK');

        }catch (\Exception $e){
            return $this->errorResponse(trans('apiResponse.tryLater'),Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function documentDestroy(Request $request, $id){
        $this->validate($request , [
            'company_id' => 'required|integer',

            'document_id' => 'required|integer'
        ]);
        try{
            $office = $this->officeExists($id , $request->company_id);
            if (!$office) return  $this->errorResponse('apiResponse.officeNotFound');

            $check = Document::where([ ['office_id' , $id] , ['id' , $request->document_id]])->delete();

            if (!$check) return $this->errorResponse('apiResponse.documentNotFound');

            return $this->successResponse('OK');

        }catch (\Exception $e){
            return $this->errorResponse(trans('apiResponse.tryLater'),Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * end documets
     */

    public function contactAdd(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'name' => 'required|min:2|max:255',
            'phone' => 'sometimes|required|regex:/^\+?[0-9]{12}$/',
            'email' => 'required_without:phone|email',
        ]);
        $company_id = $request->company_id;

        try {
            $office = Office::where([
                'id' => $id,
                'company_id' => $company_id
            ])->first();
            if (!$office)
                return $this->errorResponse('apiResponse.officeNotFound');
            $contacts = [
                'office_id' => $office->id,
                'name' => $request->name,
                'contact' => $request->phone ?? $request->email,
                'type' => $request->phone ? config('plaza.office.contact.phone') : config('plaza.office.contact.email')

            ];
            DB::table('offices_contacts')->insert($contacts);
            return $this->successResponse('ok');
        } catch (Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function contactUpdate(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'name' => 'sometimes|required|min:2|max:255',
            'phone' => 'sometimes|required|regex:/^\+?[0-9]{12}$/',
            'email' => 'sometimes|required|email',
            'contact_id' => 'required|integer'
        ]);
        if (!$request->only('email', 'phone', 'name')) {
            return $this->errorResponse(trans('apiResponse.nothing'));
        }
        $company_id = $request->company_id;

        try {
            $office = Office::where([
                'id' => $id,
                'company_id' => $company_id
            ])->exists();
            if (!$office)
                return $this->errorResponse('apiResponse.officeNotFound');
            $contact = DB::table('offices_contacts')->where([
                'id' => $request->contact_id,
                'office_id' => $id
            ])->first();

            if (!$contact)
                return $this->errorResponse('apiResponse.unProcess');

            $contacts = $request->only('name');

            if ($request->has('phone')) {
                $contacts['type'] = config('plaza.office.contact.phone');
                $contacts['contact'] = $request->phone;
            } else {
                if ($request->has('email')) {
                    $contacts['type'] = config('plaza.office.contact.email');
                    $contacts['contact'] = $request->email;
                }
            }


            DB::table('offices_contacts')->where([
                'id' => $request->contact_id,
            ])->update($contacts);
            return $this->successResponse('ok');
        } catch (Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function contactDelete(Request $request, $id)
    {
        $this->validate($request, [
            'contact_id' => 'required|integer'
        ]);
        $company_id = $request->company_id;

        try {
            $office = Office::where([
                'id' => $id,
                'company_id' => $company_id
            ])->exists();
            if (!$office)
                return $this->errorResponse('apiResponse.officeNotFound');
            $check = DB::table('offices_contacts')->where([
                'id' => $request->contact_id,
                'office_id' => $id
            ])->delete();
            if (!$check) {
                return $this->errorResponse(trans('apiResponse.unProcess'));
            }
            return $this->successResponse('ok');
        } catch (Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);

        }
    }

    public function getOfficeAssignedToUser(Request $request)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'office_id' => 'sometimes|required|integer',
            'per_page' => 'sometimes|required|integer'
        ]);
        try {

            $data = OfficeUser::with(['office:id,name' ,  'user'])->where('company_id', $request->company_id);
            if ($request->has('office_id'))
                $data->where('office_id', $request->office_id);

            $data = $data->paginate($request->get('per_page'));

            return $this->successResponse($data);
        } catch (\Exception $e) {
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function addUser(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'username' => ['required' , 'string' , 'max:255' , 'unique:users,username'],
            'password' => ['required' , 'string' ,"min:6" ,  'max:255'],
            'name' => ['required' , 'string' , 'max:255'],
            'surname' => ['sometimes' , 'required' , 'string' , 'max:255'],
        ]);

        try {
            DB::beginTransaction();
            $check = Office::where('company_id', $request->company_id)->where('id', $id)->exists();
            if (!$check) return $this->errorResponse('apiResponse.unProcess');

            $users = User::create([
                'role_id' => User::OFFICE,
                'name' => $request->get('name'),
                'password' => Hash::make($request->get('password')),
                'username' => $request->get('username')
            ]);

            $check = OfficeUser::create([
                'office_id' => $id,
                'user_id' => $users->id,
                'company_id' => $request->company_id
            ]);
            if (!$check) return $this->errorResponse('apiResponse.unProcess');
            DB::commit();
            return $this->successResponse('OK');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function updateUser(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'user_id' => ['required' , 'integer'],
            'username' => ['sometimes' , 'required' , 'string' , 'max:255'],
            'password' => ['sometimes' , 'required' , 'string' ,"min:6" ,  'max:255'],
            'name' => ['sometimes' , 'string' , 'max:255'],
            'surname' => [ 'sometimes',  'required' , 'string' , 'max:255'],
        ]);

        $data = $request->only(['username' ,'password' , 'name', 'surname' ]);
        if (!$data) return $this->errorResponse(trans('response.nothing'));
        try {
            DB::beginTransaction();

            $check = OfficeUser::whereHas('office' , function ($q)use($id,$request){
                    $q->where('id' ,$id)->where('company_id' , $request->get('company_id'));
            })->where('company_id' , $request->get('company_id'))
                ->where('user_id' , $request->get('user_id'))
                ->first(['id']);
            if (!$check) return $this->errorResponse(trans('response.userNotFound') , 404);


            if ($request->has('password')) $data['password'] = Hash::make($data['password']);

            User::where('id' , $request->get('user_id'))->update($data);
            DB::commit();
            return $this->successResponse('OK');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function removeUser(Request $request, $id)
    {
        $this->validate($request, [
            'company_id' => 'required|integer',
            'user_id' => 'required|integer',
        ]);
        try {
            DB::beginTransaction();

            $officeUser = OfficeUser::whereHas('office' , function ($q)use($id,$request){
                $q->where('id' ,$id)->where('company_id' , $request->get('company_id'));
            })->where('company_id' , $request->get('company_id'))
                ->where('user_id' , $request->get('user_id'))
                ->first(['id' , 'user_id']);
            if (!$officeUser) return $this->errorResponse(trans('response.userNotFound') , 404);

            User::where('id' , $officeUser->user_id)->delete();
            OfficeUser::where('id' , $officeUser->id)->delete();

            DB::commit();

            return $this->successResponse('OK');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse(trans('apiResponse.tryLater'), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }



    protected function officeExists($id , $company_id){
        $office = Office::where([
            'id' => $id,
            'company_id' => $company_id
        ])->first();
        if (!$office)
            return false;
        return $office;
    }

    protected function officeGet($id , $company_id , $columns =  ['*']){
        $office = Office::where([
            'id' => $id,
            'company_id' => $company_id
        ])->first($columns);
        if (!$office)
            return false;
        return $office;
    }

}
