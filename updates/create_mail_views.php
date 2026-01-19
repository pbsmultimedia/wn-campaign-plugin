<?php namespace Pbs\Campaign\Updates;

use Schema;
use Winter\Storm\Database\Updates\Migration;
use System\Models\MailTemplate;
use System\Models\MailLayout;
use Winter\Storm\Mail\MailParser;
use Illuminate\Support\Facades\File;

class CreateMailViews extends Migration
{
    public function up()
    {
        $this->createMailLayouts();
        $this->createMailTemplates();
    }

    // guess this is not needed / supposed to exist
    // a layout is created on the backend
    // including one to get started
    protected function createMailLayouts()
    {
        $layouts = [
            'newsletter' =>
                plugins_path('pbs/campaign/views/newsletter/layout-email.htm'),
        ];

        foreach ($layouts as $code => $filePath) {
            if (!File::exists($filePath)) {
                continue;
            }

            $layoutContent = File::get($filePath);
            // extract parts from layout
            $layoutParts = MailParser::parse($layoutContent);

            // this verification will not allow to install latest versions?
            // how to handle this?
            // also if layout was edited on backend, is stored on DB and file on disk is outdated..
            // guess that to override the layout that exist on DB, one has to delete it first?
            if (!MailLayout::where('code', $code)->exists()) {
                MailLayout::create([
                    'code'         => $code,
                    'name'         => 'Campaign Newsletter Layout',
                    'content_html' => $layoutParts['html'],
                    'content_text' => $layoutParts['text'],
                ]);
            }
        }
    }

    // the same for the templates?
    protected function createMailTemplates()
    {
        $templates = [
            'pbs.campaign::newsletter.index' =>
                plugins_path('pbs/campaign/views/newsletter/index.htm'),
        ];

        // guess that only one template is needed
        // so, the foreach is unnecessary..?
        foreach ($templates as $code => $filePath) {
            if (!File::exists($filePath)) {
                continue;
            }

            if (!MailTemplate::where('code', $code)->exists()) {

                // Extract @subject
                $content = File::get($filePath);
                preg_match('/^\s*@subject\s+(.*)$/m', $content, $match);
                $subject = $match[1] ?? 'Email';

                MailTemplate::create([
                    'code'         => $code,
                    'subject'      => $subject,
                    'content_html' => $content,
                    // does this match? if so, how?
                    // there is some inconsistency, so leaving as is
                    'layout'       => 'pbs.campaign::mail.layout-newsletter',
                    // 'layout'       => 'newsletter',
                    'description'  => 'newsletter',
                ]);
            }
        }
    }
}
