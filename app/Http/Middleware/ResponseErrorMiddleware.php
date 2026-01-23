<?php

namespace App\Http\Middleware;

use App\Exception\InvalidFormException;
use App\Exception\InvalidRequestException;
use App\Support\Traits\HandlesJsonErrors;
use App\Utils;
use Dotenv\Exception\ValidationException as ExceptionValidationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ResponseErrorMiddleware
{
    use HandlesJsonErrors;

    /**
     * Handle an incoming request.
     *
     * @param mixed $request
     */
    public function handle($request, \Closure $next)
    {
        try {
            $response = $next($request);

            if (isset($response->exception)) {
                throw $response->exception;
            }

            return $response;
        } catch (ExceptionValidationException $e) {
            return $this->returnRequestErrors(new InvalidRequestException($e->getMessage()));
        } catch (ValidationException $e) {
            return $this->returnValidationErrors($e);
        } catch (InvalidFormException $e) {
            return $this->returnValidationErrors($e);
        } catch (InvalidRequestException $e) {
            return $this->returnRequestErrors($e);
        } catch (\Throwable $e) {
            if (!Utils::isProduction()) {
                throw $e;
            }

            return response()->json([
                'message' => 'An unexpected error occurred',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal Server Error',
            ], $this->validHttpCode($e->getCode(), Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }
}
