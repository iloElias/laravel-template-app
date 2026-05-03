<?php

namespace App\Http\Controllers;

use App\Jobs\SendMail;
use App\Mail\FirstLoginMail;
use App\Models\Tracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DebugController extends Controller
{
    public function showEnvironment(Request $request): JsonResponse
    {
        return response()->json([
            'message' => ['ping' => 'pong'],
            'request' => [
                'ip' => Tracker::ip(),
                'request_method' => $request->method(),
                'params' => $request->route()->parameters(),
                'body' => $request->all(),
                'query' => $request->query(),
            ],
        ]);
    }

    public function getEnvironmentInstructions(): JsonResponse
    {
        return response()->json([
            'message' => [
                'instruction' => 'There is none yet.',
            ],
        ]);
    }

    public function getEnvironmentVariable(string $variable): JsonResponse
    {
        return response()->json([
            'message' => 'This functionality will not return values.',
            'data' => [
                'requested_var' => $variable,
            ],
        ]);
    }

    public function getLastError(): JsonResponse
    {
        $lastError = Log::getLogs()->last();

        return response()->json(['data' => $lastError ?? null]);
    }

    public function sendEmail(): JsonResponse
    {
        $mailable = new FirstLoginMail([
            'user' => ['name' => 'Murilo'],
            'info' => ['code' => '123456', 'expires' => now()->addMinutes(10)],
        ]);

        $mail = Mail::to('murilo7456@gmail.com')->send($mailable);

        return response()->json(['message' => 'Email sent', 'mail_info' => $mail->getDebug()]);
    }

    public function sendEmailJob(): JsonResponse
    {
        $job = SendMail::dispatch('murilo7456@gmail.com', FirstLoginMail::class, [
            'user' => ['name' => 'Murilo'],
            'info' => ['code' => '123456', 'expires' => now()->addMinutes(10)],
        ]);

        return response()->json(['message' => 'Email job created', 'job_info' => $job->getJob()]);
    }
}
