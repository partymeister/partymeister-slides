<?php

namespace Partymeister\Slides\Helpers;

use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Exceptions\CouldNotTakeBrowsershot;
use Spatie\Browsershot\Exceptions\ElementNotFound;

/**
 * Class Browsershot
 *
 * @package Partymeister\Slides\Helpers
 */
class Browsershot extends \Spatie\Browsershot\Browsershot
{
    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @return $this
     */
    public function debug()
    {
        $this->debug = true;

        return $this;
    }

    /**
     * @param string $targetPath
     * @throws CouldNotTakeBrowsershot
     * @throws ElementNotFound
     */
    public function save(string $targetPath)
    {
        if (! $this->debug) {
            parent::save($targetPath);
        }

        $extension = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));

        if ($extension === '') {
            throw CouldNotTakeBrowsershot::outputFileDidNotHaveAnExtension($targetPath);
        }

        if ($extension === 'pdf') {
            return $this->savePdf($targetPath);
        }

        $command = $this->createScreenshotCommand($targetPath);

        $output = $this->callBrowser($command);

        $this->cleanupTemporaryHtmlFile();

        if (! file_exists($targetPath)) {
            throw CouldNotTakeBrowsershot::chromeOutputEmpty($targetPath);
        }

        if (! $this->imageManipulations->isEmpty()) {
            $this->applyManipulations($targetPath);
        }

        Log::info($output);
    }
}
