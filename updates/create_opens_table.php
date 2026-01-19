<?php namespace Pbs\Campaign\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateVisualizationsTable extends Migration
{
    public function up()
    {
        Schema::create('pbs_campaign_visualizations', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedInteger('subscriber_id');
            $table->unsignedInteger('campaign_id');
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('subscriber_id');
            $table->index('campaign_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pbs_campaign_visualizations');
    }
}
