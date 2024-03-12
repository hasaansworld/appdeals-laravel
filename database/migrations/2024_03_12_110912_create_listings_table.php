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
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('icon_url')->nullable();
            $table->string('app_name');
            $table->string('short_description');
            $table->string('youtube_url')->nullable();
            $table->string('image_1')->nullable();
            $table->string('image_2')->nullable();
            $table->string('image_3')->nullable();
            $table->text('introduction');
            $table->float('price');
            $table->string('price_currency');
            $table->float('old_price')->nullable();
            $table->string('type')->nullable();
            $table->string('url')->nullable();
            $table->dateTime('ends_on');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
