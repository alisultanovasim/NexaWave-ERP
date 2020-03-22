<?php
//
//namespace Modules\Esd\Http\Controllers;
//use Modules\Esd\Http\Controllers\Controller;
//use Illuminate\Http\Request;
//use Illuminate\Http\Response;
//use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\File;
//use Illuminate\Validation\Rules\In;
//use Modules\Entities\Document;
//use Modules\Entities\Inbox;
//use Monolog\Handler\SyslogUdp\UdpSocket;
//
//class InboxController extends Controller
//{
//
//
//    /**
//     * Display a listing of the resource.
//     * @return Response
//     */
//    public function index()
//    {
//        return  Useful::createResponse(1 , "OK" , Inbox::all());
//    }
//
//    /**
//     * Store a newly created resource in storage.
//     * @param Request $request
//     * @return Response
//     */
//    public function store(Request $request)
//    {
//        $this->validate($request , [
//            "theme"=>"required|min:2|max:255",
//            "description"=>"required|min:5",
//
//            "from"=>"required|min:2|max:255",
//            "file"=>"required-without:description|mimes:docx,doc,txt,pdf,xlsx,xls,ppt,pptx",
//            //"company_id"=>"required|exists:companies,id"
//        ]);
//        $isUploadFile = false;
//        try{
//            $inbox = new Inbox;
//            $inbox->fill(array_merge($request->only("theme" , "description" , "from") , [
//                "company_id"=>1
//            ]));
//            if ($request->hasFile("file")){
//                $file = $request->file("file");
//                $filename =rand(1 , 10000).time(). ".".$file->extension();
//                $fullname = "/documents/" .$request->company_id . "/" . $filename;
//                $file->move(public_path("/documents/" .$request->company_id) , $filename);
//                $isUploadFile = true;
//                $inbox->url = $filename;
//            }
//            $inbox->save();
//            return Useful::createResponse(1 , "OK" , (isset($fullname))?["url"=>$fullname]:[]);
//        }catch (\Exception $e){
//            if ($request->hasFile("file") and $isUploadFile)
//                File::delete(public_path("/documents/" . $request->company_id ) . $filename);
//            return Useful::createResponse(3 , trans("apiResponse.tryLater") ,  $e->getMessage());
//        }
//    }
//
//    /**
//     * Show the specified resource.
//     * @param int $id
//     * @return Response
//     */
//    public function show($id)
//    {
//        try{
//            $document = Inbox::where([
//                "id"=>$id ,
//                "company_id"=>auth()->user()->company_id
//            ])->first();
//            if (!$document)
//                return Useful::createResponse(2 , trans("apiResponse.unProcess"));
//            return Useful::createResponse(1 , "OK" , $document);
//        }catch (\Exception $e){
//            return Useful::createResponse(3 , trans("apiResponse.tryLater"));
//        }
//    }
//
//    /**
//     * Remove the specified resource from storage.
//     * @param int $id
//     * @return Response
//     */
//    public function destroy($id)
//    {
//        try{
//            $check = DB::table("inboxes")->where("id" , $id)->where("company_id" , auth()->user()->company_id)->delete();
//            if (!$check)
//                return Useful::createResponse(2 , trans("apiResponse.unProcess"));
//            return Useful::createResponse(1 , "OK");
//        }catch (\Exception $e){
//            return Useful::createResponse(3 , trans("apiResponse.tryLater"));
//        }
//    }
//
//    public function destroyMany(Request $request)
//    {
//        $this->validate($request , [
//            "inbox_ids"=>"required|array",
//            "inbox_ids.*"=>"required|integer"
//        ]);
//        try{
//            $check = DB::table("inboxes")->whereIn("id" , $request->inbox_ids)->where("company_id" , auth()->user()->company_id)->delete();
//            if (!$check)
//                return Useful::createResponse(2 , trans("apiResponse.unProcess"));
//            return Useful::createResponse(1 , "OK");
//        }catch (\Exception $e){
//            return Useful::createResponse(3 , trans("apiResponse.tryLater"));
//        }
//    }
//
//    public function markAsRead($id)
//    {
//        try{
//            $inbox = Inbox::where("id" , $id)->where("company_id" , auth()->user()->company_id)->update([
//                "is_read" => 1
//            ]);
//            if (!$inbox)
//                return Useful::createResponse(2 , trans("apiResponse.unProcess") );
//            return Useful::createResponse(1 , "OK");
//        }catch (\Exception $e){
//            return Useful::createResponse(3 , trans("apiResponse.tryLater"));
//        }
//    }
//
//    public function markAsDelete($id)
//    {
//        try{
//            $inbox = Inbox::where("id" , $id)->where("company_id" , auth()->user()->company_id)->update([
//                "deleted_at"=>DB::raw("CURRENT_TIMESTAMP") ,
//                "is_read"=>1
//            ]);
//            if (!$inbox)
//                return Useful::createResponse(2 , trans("apiResponse.unProcess"));
//            return Useful::createResponse(1 , "OK");
//        }catch (\Exception $e){
//            return Useful::createResponse(3 , trans("apiResponse.tryLater"));
//        }
//    }
//
//    public function markAsDeleteMany(Request $request)
//    {
//        $this->validate($request , [
//            "inbox_ids"=>"required|array",
//            "inbox_ids.*"=>"required|integer"
//        ]);
//        try{
//            $check = Inbox::whereIn("id" ,$request->inbox_ids)->where("company_id" , auth()->user()->company_id)->update([
//                "deleted_at"=>DB::raw("CURRENT_TIMESTAMP") ,
//                "is_read"=>1
//            ]);
//            if (!$check)
//                return Useful::createResponse(2 , trans("apiResponse.unProcess"));
//            return Useful::createResponse(1 , "OK");
//        }catch (\Exception $e){
//            return Useful::createResponse(3 , trans("apiResponse.tryLater"));
//        }
//    }
//
//    public  function getNotReadCount(){
//        try{
//            $count = Inbox::where("company_id" , auth()->user()->company_id)->where("is_read" , 0)->count();
//            return Useful::createResponse(1 , "OK" , $count);
//        }catch (\Exception $e){
//            return Useful::createResponse(3 , trans("apiResponse.tryLater"));
//        }
//    }
//}
//
