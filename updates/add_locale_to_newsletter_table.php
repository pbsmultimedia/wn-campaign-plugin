<?php namespace Pbs\Campaign\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;
use Winter\Storm\Database\Schema\Blueprint;

class AddLocaleToNewsletterTable extends Migration
{
    public function up()
    {
        Schema::table('pbs_campaign_newsletters', function (Blueprint $table) {
            $table->string('locale')->nullable()->after('email');
        });
    }

    public function down()
    {
        Schema::table('pbs_campaign_newsletters', function (Blueprint $table) {
            $table->dropColumn('locale');
        });
    }
}