<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique(); // GitHub's X-GitHub-Delivery-ID
            $table->string('event_type'); // push, pull_request, etc.
            $table->string('action'); // opened, synchronize, closed, etc.
            $table->foreignId('repository_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('pull_request_id')->nullable()->constrained()->onDelete('set null');
            $table->json('payload');
            $table->string('status'); // received, processing, processed, failed
            $table->text('error_message')->nullable();
            $table->timestamps();
            
            $table->index(['event_type', 'action']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_events');
    }
};
