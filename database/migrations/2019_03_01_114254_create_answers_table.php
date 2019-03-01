<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->increments('id');
	        $table->text('answer', 65535)->nullable();
	        $table->integer('is_deleted')->default(0);
	        $table->integer('question_id')->nullable();
	        $table->integer('rank')->default(1);
	        $table->integer('user_id')->unsigned()->nullable()->default(0);
	        $table->float('bayesian_rating', 10, 0)->nullable();
	        $table->integer('must_update')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('answers');
    }
}
