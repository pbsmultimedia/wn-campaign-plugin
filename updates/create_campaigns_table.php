<?php namespace Pbs\Campaign\Updates;

use Schema;
use Winter\Storm\Database\Schema\Blueprint;
use Winter\Storm\Database\Updates\Migration;
use Pbs\Campaign\Enums\CampaignStatus;

class CreateCampaignsTable extends Migration
{
    public function up()
    {
        Schema::create('pbs_campaign_campaigns', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('subject')->nullable();
            $table->integer('newsletter_id')->unsigned()->nullable();
            $table->text('description')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->enum('status', CampaignStatus::values())->default(CampaignStatus::Draft->value);
            $table->timestamps();
        });

        # guess this name is not intuitive.. it's a pivot table..!
        Schema::create('pbs_campaign_campaign_list', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->integer('campaign_id')->unsigned();
            $table->integer('list_id')->unsigned();
            $table->primary(['campaign_id', 'list_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('pbs_campaign_campaign_list');
        Schema::dropIfExists('pbs_campaign_campaigns');
    }
}