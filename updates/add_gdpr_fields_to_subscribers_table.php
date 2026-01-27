<?php namespace Pbs\Campaign\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Database\Schema\Blueprint;

class AddGdprFieldsToSubscribersTable extends Migration
{
    public function up()
    {
        Schema::table('pbs_campaign_subscribers', function (Blueprint $table) {
            $table->timestamp('verified_at')->nullable()->after('hash');
            $table->timestamp('consent_given_at')->nullable()->after('verified_at');
            $table->string('consent_version')->nullable()->after('consent_given_at');
        });
    }

    public function down()
    {
        Schema::table('pbs_campaign_subscribers', function (Blueprint $table) {
            $table->dropColumn(['verified_at', 'consent_given_at', 'consent_version']);
        });
    }
}