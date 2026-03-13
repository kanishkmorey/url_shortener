<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clicks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('url_id');
            $table->timestamp('clicked_at', 3)->useCurrent();
            $table->binary('ip')->nullable(); // supports IPv4 + IPv6
            $table->char('country', 2)->nullable();
            $table->string('referrer', 512)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->softDeletes();

            $table->index('url_id');
            $table->index(['url_id', 'clicked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clicks');
    }
};
