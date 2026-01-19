<?php namespace Pbs\Campaign\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateListsTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('pbs_campaign_lists')) {
            Schema::create('pbs_campaign_lists', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name')->unique();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('pbs_campaign_lists');
    }
}
