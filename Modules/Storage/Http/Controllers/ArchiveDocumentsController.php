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
        $archive=ArchiveDocument::query();

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


        $demands=$archive
            ->where('document_type',ArchiveDocument::DEMAND_TYPE)
            ->with('demands')
            ->get()->toArray();

        $proposes=$archive
            ->where('document_type',ArchiveDocument::PPROPOSE_TYPE)
            ->with('proposes')
            ->get()->toArray();

        $purchases=$archive
            ->where('document_type',ArchiveDocument::PURCHASE_TYPE)
            ->with('purchases')
            ->get()->toArray();


        return array_merge($purchases,array_merge($demands,$proposes));
    }
}
