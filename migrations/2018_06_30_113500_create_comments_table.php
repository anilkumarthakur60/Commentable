<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('commenter');
            $table->morphs('commentable');
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->longText('comment');
            $table->boolean('approved')->default(true);
            $table->unsignedBigInteger('child_id')->nullable();
            $table->foreign('child_id')->references('id')->on('comments')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
