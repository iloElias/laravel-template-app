<?php

namespace App\Services\Pdf;

use App\Models\Hr\User;
use App\Models\Pdf\MarkdownExport;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MarkdownExportService
{
    private const STORAGE_DISK = 's3';
    private const STORAGE_PREFIX = 'pdf/markdown';

    /**
     * Converte markdown para PDF, armazena no S3/RustFS e registra no banco.
     *
     * Se o mesmo conteúdo já foi exportado para o mesmo projeto, retorna o
     * registro existente (sem re-gerar o PDF) para economizar processamento.
     *
     * @param string      $content   Conteúdo markdown
     * @param string|null $projectId UUID do projeto (vindo do frontend)
     * @param string|null $filename  Nome sugerido para o arquivo
     * @param User|null   $user      Usuário autenticado (opcional)
     *
     * @return MarkdownExport
     *
     * @throws \RuntimeException se a geração do PDF falhar
     */
    public function convert(
        string $content,
        ?string $projectId = null,
        ?string $filename = null,
        ?User $user = null,
    ): MarkdownExport {
        $contentHash = hash('sha256', $content);

        // Cache: mesmo conteúdo + mesmo projeto → retorna existente
        if ($projectId) {
            $existing = MarkdownExport::where('project_id', $projectId)
                ->where('content_hash', $contentHash)
                ->where('status', MarkdownExport::STATUS_COMPLETED)
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        $version = MarkdownExport::nextVersion($projectId);
        $exportUuid = (string) Str::uuid();
        $safeFilename = $this->buildFilename($filename, $version);
        $storagePath = $this->buildStoragePath($exportUuid, $safeFilename);

        // Cria registro com status "pending" para rastreabilidade
        $export = MarkdownExport::create([
            'uuid' => $exportUuid,
            'project_id' => $projectId,
            'content_hash' => $contentHash,
            'filename' => $safeFilename,
            'storage_path' => $storagePath,
            'version' => $version,
            'status' => MarkdownExport::STATUS_PENDING,
            'created_by' => $user?->id,
        ]);

        try {
            $pdfContent = $this->generatePdf($content, $filename ?? 'Document');

            Storage::disk(self::STORAGE_DISK)->put($storagePath, $pdfContent);

            $export->update([
                'size' => strlen($pdfContent),
                'status' => MarkdownExport::STATUS_COMPLETED,
            ]);
        } catch (\Throwable $e) {
            $export->update([
                'status' => MarkdownExport::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'Falha ao gerar PDF: ' . $e->getMessage(),
                0,
                $e,
            );
        }

        return $export->fresh();
    }

    /**
     * Retorna todas as versões exportadas de um projeto, ordenadas por versão.
     */
    public function listVersions(string $projectId): Collection
    {
        return MarkdownExport::where('project_id', $projectId)
            ->where('status', MarkdownExport::STATUS_COMPLETED)
            ->orderBy('version', 'desc')
            ->get();
    }

    /**
     * Recupera o conteúdo binário do PDF armazenado.
     *
     * @throws \RuntimeException se o arquivo não for encontrado
     */
    public function getContent(MarkdownExport $export): string
    {
        $content = Storage::disk(self::STORAGE_DISK)->get($export->storage_path);

        if ($content === null) {
            throw new \RuntimeException('Arquivo PDF não encontrado no armazenamento.');
        }

        return $content;
    }

    /**
     * Gera uma URL temporária de download assinada (S3/RustFS presigned URL).
     * O disco S3 expõe `temporaryUrl` via AwsS3V3Adapter.
     *
     * @throws \RuntimeException se o disco não suportar URLs temporárias
     */
    public function temporaryUrl(MarkdownExport $export, int $minutes = 15): string
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk(self::STORAGE_DISK);

        if (!method_exists($disk, 'temporaryUrl')) {
            // Fallback: retorna URL de download via API interna
            return config('app.url') . '/api/pdf/markdown/' . $export->uuid;
        }

        return $disk->temporaryUrl(
            $export->storage_path,
            now()->addMinutes($minutes),
        );
    }

    /**
     * Remove o PDF do armazenamento e apaga o registro (soft delete).
     */
    public function delete(MarkdownExport $export): void
    {
        Storage::disk(self::STORAGE_DISK)->delete($export->storage_path);
        $export->delete();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers privados
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Gera o PDF a partir do conteúdo markdown usando Dompdf.
     *
     * Converte Markdown → HTML (via Laravel Str::markdown) e depois renderiza
     * o HTML em PDF com tipografia adequada para impressão.
     */
    private function generatePdf(string $markdownContent, string $title): string
    {
        $bodyHtml = Str::markdown($markdownContent, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        $html = $this->buildHtmlDocument($bodyHtml, $title);

        $dompdf = new \Dompdf\Dompdf([
            'enable_remote' => false,
            'enable_html5parser' => true,
            'default_font' => 'DejaVu Sans',
            'dpi' => 96,
            'default_paper_size' => 'a4',
        ]);

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    /**
     * Monta o documento HTML completo com estilos otimizados para impressão.
     */
    private function buildHtmlDocument(string $bodyHtml, string $title): string
    {
        $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>{$safeTitle}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'DejaVu Sans', Arial, sans-serif;
      font-size: 11pt;
      line-height: 1.7;
      color: #1a1a1a;
      background: #fff;
      padding: 2cm 2.5cm;
    }

    h1, h2, h3, h4, h5, h6 {
      font-weight: bold;
      margin-top: 1.2em;
      margin-bottom: 0.4em;
      page-break-after: avoid;
      color: #111;
    }

    h1 { font-size: 22pt; border-bottom: 2px solid #e5e7eb; padding-bottom: 0.3em; }
    h2 { font-size: 18pt; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.2em; }
    h3 { font-size: 14pt; }
    h4 { font-size: 12pt; }

    p { margin-bottom: 0.8em; }

    a { color: #1d4ed8; text-decoration: underline; }

    code {
      font-family: 'DejaVu Sans Mono', Courier, monospace;
      font-size: 9.5pt;
      background: #f3f4f6;
      border: 1px solid #d1d5db;
      border-radius: 3px;
      padding: 0.1em 0.3em;
    }

    pre {
      background: #1e293b;
      color: #e2e8f0;
      border-radius: 6px;
      padding: 1em;
      overflow: hidden;
      margin-bottom: 1em;
      page-break-inside: avoid;
    }

    pre code {
      background: transparent;
      border: none;
      color: inherit;
      padding: 0;
      font-size: 9pt;
      white-space: pre-wrap;
      word-wrap: break-word;
    }

    blockquote {
      border-left: 4px solid #6366f1;
      padding-left: 1em;
      margin: 1em 0;
      color: #4b5563;
      font-style: italic;
      page-break-inside: avoid;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 1em;
      font-size: 10pt;
      page-break-inside: avoid;
    }

    th, td {
      border: 1px solid #d1d5db;
      padding: 0.5em 0.75em;
      text-align: left;
    }

    th {
      background: #f9fafb;
      font-weight: bold;
    }

    tr:nth-child(even) td { background: #f9fafb; }

    ul, ol {
      margin-left: 1.5em;
      margin-bottom: 0.8em;
    }

    li { margin-bottom: 0.3em; }

    hr {
      border: none;
      border-top: 1px solid #e5e7eb;
      margin: 1.5em 0;
    }

    img {
      max-width: 100%;
      height: auto;
      page-break-inside: avoid;
    }

    @page {
      margin: 0;
      size: A4 portrait;
    }
  </style>
</head>
<body>
  {$bodyHtml}
</body>
</html>
HTML;
    }

    private function buildFilename(?string $base, int $version): string
    {
        $name = $base ? preg_replace('/[^a-zA-Z0-9_\-\. ]/', '', $base) : 'document';
        $name = trim(str_replace(' ', '_', $name)) ?: 'document';
        $name = mb_substr($name, 0, 100);

        return "{$name}_v{$version}.pdf";
    }

    private function buildStoragePath(string $exportUuid, string $filename): string
    {
        return self::STORAGE_PREFIX . "/{$exportUuid}/{$filename}";
    }
}
