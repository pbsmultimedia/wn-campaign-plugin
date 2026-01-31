<?php namespace Pbs\Campaign\Components;

use Cms\Classes\ComponentBase;
use Pbs\Campaign\Classes\NewsletterPreviewService;

class Newsletter extends ComponentBase
{
    public function componentDetails(): array
    {
        return [
            'name'        => 'Newsletter Renderer',
            'description' => 'Renders a Campaign newsletter by its (campaign) ID.'
        ];
    }

    public function onRun()
    {
        try {
            $id = (int) $this->param('id');
            $hash = $this->param('hash');
            $newsletter = (new NewsletterPreviewService())->render($id, $hash);
            $this->page['renderedHtml'] = $newsletter;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->controller->run('404');
        }
    }
}
