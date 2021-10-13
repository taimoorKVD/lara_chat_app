<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->onDelete('cascade');
            $table->unsignedInteger('sender_id');
            $table->unsignedInteger('receiver_id')->nullable();
            $table->tinyInteger('type')->default(0)->comment('1:group_message, 0:personal_message');
            $table->tinyInteger('seen_status')->default(0)->comment('1:seen');
            $table->tinyInteger('delivery_status')->default(0)->comment('1:delivered');
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
        Schema::dropIfExists('user_messages');
    }
}
