<?php

namespace Partymeister\Slides\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * Class PartymeisterChromiumProcess
 */
class PartymeisterWebdriverCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'partymeister:slides:webdriver {status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start or stop the chromium webdriver';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (is_null(config('partymeister-slides.webdriver', null))) {
            $this->error('No chromium-webdriver binary defined');

            return;
        }
        if (! is_file(config('partymeister-slides.webdriver', null))) {
            $this->error('No chromium-webdriver binary found in '.config('partymeister-slides.webdriver'));

            return;
        }

        if ($this->argument('status') === 'start') {
            $process = new Process([config('partymeister-slides.webdriver')]);
            $process->start();
            sleep(5);
            $this->info('Started chromium webdriver');
        } elseif ($this->argument('status') === 'stop') {
            $process = new Process(['killall '.config('partymeister-slides.webdriver')]);
            $process->run();
            $this->info('Stopped chromium webdriver');
        }
    }
}
