<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('profile')->nullable();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('force_password_change')->default(false);
            $table->boolean('created_by_admin')->default(false); 
            $table->enum('role', ['b2b','deliveryrider', 'salesofficer', 'superadmin'])->default('b2b');
            $table->string('otp_code')->nullable();
            $table->timestamp('otp_expire')->nullable();
            $table->boolean('status')->default(true);
            $table->text('about')->nullable();
            $table->decimal('credit_limit', 10,2)->default(300000);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
