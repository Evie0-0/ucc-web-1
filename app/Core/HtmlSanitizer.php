<?php
declare(strict_types=1);

namespace Core;

use HTMLPurifier;
use HTMLPurifier_Config;

final class HtmlSanitizer {
    private HTMLPurifier $purifier;

    public function __construct() {
        $config = HTMLPurifier_Config::createDefault();

        $config->set('HTML.Allowed', 'p,b,strong,i,em,u,ul,ol,li[data-list],a[href],br,h1,h2,h3,h4,h5,h6,blockquote,pre,code,span');
        $config->set('HTML.Nofollow', true);
        $config->set('URI.AllowedSchemes', [
            'http' => true,
            'https' => true,
        ]);
        $config->set('Attr.EnableID', true);

        $this->purifier = new HTMLPurifier($config);
    }

    public function sanitize(string $html): string {
        return $this->purifier->purify($html);
    }
}
