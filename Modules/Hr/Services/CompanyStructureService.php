<?php


namespace Modules\Hr\Services;


use App\Models\Company;
use Illuminate\Support\Facades\Cache;

class CompanyStructureService
{
    public function getStructure($companyId, $getNestedFormat = false)
    {
        $structure = Company::where('id', $companyId)
        ->with([
            'structuredDepartments:id,name,is_closed,structable_id,structable_type',
            'structuredSections:id,name,is_closed,structable_id,structable_type',
            'structuredSectors:id,name,is_closed,structable_id,structable_type',
        ])
        ->first([
            'id',
            'name'
        ]);
        if ($getNestedFormat){
            //when update company structure unset company-structure-comapnyId-* cache keys
            $cacheKey = 'company-structure-'. $companyId . '-' . md5(serialize($structure));
            if (Cache::has($cacheKey)){
                $structure->children = Cache::get($cacheKey);
            }
            else {
                $children = $this->getNestedStructure($structure);
                Cache::put($cacheKey, $children, 24 * 60 * 60);
                $structure->children = $children;
            }
            unset($structure->structuredDepartments);
            unset($structure->structuredSections);
            unset($structure->structuredSectors);
        }

        return $structure;
    }

    private function getNestedStructure($structure = []){
        $formattedStructure = [];

        foreach ($structure['structuredDepartments'] ?? [] as $department){
            $formattedStructure[] = [
                'id' => $department['id'],
                'name' => $department['name'],
                'is_closed' => $department['is_closed'],
                'type' => 'department',
                'children' => $this->getNestedStructure($department)
            ];
        }

        foreach ($structure['structuredSections'] ?? [] as $section){
            $formattedStructure[] = [
                'id' => $section['id'],
                'name' => $section['name'],
                'is_closed' => $section['is_closed'],
                'type' => 'section',
                'children' => $this->getNestedStructure($section)
            ];
        }

        foreach ($structure['structuredSectors'] ?? [] as $sector){
            $formattedStructure[] = [
                'id' => $sector['id'],
                'name' => $sector['name'],
                'is_closed' => $sector['is_closed'],
                'type' => 'sector',
                'children' => $this->getNestedStructure($sector)
            ];
        }
        return $formattedStructure;
    }
}
