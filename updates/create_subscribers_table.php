<?php namespace Pbs\Campaign\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSubscribersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('pbs_campaign_subscribers')) {
            Schema::create('pbs_campaign_subscribers', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->nullable();
                $table->string('email')->unique();
                $table->string('hash')->unique();
                // guess this is not needed?
                // or use for bounced
                $table->string('status')->default('active');
                // is_subscribed maybe?
                $table->boolean('is_subscribed')->default(true);
                $table->timestamp('unsubscribed_at')->nullable();
                // TODO: enum
                $table->enum('unsubscribed_by', ['user', 'system'])->nullable();
                // nice to have: unsubscribe_campaign_id and unsubscribe_reason
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('pbs_campaign_subscribers');
    }
}
