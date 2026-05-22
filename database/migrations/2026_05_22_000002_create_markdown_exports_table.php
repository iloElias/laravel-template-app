<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pdf.markdown_export', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();

            // Identificador do projeto (vindo do localStorage do frontend)
            $table->string('project_id', 36)->nullable()->index();

            // Hash SHA-256 do conteúdo para evitar re-geração de PDFs idênticos
            $table->string('content_hash', 64)->index();

            // Metadados do arquivo
            $table->string('filename');
            $table->string('storage_path');
            $table->unsignedBigInteger('size')->default(0);

            // Versionamento: v1, v2, v3... por project_id
            $table->unsignedSmallInteger('version')->default(1);

            // Status do processamento
            $table->string('status', 20)->default('completed');
            $table->text('error_message')->nullable();

            // Quem criou (opcional — conversões anônimas são permitidas)
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('hr.user')
                ->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            // Índice composto: busca rápida por projeto + versão
            $table->unique(['project_id', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdf.markdown_export');
    }
};
