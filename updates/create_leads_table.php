<?php namespace Pbs\Campaign\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateLeadsTable extends Migration
{
    public function up()
    {
        Schema::create('pbs_campaign_leads', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedInteger('campaign_id');
            $table->unsignedInteger('subscriber_id');
            $table->unsignedInteger('link_id');
            $table->text('meta')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('campaign_id');
            $table->index('subscriber_id');
            $table->index('link_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pbs_campaign_leads');
    }
}
