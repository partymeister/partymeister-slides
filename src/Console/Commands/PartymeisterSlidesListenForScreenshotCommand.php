<?php

namespace Partymeister\Slides\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Partymeister\Slides\Events\ScreenshotUpdated;

class PartymeisterSlidesListenForScreenshotCommand extends Command
{
    protected $signature = 'partymeister:slides:screenshots:listen';

    protected $description = 'Listen for screenshot job completion events from Redis';

    public function handle()
    {
        $streamKey = 'stream:screenshot:done';
        $lastId = Redis::get('stream:screenshot:count'); // or use '$' to only get new entries
        if (!$lastId) {
            $lastId = (int)0;
        }

        while (true) {
            $result = Redis::xread([$streamKey => $lastId], 0); // 0 = block indefinitely

            if ($result && isset($result[$streamKey])) {
                foreach ($result[$streamKey] as $id => $event) {

                    $this->info("Received new message from stream: {$streamKey} with id: {$id}");
                    //var_dump($event);

                    $model = $event['class']::find((int)$event['slideId']);

                    if (!is_null($model)) {
                        // Dispatch job or handle inline
                        \Partymeister\Slides\Jobs\ProcessScreenshotJob::dispatch($model, $event['fileName'], $event['collection']);
                        ScreenshotUpdated::dispatch($model);
                    } else {
                        Log::warning("Model not found for class: {$event['class']} and slideId: {$event['slideId']}");
                    }

                    $lastId = $id;
                    Redis::set('stream:screenshot:count', $lastId); // Update lastId in Redis
                }
            }
        }
    }
}
