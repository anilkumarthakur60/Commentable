<?php

use Anil\Comments\Comment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
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
            $table->foreignIdFor(Comment::class, 'child_id')->nullable()->constrained('comments')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
