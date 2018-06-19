<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();  
            $table->string('name');
            $table->string('description')->default('No description provided');
            $table->string('category')->default('Others');
            $table->string('picture')->default('https://placeholdit.co//i/200x200?&bg=ecf0f1&fc=e74c3c&text=Goodle%20Course');
            $table->string('theme')->default('city');
            $table->string('color')->default('rgba(55,74,93,.58)');
            $table->boolean('public')->default(0);
            $table->unsignedInteger('admin_id');
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('courses');
    }
}
