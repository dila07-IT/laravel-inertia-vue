<?php

namespace Dilansabah\LaravelInertiaVue\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class InstallCommand extends Command
{
    protected array $skippedImportantFiles = [];

    protected $signature = 'inertia-vue:install {--force : Overwrite existing files}';

    protected $description = 'Install Inertia.js and Vue.js scaffolding';

    public function handle(): int
    {
        $this->info('Installing Inertia + Vue...');

        $this->installComposerPackages();
        $this->installNpmPackages();
        $this->copyStubs();
        $this->addRouteComment();
        $this->generateSetupGuide();

        $this->newLine();
        $this->info('Inertia + Vue installed successfully.');

        return self::SUCCESS;
    }

    protected function installComposerPackages(): void
    {
        if ($this->isComposerPackageInstalled('inertiajs/inertia-laravel')) {
            $this->warn('Composer package already installed: inertiajs/inertia-laravel');
            return;
        }

        $this->line('Installing composer package: inertiajs/inertia-laravel');

        $this->runShellCommand('composer require inertiajs/inertia-laravel');
    }

    protected function installNpmPackages(): void
    {
        $packages = [
            'vue',
            '@inertiajs/vue3',
            '@vitejs/plugin-vue',
        ];

        $missingPackages = [];

        foreach ($packages as $package) {
            if (! $this->isNpmPackageInstalled($package)) {
                $missingPackages[] = $package;
            }
        }

        if (empty($missingPackages)) {
            $this->warn('NPM packages already installed.');
            return;
        }

        $this->line('Installing npm packages: ' . implode(', ', $missingPackages));

        $this->runShellCommand('npm install ' . implode(' ', $missingPackages));
    }

    protected function copyStubs(): void
{
    $files = new Filesystem();

    $stubs = [
        'app.js.stub' => [
            'target' => resource_path('js/app.js'),
            'ask' => true,
        ],
        'Home.vue.stub' => [
            'target' => resource_path('js/Pages/Home.vue'),
            'ask' => false,
        ],
        'app.blade.php.stub' => [
            'target' => resource_path('views/app.blade.php'),
            'ask' => false,
        ],
        'vite.config.js.stub' => [
            'target' => base_path('vite.config.js'),
            'ask' => true,
        ],
    ];

    foreach ($stubs as $stub => $config) {
        $this->copyFile($files, $stub, $config['target'], $config['ask']);
    }
}

    protected function addRouteComment(): void
    {
        $routePath = base_path('routes/web.php');

        if (! file_exists($routePath)) {
            $this->error("Route file not found: {$routePath}");
            return;
        }

        $content = file_get_contents($routePath);

        // prevent duplicate comment
        if (str_contains($content, 'Inertia Vue Starter')) {
            $this->warn('Route comment already exists in routes/web.php');
            return;
        }

        $comment = "\n\n// ================= Inertia Vue Starter =================\n";
        $comment .= "// If you want to use Inertia, uncomment the route below:\n\n";
        $comment .= "// use Inertia\\Inertia;\n\n";
        $comment .= "// Route::get('/', function () {\n";
        $comment .= "//     return inertia('Home');\n";
        $comment .= "// });\n\n";
        $comment .= "// ======================================================\n";

        file_put_contents($routePath, $content . $comment);

        $this->info("Route comment added to: {$routePath}");
    }

    protected function copyFile(Filesystem $files, string $stub, string $target, bool $askBeforeOverwrite = false): void
    {
        $source = dirname(__DIR__, 2) . '/stubs/' . $stub;

        $directory = dirname($target);

        if (! $files->isDirectory($directory)) {
            $files->makeDirectory($directory, 0755, true);
        }

        if ($files->exists($target)) {
            if ($this->option('force')) {
                $files->copy($source, $target);
                $this->info("Overwritten: {$target}");
                return;
            }

            if ($askBeforeOverwrite) {
                if ($this->confirm("File already exists: {$target}. Do you want to overwrite it?", false)) {
                    $files->copy($source, $target);
                    $this->info("Overwritten: {$target}");
                } else {
                    $this->warn("Skipped existing file: {$target}");

                    $this->skippedImportantFiles[] = [
                        'stub' => $stub,
                        'target' => $target,
                    ];
                }

                return;
            }

            $this->warn("Skipped existing file: {$target}");
            return;
        }

        $files->copy($source, $target);
        $this->info("Created: {$target}");
    }


    protected function generateSetupGuide(): void
    {
        if (empty($this->skippedImportantFiles)) {
            return;
        }

        $content = "# Inertia Vue Setup Required\n\n";
        $content .= "Some important files were skipped during installation.\n";
        $content .= "Please update them manually using the content below.\n\n";

        foreach ($this->skippedImportantFiles as $file) {
            $stubPath = dirname(__DIR__, 2) . '/stubs/' . $file['stub'];
            $stubContent = file_get_contents($stubPath);

            $language = match ($file['stub']) {
                'Home.vue.stub' => 'vue',
                'app.blade.php.stub' => 'php',
                'app.js.stub', 'vite.config.js.stub' => 'js',
                default => '',
            };

            $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file['target']);

            $content .= "## {$relativePath}\n\n";
            $content .= "```{$language}\n";
            $content .= $stubContent;
            $content .= "\n```\n\n";
        }

        $content .= "After updating your files, run:\n\n";
        $content .= "```bash\n";
        $content .= "npm run dev\n";
        $content .= "```\n";

        $path = base_path('inertia-vue-setup.md');

        file_put_contents($path, $content);

        $this->info("Setup guide created: {$path}");
    }

    protected function runShellCommand(string $command): void
    {
        $this->line("Running: {$command}");

        passthru($command, $exitCode);

        if ($exitCode !== 0) {
            $this->error("Command failed: {$command}");
        }
    }

    protected function isComposerPackageInstalled(string $package): bool
    {
        $composerLock = base_path('composer.lock');

        if (! file_exists($composerLock)) {
            return false;
        }

        $content = json_decode(file_get_contents($composerLock), true);

        $packages = array_merge(
            $content['packages'] ?? [],
            $content['packages-dev'] ?? []
        );

        foreach ($packages as $installedPackage) {
            if (($installedPackage['name'] ?? null) === $package) {
                return true;
            }
        }

        return false;
    }
    protected function isNpmPackageInstalled(string $package): bool
    {
        $packageJson = base_path('package.json');

        if (! file_exists($packageJson)) {
            return false;
        }

        $content = json_decode(file_get_contents($packageJson), true);

        $dependencies = $content['dependencies'] ?? [];
        $devDependencies = $content['devDependencies'] ?? [];

        return isset($dependencies[$package]) || isset($devDependencies[$package]);
    }
}
