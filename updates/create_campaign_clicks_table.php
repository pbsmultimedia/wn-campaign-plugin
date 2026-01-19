<?php namespace Pbs\Campaign\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateClicksTable extends Migration
{
    public function up()
    {
        Schema::create('pbs_campaign_clicks', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');

            $table->integer('link_id')->unsigned();
            // maybe store the campaign_id to make it easier to get clicks by campaign
            $table->integer('subscriber_id')->unsigned();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('link_id')
                ->references('id')
                ->on('pbs_campaign_links')
                ->onDelete('cascade');
            
            $table->foreign('subscriber_id')
                ->references('id')
                ->on('pbs_campaign_subscribers')
                ->onDelete('cascade');

            $table->index(['link_id', 'subscriber_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pbs_campaign_clicks');
    }
}