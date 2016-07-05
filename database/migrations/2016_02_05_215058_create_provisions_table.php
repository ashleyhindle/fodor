<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProvisionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provisions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid');
            $table->string('email');
            $table->string('digitalocean_token'); // TODO: Don't need to do this somehow
            $table->string('digitalocean_uuid'); // TODO: Not ideal for supporting other providers
            $table->index('digitalocean_uuid');

            $table->integer('exitcode')->nullable();
            $table->string('repo'); // ashleyhindle/fodor-example
            $table->index('repo');
            $table->bigInteger('dropletid')->nullable();
            $table->string('ipv4');
            $table->string('ipv6');
            $table->string('ipv4_private');

            $table->string('region')->default('nyc3'); // TODO: Separate table
            $table->string('size')->default('512mb'); // TODO: Separate table
            $table->string('distro')->default('ubuntu-14-04-x64'); // TODO: Separate table

            $table->string('subdomain');
            $table->string('status')->default('new'); // new, active, provision, ready, off, archived

            $table->timestamp('datestarted')->nullable();
            $table->timestamp('dateready')->nullable(); // Provision ready for use
            $table->timestamp('datedeleted')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('provisions');
    }
}
