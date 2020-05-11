<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponse;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function invalidJson($request, ValidationException $exception)
    {
        return $this->errorResponse($exception->errors(),Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     *
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param Throwable $exception
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof HttpException) {
            $code = $exception->getStatusCode();
            $message = \Symfony\Component\HttpFoundation\Response::$statusTexts[$code];
            return $this->errorResponse($message, $code);
        }
        else if ($exception instanceof QueryException) {
            if ($exception->errorInfo[1] == 1452)
                return $this->errorResponse([trans('response.SomeFiledIsNotFoundInDatabase')], 422);
            return $this->errorResponse(trans('response.serverError. code : 222'), 422);
        }
        else if ($exception instanceof ModelNotFoundException) {
            $model = strtolower(class_basename($exception->getModel()));
            return $this->errorResponse("Does not exist any instance of {$model} with the given id", Response::HTTP_NOT_FOUND);
        }
        else if ($exception instanceof AuthorizationException) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_FORBIDDEN);
        }
        else if ($exception instanceof AuthenticationException) {
            return $this->errorResponse($exception->getMessage(), Response::HTTP_UNAUTHORIZED);
        }
        else if ($exception instanceof ValidationException) {
            $errors = $exception->validator->errors()->getMessages();
            return $this->errorResponse($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        else if ($exception instanceof ClientException){
            if (env('APP_DEBUG')){
                $response = $exception->getResponse();
                $errors = json_decode($response->getBody()->getContents());
                $code = $response->getStatusCode();
                return $this->errorResponse($errors, $code);
            }
            else{
                return $this->errorResponse('Client Request Error', Response::HTTP_BAD_REQUEST);
            }
        }
        else {
            if (env('APP_DEBUG'))
                return parent::render($request, $exception);
            else
                return $this->errorResponse('Try later', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
