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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('company_code')->unique();
            $table->string('company_name');
            $table->text('company_about')->nullable();
            $table->string('company_business_email')->unique();
            $table->string('company_phone');
            $table->string('company_logo')->nullable();
            $table->text('address')->nullable();
            $table->string('linkdin_url')->nullable();
            $table->string('pan_number')->unique();
            $table->string('pan_document');
            $table->string('gst_number')->unique();
            $table->string('gst_document');
            $table->bigInteger('manager_user_id');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
