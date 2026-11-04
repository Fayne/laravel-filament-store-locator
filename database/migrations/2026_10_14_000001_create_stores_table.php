<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('name');
            $table->unique(['tenant_id', 'name']);
            $table->text('description')->nullable();
            $table->text('logo')->nullable();
            $table->json('banner')->nullable();
            $table->boolean('status')->default(true);
            $table->unsignedSmallInteger('weight')->default(0);
            $table->string('internal_serial_number')->nullable();
            $table->json('subway_lines')->nullable();
            $table->json('brands')->nullable();
            $table->string('continent')->nullable();
            $table->string('country')->nullable();
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->string('website')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('postal_code')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->json('opening_hours')->nullable();
            $table->text('opening_hours_text')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
