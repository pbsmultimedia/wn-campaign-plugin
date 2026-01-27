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
                $table->string('status')->default('active');                
                $table->boolean('is_subscribed')->default(true);
                $table->timestamp('unsubscribed_at')->nullable();
                $table->enum('unsubscribed_by', ['user', 'system'])->nullable();                
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('pbs_campaign_subscribers');
    }
}
