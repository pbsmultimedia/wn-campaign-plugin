<?php namespace Pbs\Campaign\Components;

// can't remember where and what for this was used..?

use File;
use Cms\Classes\ComponentBase;
use Pbs\Campaign\Models\Newsletter as NewsletterModel;
use Winter\Storm\Parse\Syntax\Parser as SyntaxParser;
use Winter\Storm\Parse\Twig as TwigParser;
use Pbs\Campaign\Classes\NewsletterPreviewService;

class Newsletter extends ComponentBase
{
    public function componentDetails(): array
    {
        return [
            'name'        => 'Newsletter Renderer',
            'description' => 'Renders a Campaign newsletter by its ID.'
        ];
    }

    public function onRun()
    {
        $id = (int) $this->param('id');
        $hash = $this->param('hash');
        $newsletter = (new NewsletterPreviewService())->render($id, $hash);
        return $newsletter;
    }
}
