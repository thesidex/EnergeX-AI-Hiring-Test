<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 200);
            $table->text('content');
            $table->unsignedInteger('user_id');
            $table->timestamp('created_at')->useCurrent(); // required by spec

            // Optional: allow updated_at if you want
            // $table->timestamp('updated_at')->nullable();

            // Optional: soft deletes
            // $table->softDeletes();

            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
