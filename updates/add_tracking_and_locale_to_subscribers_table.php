<?php namespace Pbs\Campaign\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Database\Schema\Blueprint;

class AddTrackingAndLocaleToSubscribersTable extends Migration
{
    public function up()
    {
        Schema::table('pbs_campaign_subscribers', function (Blueprint $table) {
            $table->enum('source', ['system', 'user'])->default('system')->after('hash');
            $table->string('ip')->nullable()->after('source');
            $table->string('locale')->nullable()->after('ip');
            $table->integer('unsubscribe_campaign_id')->unsigned()->nullable();
            $table->string('unsubscribe_reason')->nullable();
        });
    }

    public function down()
    {
        Schema::table('pbs_campaign_subscribers', function (Blueprint $table) {
            $table->dropColumn([
                'source',
                'ip',
                'locale',
                'unsubscribe_campaign_id',
                'unsubscribe_reason',
            ]);
        });
    }
}