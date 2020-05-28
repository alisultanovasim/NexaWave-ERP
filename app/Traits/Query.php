<?php


namespace App\Traits;


use Deployer\Exception\Exception;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Constraint\RegularExpressionTest;

trait Query
{


    public $custom = [];
    /**
     * doc
     * you must add to config/query.php file table to your field
     * if that table is not exists
     * @param $companyId
     * @param array $rules
     * @param array $custom
     * @return array
     */
    protected function companyInfo($companyId, $rules = [] , $custom = []): array
    {
        $this->custom = $custom;
        if (!$rules) return [];
        $sql = 'select  ';
        foreach ($rules as $field => $id) {
            if (is_array($id))
               foreach ($id as $i)
                   $sql .= $this->sqlQuery($field, $i  , $companyId );
            else
                $sql .= $this->sqlQuery($field, $id  , $companyId );
        }
        $sql = rtrim($sql, ",");
        $errors = [];
        try {
            $res = DB::select($sql)[0];
        } catch (QueryException $e) {
            if ($e->errorInfo[1] === 1146) {
                if (env('APP_DEBUG')) return ['error' => 'table in config/query.php is not exists'];
            }
            return [
                'error' => $e->getMessage()
            ];
        }

        foreach ($res as $k => $v)
            if (!$v)
                $errors[$k] = trans('response.notExists');
        return $errors;
    }

    public function sqlQuery($field , $id , $companyId){
        return " EXISTS( SELECT id from " . config("query.$field") . " where (company_id = $companyId or company_id is null )  and id = $id ) as  {$field},";
    }
}
