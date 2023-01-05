<?php

namespace Modules\Storage\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Storage\Entities\ArchiveDocument;

class ArchiveDocumentsController extends Controller
{
    public function index(Request $request)
    {
        $per_page=$request->per_page ?? 10;
        $archive=ArchiveDocument::query()->with([
            'demands',
            'proposes',
            'purchases'
        ]);

        if ($request->has('date')){
            if (isset($request->date[0]))
                $archive->whereDate('created_at','>=',$request->date[0]);
            if (isset($request->date[1]))
                $archive->whereDate('created_at','<=',$request->date[1]);

        }

        if ($request->has('status')){
            $archive->where('status',$request->status);
        }

        if ($request->has('employee_id')){
            $archive->where('employee_id',$request->employee_id);
        }

        if ($request->has('role_id'))
            $archive->where('role_id',$request->role_id);

        return $archive->paginate($per_page);
    }
}
