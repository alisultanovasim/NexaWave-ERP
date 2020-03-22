<?php
namespace App\Traits;
use Illuminate\Http\Response;
trait ApiResponse
{
    /**
     * Building success response
     * @param $data
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function successResponse($data, $code = Response::HTTP_OK)
    {
        return \response()->json(['data' => $data], $code);
    }

    public function errorResponse($message, $code = Response::HTTP_UNPROCESSABLE_ENTITY)
    {
        if (is_array($message))
            return \response()->json($message, $code);
        else
            return \response()->json(['error' => $message], $code);
    }

    public function errorMessage($data, $code = Response::HTTP_UNPROCESSABLE_ENTITY)
    {
        return \response()->json($data, $code);
    }
}
