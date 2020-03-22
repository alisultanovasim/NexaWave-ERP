<?php


namespace App\Http\Controllers;

use Dusterio\LumenPassport\LumenPassport;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class StaticController extends Controller
{

//    public function __construct()
//    {
//        $this->middleware('auth');
//    }

    public function Esd(Request $request)
    {
        $this->validate($request, [
            'path' => 'required',
        ]);

        if (!$this->validToken($request->token)) return \response([
            'error' => 'the static file does not belongs to you'
        ]);

        return $this->responseImage(env('ESD_BASE_URI') . $request->path , $request->path);

    }

    public function Plaza(Request $request)
    {
        $this->validate($request, [
            'path' => 'required'
        ]);

        $filename = env('PLAZA_BASE_URI') . $request->path;
        return $this->responseImage($filename , $request->path);
    }

    public function responseImage($filename , $path)
    {
        try{
            $file = file_get_contents($filename);
            $filename = explode("/",$path);
            return \response($file,200,[
                'Content-type'=>"application/octet-stream",
                "Content-Disposition"=>"attachment; filename=".$filename[count($filename)-1]
            ]);
        } catch (\Exception $e){
            return \response([
                'error' => 'Path invalid or not found'
            ] , 404);
        }
    }

    protected function validToken($token){
        return true;
    }
}
