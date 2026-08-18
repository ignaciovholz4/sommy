<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wa_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wa_account_id')->constrained('wa_accounts')->cascadeOnDelete();
            $table->string('name');
            $table->string('language', 10)->default('es_AR');
            $table->string('category', 30)->nullable(); // MARKETING | UTILITY | AUTHENTICATION
            $table->text('body_text')->nullable();
            $table->json('variables')->nullable();      // nombres/ejemplos de {{1}}, {{2}}...
            $table->enum('meta_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();

            $table->unique(['wa_account_id', 'name', 'language']);
        });

        Schema::create('wa_tags', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 60)->unique();
            $table->string('color', 20)->default('#6c757d');
            $table->timestamps();
        });

        Schema::create('wa_conversation_tag', function (Blueprint $table) {
            $table->foreignId('conversation_id')->constrained('wa_conversations')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('wa_tags')->cascadeOnDelete();
            $table->primary(['conversation_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wa_conversation_tag');
        Schema::dropIfExists('wa_tags');
        Schema::dropIfExists('wa_templates');
    }
};
