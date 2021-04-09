<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('multiple-tokens-auth.table'), function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->index();
            $table->string('token', config('multiple-tokens-auth.token.char_length'))->collation('utf8mb4_bin')->unique();
            $table->dateTime('expired_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop(config('multiple-tokens-auth.table'));
    }
}
