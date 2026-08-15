<?php

namespace App\Exceptions;

use App\Http\Middleware\Share;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Session\TokenMismatchException;

class Handler extends ExceptionHandler
{
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

    /**
     * Report or log an exception.
     *
     * @param \Throwable $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($request->is('api/*')) {

            return $this->renderApi($request, $exception);
        }

        $modelNotFound = ($exception instanceof ModelNotFoundException);

        // ValidationException must never enter this custom-error-page path — it has its own
        // correct handling in the base Handler (redirect-back-with-errors for normal requests,
        // 422 JSON for AJAX/API requests). Letting it fall through here caused every validation
        // failure site-wide (login, register, contact, checkout, etc.) to show a 500 crash-page
        // instead of a normal field-error, whenever APP_DEBUG=false (i.e. always in production).
        // AuthenticationException must also bypass this path — the base Handler's
        // unauthenticated() correctly redirects to the login page (or returns 401 JSON for API).
        // Without this exclusion, any expired/missing session on an authenticated route showed
        // a 500 crash-page instead of a normal login redirect (found 2026-08-15, Loop 2.9 session,
        // pre-existing bug unrelated to 2FA work — confirmed via unauthenticated POST to unrelated
        // pre-existing route /panel/setting/metas).
        if (
            !($exception instanceof ValidationException) and
            !($exception instanceof AuthenticationException) and
            !($exception instanceof TokenMismatchException) and
            (
                $this->isHttpException($exception) or
                (
                    $exception->getCode() == 0 and
                    ($modelNotFound or !env('APP_DEBUG'))
                )
            )
        ) {

            if ($this->isHttpException($exception)) {
                $statusCode = $exception->getStatusCode(); // @phpstan-ignore-line
            } else if ($modelNotFound) {
                $statusCode = 404;
            } else {
                $statusCode = 500;
            }

            if (in_array($statusCode, [404, 403, 419, 500])) {
                $share = (new Share());
                $shareData = $share->getShareData($request);

                $errorSettings = "get{$statusCode}ErrorPageSettings"();
                $pageTitle = !empty($errorSettings['title']) ? $errorSettings['title'] : trans('update.error_page');

                $data = [
                    "pageTitle" => $pageTitle,
                    'statusCode' => $statusCode,
                    'errorSettings' => $errorSettings,
                    'dontShowCookieSecurity' => true,
                ];

                $data = array_merge($data, $shareData);

                return response()->view('design_1.web.errors.errors', $data, $statusCode);
            }
        }

        return parent::render($request, $exception);
    }

    public function renderApi($request, Throwable $e)
    {
        if ($e instanceof MethodNotAllowedHttpException) {
            $status = Response::HTTP_METHOD_NOT_ALLOWED;
            $e = new MethodNotAllowedHttpException([], 'HTTP_METHOD_NOT_ALLOWED', $e);
        } elseif ($e instanceof NotFoundHttpException) {
            $status = Response::HTTP_NOT_FOUND;
            $e = new NotFoundHttpException('HTTP_NOT_FOUND', $e);
        } elseif ($e instanceof AuthorizationException) {
            $status = Response::HTTP_FORBIDDEN;
            $e = new AuthorizationException('HTTP_FORBIDDEN', $status);
        } elseif ($e instanceof \Dotenv\Exception\ValidationException) {

            $status = Response::HTTP_BAD_REQUEST;
            $e = new \Dotenv\Exception\ValidationException('HTTP_BAD_REQUEST', $status, $e);
        } elseif ($e instanceof ValidationException && $e->getResponse()) {
            return $e->getResponse();
        } elseif ($e instanceof ThrottleRequestsException) {
            $status = Response::HTTP_TOO_MANY_REQUESTS;
            $headers = $e->getHeaders();
            return response()->json([
                'success' => false,
                'status' => $status,
                'message' => 'Too many requests. Please slow down and try again shortly.'
            ], $status, $headers);
        } else {
            if (env('APP_DEBUG')) {
                return parent::render($request, $e);
            }
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
            $e = new HttpException($status, 'HTTP_INTERNAL_SERVER_ERROR');
        }

        return response()->json([
            'success' => false,
            'status' => $status,
            'message' => $e->getMessage()
        ], $status);
    }

}
