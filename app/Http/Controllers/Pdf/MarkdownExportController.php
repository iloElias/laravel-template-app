<?php

namespace App\Http\Controllers\Pdf;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pdf\MarkdownExportRequest;
use App\Models\Hr\User;
use App\Models\Pdf\MarkdownExport;
use App\Services\Pdf\MarkdownExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MarkdownExportController extends Controller
{
    public function __construct(private readonly MarkdownExportService $service)
    {
    }

    /**
     * POST /api/pdf/markdown/convert
     *
     * Converte markdown para PDF, armazena no S3/RustFS com versionamento
     * e retorna metadados + URL temporária de download.
     */
    public function convert(MarkdownExportRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Aceita conteúdo direto (string) ou arquivo .md enviado via multipart
        if (isset($validated['file'])) {
            $content = $request->file('file')->get();
        } else {
            $content = $validated['content'];
        }

        $user = User::auth() ?: null;

        $export = $this->service->convert(
            content: $content,
            projectId: $validated['project_id'] ?? null,
            filename: $validated['filename'] ?? null,
            user: $user,
        );

        $downloadUrl = $this->service->temporaryUrl($export, minutes: 30);

        return response()->json([
            'uuid' => $export->uuid,
            'filename' => $export->filename,
            'version' => $export->version,
            'size' => $export->size,
            'status' => $export->status,
            'download_url' => $downloadUrl,
            'project_id' => $export->project_id,
            'created_at' => $export->created_at->toIso8601String(),
        ], 201);
    }

    /**
     * GET /api/pdf/markdown/{uuid}
     *
     * Faz o download direto do PDF gerado.
     */
    public function download(string $uuid): Response|JsonResponse
    {
        $export = MarkdownExport::where('uuid', $uuid)
            ->where('status', MarkdownExport::STATUS_COMPLETED)
            ->firstOrFail();

        try {
            $pdfContent = $this->service->getContent($export);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $export->filename . '"',
            'Content-Length' => $export->size,
            'Cache-Control' => 'no-store',
        ]);
    }

    /**
     * GET /api/pdf/markdown/{uuid}/stream
     *
     * Retorna o PDF em stream para visualização inline no browser.
     */
    public function stream(string $uuid): StreamedResponse|JsonResponse
    {
        $export = MarkdownExport::where('uuid', $uuid)
            ->where('status', MarkdownExport::STATUS_COMPLETED)
            ->firstOrFail();

        try {
            $pdfContent = $this->service->getContent($export);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        return response()->stream(function () use ($pdfContent) {
            echo $pdfContent;
        }, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $export->filename . '"',
            'Content-Length' => strlen($pdfContent),
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    /**
     * GET /api/pdf/markdown/{uuid}/url
     *
     * Gera uma URL temporária de download assinada (S3/RustFS presigned URL).
     */
    public function temporaryUrl(string $uuid): JsonResponse
    {
        $export = MarkdownExport::where('uuid', $uuid)
            ->where('status', MarkdownExport::STATUS_COMPLETED)
            ->firstOrFail();

        $url = $this->service->temporaryUrl($export, minutes: 30);

        return response()->json(['download_url' => $url, 'expires_in' => 1800]);
    }

    /**
     * GET /api/pdf/markdown/project/{projectId}/versions
     *
     * Lista todas as versões exportadas de um projeto.
     */
    public function versions(string $projectId): JsonResponse
    {
        $exports = $this->service->listVersions($projectId);

        return response()->json(
            $exports->map(fn(MarkdownExport $e) => [
                'uuid' => $e->uuid,
                'filename' => $e->filename,
                'version' => $e->version,
                'size' => $e->size,
                'created_at' => $e->created_at->toIso8601String(),
            ])
        );
    }

    /**
     * DELETE /api/pdf/markdown/{uuid}
     *
     * Remove uma versão específica do PDF (soft delete + apaga do storage).
     */
    public function destroy(string $uuid): JsonResponse
    {
        $export = MarkdownExport::where('uuid', $uuid)->firstOrFail();

        $this->service->delete($export);

        return response()->json(['message' => 'Exportação removida com sucesso.']);
    }
}
