<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
        DB::statement('CREATE INDEX embeddings_embedding_idx ON embeddings USING ivfflat (embedding vector_cosine_ops) WITH (lists = 100)');
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX embeddings_embedding_idx');
    }
};
