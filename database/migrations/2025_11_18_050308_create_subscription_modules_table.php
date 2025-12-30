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
        Schema::create('subscription_modules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subscription_id');
            $table->string('module_unique_key');
            $table->timestamps();

            // Foreign key for subscription_id
            $table->foreign('subscription_id')
                ->references('id')
                ->on('subscriptions')
                ->onDelete('cascade');

            // Foreign key for module_unique_key (references modules table)
            $table->foreign('module_unique_key')
                ->references('unique_key')
                ->on('modules')
                ->onDelete('cascade');

            $table->index('module_unique_key');
            
            $table->index(['subscription_id', 'module_unique_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_modules');
    }
};
