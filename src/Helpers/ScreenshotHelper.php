<?php

namespace Partymeister\Slides\Helpers;

use Exception;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;

class ScreenshotHelper
{
    protected $driver = null;

    /**
     * ScreenshotHelper constructor.
     * Initialize a browser
     */
    //public function __construct()
    //{
    //    try {
    //        $host = 'http://localhost:9515';
    //        $options = new ChromeOptions();
    //        $options->addArguments([
    //            '--headless=old',
    //            '--window-size=1920,1080',
    //            '--disable-gpu',
    //            '--no-sandbox'
    //        ]);
    //
    //        $capabilities = DesiredCapabilities::chrome();
    //        $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);
    //        $this->driver = RemoteWebDriver::create($host, $capabilities, 5000);
    //    } catch (Exception $e) {
    //        exit($e->getMessage());
    //        exit('Webdriver not running');
    //        // Do nothing for now
    //    }
    //}

    /**
     * @param $url
     * @param $file
     */
    public function screenshot($url, $file)
    {
        try {
            Browsershot::url($url)->setChromePath('/usr/bin/chromium')->newHeadless()->windowSize(1920, 1080)->save($file);
            //if ($this->driver) {
            //    $this->driver->get($url);
            //    $this->driver->takeScreenshot($file);
            //}
        } catch (Exception $e) {
            Log::error("Screenshothelper: ". $e->getMessage());
        }
    }

    /**
     * Throw away the browser
     */
    //public function __destruct()
    //{
    //    if ($this->driver instanceof RemoteWebDriver) {
    //        $this->driver->close();
    //    }
    //}
}
