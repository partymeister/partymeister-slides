<?php

namespace Partymeister\Slides\Helpers;

use Illuminate\Support\Facades\Redis;

class ScreenshotHelper
{
    public function screenshot(string $url, string $fileName, int $slideId, string $class, string $collection): void
    {
        Redis::rpush('screenshot:jobs', json_encode([
            'url'        => $url,
            'fileName'   => $fileName,
            'slideId'    => $slideId,
            'class'      => $class,
            'collection' => $collection,
        ]));
    }
}
