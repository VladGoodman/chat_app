<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessageProgressTable extends Migration
{
    public function up()
    {
        Schema::create('message_progress', function (Blueprint $table) {
            $table->bigInteger('account_id');
            $table->bigInteger('event_id');
            $table->boolean('received_at')->nullable();

            $table->unique(['account_id', 'event_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('message_progress');
    }
}
