<?php

namespace CleaniqueCoders\LaravelApiVersion\Exceptions;

use CleaniqueCoders\LaravelApiVersion\Processors\VersionResolver;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionHandler extends ExceptionHandler
{
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $exception)
    {
        // Check if the request is an API request based on config
        if (VersionResolver::isApiRequest($request) && $exception instanceof NotFoundHttpException) {
            // Use VersionResolver to determine the current version
            $currentVersion = VersionResolver::resolve($request);

            return response()->json([
                'error' => 'The requested resource does not exist in this API version.',
                'version' => $currentVersion,
            ], 404);
        }

        // Fallback to default handler if it's not an API 404
        return parent::render($request, $exception);
    }
}
