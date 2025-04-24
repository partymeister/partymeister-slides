<?php

namespace Partymeister\Slides\Helpers;

use Illuminate\Support\Facades\Redis;

class ScreenshotHelper
{
    protected $driver = null;

    /**
     * @return void
     */
    public function screenshot($url, $file, $slideId, $class, $collection)
    {
        $this->enqueue($url, $file, $slideId, $class, $collection);
    }

    public function enqueue(string $url, string $filename, int $slideId, string $class, string $collection): void
    {
        // Define queue name
        $queueName = 'screenshot';

        // 1. Generate job ID (auto-increment)
        $jobId = Redis::incr("bull:$queueName:id");

        // 2. Build job payload
        $jobKey = "bull:$queueName:$jobId";
        $data = [
            'url' => $url,
            'fileName' => $filename,
            'slideId' => $slideId,
            'class' => $class,
            'collection' => $collection,
        ];

        $job = [
            'name' => 'screenshot', // Job name
            'data' => json_encode($data),
            'opts' => json_encode([]),
            'progress' => 0,
            'attemptsMade' => 0,
            'finishedOn' => null,
            'processedOn' => null,
            'timestamp' => (string) now()->timestamp * 1000,
        ];

        // 3. Store the job hash
        Redis::hmset($jobKey, $job);

        // 4. Push job ID to the 'wait' list
        Redis::rpush("bull:$queueName:wait", $jobId);

        // (Optional) Notify workers
        Redis::publish("bull:$queueName:events", json_encode([
            'event' => 'waiting',
            'jobId' => $jobId,
            'prev' => null,
        ]));
    }
}
