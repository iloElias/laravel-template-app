<?php

// app/Jobs/ValidateGooglePlacesJob.php

namespace App\Jobs;

use App\Services\TransportRequestService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ValidateGooglePlacesJob implements ShouldQueue
{
    use Dispatchable;
    use Queueable;
    use InteractsWithQueue;
    use SerializesModels;

    protected int $requestId;

    public function __construct(int $requestId)
    {
        $this->requestId = $requestId;
    }

    public function handle(TransportRequestService $transportValidationService): void
    {
        $transportValidationService->validateTransportRequest($this->requestId);
    }
}
