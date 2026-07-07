<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pull_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repository_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->bigInteger('github_pr_id')->unique();
            $table->integer('number');
            $table->string('title');
            $table->string('state');
            $table->string('head_branch');
            $table->string('base_branch');
            $table->string('github_url');
            $table->timestamp('github_created_at')->nullable();
            $table->timestamp('github_updated_at')->nullable();
            $table->timestamp('github_closed_at')->nullable();
            $table->timestamp('github_merged_at')->nullable();
            $table->timestamps();
            
            $table->index(['repository_id', 'github_pr_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pull_requests');
    }
};
