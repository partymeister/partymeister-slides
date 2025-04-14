<?php

namespace Partymeister\Slides\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Class GenerateSlide
 */
class ProcessScreenshotJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Model
     */
    public Model $slide;

    /**
     * @var
     */
    public string $fileName;

    public string $collection;

    /**
     * @param \Partymeister\Slides\Models\Slide $slide
     * @param string $fileName
     * @param string $collection
     */
    public function __construct(Model $slide, string $fileName, string $collection)
    {
        $this->slide = $slide;
        $this->fileName = $fileName;
        $this->collection = $collection;
    }

    /**
     * @return void
     */
    public function handle()
    {
        $this->slide->clearMediaCollection($this->collection);
        try {
            $this->slide->addMedia($this->fileName)
                  ->toMediaCollection($this->collection, 'media');
        } catch (\Exception $e) {
            Log::warning("Can't generate screenshot for slide .".$this->slide->id, [$e->getMessage()]);
        }
        Log::info('Screenshot attached to slide', [$this->slide->id, $this->fileName, $this->collection]);
    }
}
