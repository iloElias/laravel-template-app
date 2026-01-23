<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

class SendMail implements ShouldQueue
{
    use Dispatchable;
    use Queueable;
    use SerializesModels;

    public array|string $to;
    public string $mailableClass;
    public array $data;
    public string $locale;
    public int $tries;

    /**
     * @param class-string<Mailable> $mailableClass
     */
    public function __construct(
        array|string $to,
        string $mailableClass,
        array $data = [],
        int $tries = 1
    ) {
        $this->to = $to;
        $this->mailableClass = $mailableClass;
        $this->data = $this->normalizeData($data);
        $this->tries = $tries;
        $this->locale = App::getLocale();
    }

    public function handle(): void
    {
        $resolvedData = $this->resolveData();

        /** @var Mailable $mailable */
        $mailable = (new $this->mailableClass($resolvedData))
            ->locale($this->locale)
        ;

        Mail::to($this->to)
            ->locale($this->locale)
            ->send($mailable)
        ;
    }

    /**
     * Converte modelos Eloquent em identificadores serializáveis.
     */
    protected function normalizeData(array $data): array
    {
        return collect($data)->map(function ($item) {
            if ($item instanceof Model) {
                return [
                    '_model' => get_class($item),
                    '_id' => $item->getKey(),
                ];
            }

            return $item;
        })->toArray();
    }

    /**
     * Restaura modelos antes de instanciar o mailable.
     */
    protected function resolveData(): array
    {
        return collect($this->data)->map(function ($item) {
            if (is_array($item) && isset($item['_model'], $item['_id'])) {
                return $item['_model']::find($item['_id']);
            }

            return $item;
        })->toArray();
    }
}
