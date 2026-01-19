<?php namespace Pbs\Campaign\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;
use Illuminate\Database\Schema\Blueprint;
use Pbs\Campaign\Enums\CampaignRecipientStatus;

class CreateCampaignRecipientsTable extends Migration
{
    public function up()
    {
        Schema::create('pbs_campaign_recipients', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            // maybe just use the subscriber ID instead of the email?
            // that way email and hash are accessible via the subscriber model
            $table->unsignedInteger('subscriber_id');
            // $table->string('email')->unique();
            $table->unsignedInteger('campaign_id');
            $table->unsignedInteger('newsletter_id');
            // Improve performance for lookups
            $table->index('campaign_id');
            $table->index('subscriber_id');
            $table->enum('status', CampaignRecipientStatus::values())->default(CampaignRecipientStatus::Pending->value);
            // nice to have: if failed, store the error message? or just log it? guess that is logged by default?
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
        Schema::dropIfExists('pbs_campaign_recipients');
    }
}
