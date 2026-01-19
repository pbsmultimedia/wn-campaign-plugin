<?php namespace Pbs\Campaign\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Illuminate\Database\Schema\Blueprint;
use Pbs\Campaign\Enums\NewsletterStatus;

class CreateNewslettersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('pbs_campaign_newsletters')) {
            Schema::create('pbs_campaign_newsletters', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->string('template');
                $table->text('content')->nullable();
                $table->enum('status', NewsletterStatus::values())->default(NewsletterStatus::Draft);
                $table->boolean('is_public')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('pbs_campaign_newsletters');
    }
}
