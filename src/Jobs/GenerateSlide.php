<?php

namespace Partymeister\Slides\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Partymeister\Slides\Helpers\Browsershot;

/**
 * Class GenerateSlide
 */
class GenerateSlide implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var Model
     */
    public $slide;

    public $namePrefix;

    /**
     * Create a new job instance.
     */
    public function __construct(Model $slide, $namePrefix)
    {
        $this->slide = $slide;
        $this->namePrefix = $namePrefix;
    }

    /**
     * Execute the job.
     *
     * @throws \Spatie\Browsershot\Exceptions\CouldNotTakeBrowsershot
     */
    public function handle()
    {
        $filenameForPreview = base_path().'/storage/app/temp/'.$this->namePrefix.'_preview_'.$this->slide->id.'.png';
        $filenameForFinal = base_path().'/storage/app/temp/'.$this->namePrefix.'_final_'.$this->slide->id.'.png';

        Browsershot::url(url('/backend/'.$this->namePrefix.'/'.$this->slide->id))
            ->setBinPath(__DIR__.'/../../resources/assets/bin/browser.js')
            ->waitUntilNetworkIdle()
            ->windowSize(1920, 1080)// ->debug()
            // ->fit(Manipulations::FIT_CONTAIN, 1920, 1080)
            ->save($filenameForFinal);

        Browsershot::url(url('/backend/'.$this->namePrefix.'/'.$this->slide->id.'?preview=true'))
            ->setBinPath(__DIR__.'/../../resources/assets/bin/browser.js')
            ->waitUntilNetworkIdle()
            ->windowSize(1920, 1080)// ->debug()
            // ->fit(Manipulations::FIT_CONTAIN, 1920, 1080)
            ->save($filenameForPreview);

        $this->slide->clearMediaCollection('preview');
        $this->slide->clearMediaCollection('final');
        $this->slide->addMedia($filenameForPreview)
            ->toMediaCollection('preview', 'media');
        $this->slide->addMedia($filenameForFinal)
            ->toMediaCollection('final', 'media');
    }
}
