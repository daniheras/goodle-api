<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_user', function (Blueprint $table) {
        $table->increments('id');
          $table->timestamp('uploaded_at')->useCurrent();
          $table->timestamp('updated_at')->useCurrent();          
          $table->unsignedInteger('user_id');
          $table->unsignedInteger('task_id');
          $table->unique(['user_id', 'task_id']);
          $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
          $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
          $table->string('file');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_user');
    }
}
