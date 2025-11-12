<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            //$table->foreignId('file_upload_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('file_upload_id')->nullable(); // no login user
            $table->string('unique_key')->index();
            $table->string('product_title')->nullable();
            $table->text('product_description')->nullable();
            $table->string('style')->nullable();
            $table->string('sanmar_mainframe_color')->nullable();
            $table->string('size')->nullable();
            $table->string('color_name')->nullable();
            $table->decimal('piece_price', 12, 2)->nullable();
            $table->timestamps();
        });
    }

  public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
