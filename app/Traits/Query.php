<?php


namespace App\Traits;


use Deployer\Exception\Exception;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Constraint\RegularExpressionTest;

trait Query
{

    /**
     * doc
     * you must add to config/query.php file table to your field
     * if that table is not exists
     * @throws TableNotFoundException
     */
    protected function companyInfo($companyId, $rules = []): array
    {
        if (!$rules) return [];
        $sql = 'select  ';
        foreach ($rules as $field => $id) {
            $sql .= " EXISTS( SELECT id from " . config("query.$field") . " where (company_id = $companyId or company_id is null )  and id = $id ) as  {$field},";
        }
        $sql = rtrim($sql, ",");
        $errors = [];
        try {
            $res = DB::select($sql)[0];
        } catch (QueryException $e) {
            if ($e->errorInfo[1] === 1146) {
                if (env('APP_DEBUG')) return ['error' => 'table in config/query.php is not exists'];
                return $this->errorResponse([
                    'error' => 'server error'
                ], 500);
            }
        }

        foreach ($res as $k => $v)
            if (!$v)
                $errors[$k] = trans('response.notExists');
        return $errors;
    }
}
