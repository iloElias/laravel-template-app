<?php

namespace App\Http\Controllers;

use App\Jobs\SendMail;
use App\Jobs\SendSms;
use App\Mail\FirstLoginMail;
use App\Models\Tracker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DebugController extends Controller
{
    public function showEnvironment(Request $request)
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
            'data' => ['request' => $GLOBALS],
            'raw_data' => $request->getContent(),
        ]);
    }

    public function showNestedParams(Request $request)
    {
        return response()->json([
            'request' => [
                'params' => $request->route()->parameters(),
                'query' => $request->query(),
            ],
        ]);
    }

    public function getEnvironmentInstructions()
    {
        return response()->json([
            'message' => [
                'instruction' => 'There is none yet.',
            ],
        ]);
    }

    public function getEnvironmentVariable($variable)
    {
        return response()->json([
            'message' => 'This functionality will not return values.',
            'data' => [
                'requested_var' => $variable,
            ],
        ]);
    }

    public function mapProjectFiles()
    {
        $projectRoot = base_path();
        $files = $this->readDirectory($projectRoot);

        return response()->json([
            'data' => $files,
        ]);
    }

    public function getFileContent(Request $request)
    {
        $filePath = $request->query('path');
        $fullPath = base_path($filePath);

        if (File::exists($fullPath)) {
            return response()->json([
                'data' => File::get($fullPath),
            ]);
        }

        return response()->json([
            'message' => 'File not found',
        ], 404);
    }

    public function showBody(Request $request)
    {
        return response()->json([
            'data' => $request->all(),
        ]);
    }

    public function getLastError()
    {
        $lastError = Log::getLogs()->last();

        if ($lastError) {
            return response()->json(['data' => $lastError]);
        }

        return response()->json(['data' => null]);
    }

    public function sendEmail()
    {
        $mailable = new FirstLoginMail(['user' => ['name' => 'Murilo'], 'info' => ['code' => '123456', 'expires' => now()->addMinutes(10)]]);
        $mail = Mail::to('murilo7456@gmail.com')->send($mailable);

        return response()->json(['message' => 'Email sent', 'mail_info' => $mail->getDebug()]);
    }

    public function sendSms()
    {
        // $job = SendSms::dispatch('+5564996020731', 'Seu código de autenticação para o agrofast é: Apenas um teste');

        // return response()->json(['message' => 'Email job created', 'job_info' => $job->getJob()]);
    }

    public function sendEmailJob()
    {
        $job = SendMail::dispatch('murilo7456@gmail.com', FirstLoginMail::class, ['user' => ['name' => 'Murilo'], 'info' => ['code' => '123456', 'expires' => now()->addMinutes(10)]]);

        return response()->json(['message' => 'Email job created', 'job_info' => $job->getJob()]);
    }

    public function sendSmsJob()
    {
        $job = SendSms::dispatch('+5564996020731', 'Seu código de autenticação para o agrofast é: Apenas um teste');

        return response()->json(['message' => 'Email job created', 'job_info' => $job->getJob()]);
    }

    private function readDirectory($directory)
    {
        $items = [];
        foreach (scandir($directory) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;
            $items[] = [
                'name' => $item,
                'type' => is_dir($path) ? 'directory' : 'file',
                'path' => $path,
            ];
        }

        return $items;
    }
}
