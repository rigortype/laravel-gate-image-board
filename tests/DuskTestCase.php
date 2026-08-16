<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Support\Collection;
use Laravel\Dusk\Dusk;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;
use Symfony\Component\Process\Process;

abstract class DuskTestCase extends BaseTestCase
{
    protected static ?Process $applicationServer = null;

    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        $database = dirname(__DIR__).'/database/dusk.sqlite';

        if (! file_exists($database)) {
            touch($database);
        }

        Dusk::selectorHtmlAttribute('data-test');

        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }

        static::startApplicationServer();
        register_shutdown_function([static::class, 'stopApplicationServer']);
    }

    /**
     * Serve the application so the browser can reach it.
     */
    protected static function startApplicationServer(): void
    {
        if (static::$applicationServer?->isRunning()) {
            return;
        }

        static::$applicationServer = new Process([
            PHP_BINARY,
            'artisan',
            'serve',
            '--host=127.0.0.1',
            '--port=8001',
        ], dirname(__DIR__), timeout: null);

        static::$applicationServer->start();

        $deadline = microtime(true) + 10;

        while (microtime(true) < $deadline) {
            $connection = @fsockopen('127.0.0.1', 8001);

            if (is_resource($connection)) {
                fclose($connection);

                return;
            }

            usleep(100_000);
        }

        static::stopApplicationServer();

        throw new \RuntimeException('Unable to start the Dusk application server on 127.0.0.1:8001.');
    }

    /**
     * Stop the application server started for Dusk.
     */
    protected static function stopApplicationServer(): void
    {
        static::$applicationServer?->stop();
        static::$applicationServer = null;
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
            '--disable-smooth-scrolling',
        ])->when(isset($_ENV['CI']) || getenv('CI'), function (Collection $items) {
            return $items->merge([
                '--no-sandbox',
                '--disable-dev-shm-usage',
            ]);
        })->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        if ($binary = $this->chromeBinary()) {
            $options->setBinary($binary);
        }

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    /**
     * Resolve a Chrome / Chromium binary if the default name is not on PATH.
     */
    protected function chromeBinary(): ?string
    {
        $configured = $_ENV['DUSK_CHROME_PATH'] ?? env('DUSK_CHROME_PATH');

        if (is_string($configured) && $configured !== '' && is_executable($configured)) {
            return $configured;
        }

        foreach ([
            '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
            '/Applications/Google Chrome Beta.app/Contents/MacOS/Google Chrome Beta',
            '/Applications/Google Chrome Canary.app/Contents/MacOS/Google Chrome Canary',
            '/Applications/Chromium.app/Contents/MacOS/Chromium',
        ] as $path) {
            if (is_executable($path)) {
                return $path;
            }
        }

        return null;
    }
}
