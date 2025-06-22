<?php

namespace Gillyware\Gatekeeper\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;
    use WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    }

    protected function renderBladeString(string $string, array $data = []): string
    {
        $pathToTemporaryBladeFile = $this->makeTemporaryBladeFile($string);
        $view = app('view')->file($pathToTemporaryBladeFile);

        $rendered = trim($view->with($data)->render());

        @unlink($pathToTemporaryBladeFile);

        return $rendered;
    }

    private function makeTemporaryBladeFile(string $contents): string
    {
        $dir = sys_get_temp_dir();
        $path = tempnam($dir, 'blade_').'.blade.php';
        file_put_contents($path, $contents);

        return $path;
    }
}
