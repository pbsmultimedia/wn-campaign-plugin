<?php namespace Pbs\Campaign\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBlogExtendedFields extends Migration
{
    public function up()
    {
        if (Schema::hasTable('winter_blog_posts')) {
            Schema::table('winter_blog_posts', function (Blueprint $table) {
                $table->text('extra_info')->nullable();
                $table->string('featured_badge')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('winter_blog_posts')) {
            Schema::table('winter_blog_posts', function (Blueprint $table) {
                $table->dropColumn(['extra_info', 'featured_badge']);
            });
        }
    }
}
