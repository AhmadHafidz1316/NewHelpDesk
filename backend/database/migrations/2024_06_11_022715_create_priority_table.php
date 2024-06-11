<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('priority', function (Blueprint $table) {
            $table->id();
            $table->string('priority_name', 100);
            $table->integer('resolution_time');
            $table->integer('response_time');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('priority');
    }
};
