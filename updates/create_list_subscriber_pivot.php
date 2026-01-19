<?php namespace Pbs\Campaign\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateListSubscriberPivot extends Migration
{
    public function up()
    {
        // is this verification needed? check the best practices
        // checked some plugins, and they don't use it
        if (!Schema::hasTable('pbs_campaign_list_subscriber')) {
            Schema::create('pbs_campaign_list_subscriber', function (Blueprint $table) {
                $table->engine = 'InnoDB';
                $table->unsignedInteger('list_id');
                $table->unsignedInteger('subscriber_id');
                $table->primary(['list_id', 'subscriber_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('pbs_campaign_list_subscriber');
    }
}
