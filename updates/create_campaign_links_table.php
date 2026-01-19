<?php

namespace Pbs\Campaign\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLinksTable extends Migration
{
    public function up()
    {
        Schema::create('pbs_campaign_links', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('campaign_id')->unsigned();
            // $table->string('hash')->unique();
            // removing the unique for now
            $table->string('hash');
            $table->text('original_url');
            // why? track total clicks - does that make sense? guess not?
            // $table->integer('clicks')->default(0);
            $table->timestamps();

            $table->foreign('campaign_id')
                ->references('id')
                ->on('pbs_campaign_campaigns')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pbs_campaign_links');
    }
}