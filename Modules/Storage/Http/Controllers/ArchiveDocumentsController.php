<?php

namespace Modules\Storage\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Storage\Entities\ArchiveDocument;

class ArchiveDocumentsController extends Controller
{
    public function index(Request $request)
    {
        $demands=ArchiveDocument::query()
            ->where('document_type',ArchiveDocument::DEMAND_TYPE)
            ->with('demands')
            ->get()->toArray();

        $proposes=ArchiveDocument::query()
            ->where('document_type',ArchiveDocument::PPROPOSE_TYPE)
            ->with('proposes')
            ->get()->toArray();

        $purchases=ArchiveDocument::query()
            ->where('document_type',ArchiveDocument::PURCHASE_TYPE)
            ->with('purchases')
            ->get()->toArray();


        $archive=array_merge($demands,$proposes);

        return $archive;
    }
}
