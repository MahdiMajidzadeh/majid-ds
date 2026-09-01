<?php

namespace MajidDs;

use BladeUI\Icons\Factory as IconFactory;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use MajidDs\Support\Icons;
use MajidDs\Support\Jalali;
use MajidDs\Support\Persian;

class MajidDsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mds.php', 'mds');

        $this->app->singleton(MdsManager::class);
        $this->app->alias(MdsManager::class, 'mds');

        // The `Mds` facade alias is registered through composer.json's
        // extra.laravel.aliases, so an app can opt out with dont-discover
        // and keep its own class of that name.
    }

    public function boot(): void
    {
        $this->bootComponentPath();
        $this->bootTagCompiler();
        $this->bootDirectives();
        $this->bootIconSets();
        $this->bootPublishing();
    }

    /**
     * Register a blade-icons set per configured Hugeicons Pro style. Pro icons
     * are never shipped with this package — these read from the app's own
     * licensed export, so only licensed users ever hold the files.
     */
    protected function bootIconSets(): void
    {
        $sets = (array) config('mds.icons.sets', []);

        if ($sets === [] || ! class_exists(IconFactory::class)) {
            return;
        }

        $this->callAfterResolving(IconFactory::class, function (IconFactory $factory) use ($sets) {
            foreach ($sets as $style => $path) {
                if (! is_string($path) || ! is_dir($path)) {
                    continue;
                }

                $prefix = Icons::PRO_PREFIX.'-'.$style;

                $factory->add($prefix, ['path' => $path, 'prefix' => $prefix]);
            }
        });
    }

    protected function bootComponentPath(): void
    {
        // App-level overrides take precedence, mirroring Flux's publish/override flow...
        if (file_exists(resource_path('views/mds'))) {
            Blade::anonymousComponentPath(resource_path('views/mds'), 'mds');
        }

        Blade::anonymousComponentPath(__DIR__.'/../resources/views/mds', 'mds');
    }

    protected function bootTagCompiler(): void
    {
        $compiler = new MdsTagCompiler(
            app('blade.compiler')->getClassComponentAliases(),
            app('blade.compiler')->getClassComponentNamespaces(),
            app('blade.compiler'),
        );

        $this->app->bind('mds.compiler', fn () => $compiler);

        app('blade.compiler')->precompiler(fn ($in) => $compiler->compile($in));
    }

    protected function bootDirectives(): void
    {
        // @fa($value) — convert Latin digits to Persian digits...
        Blade::directive('fa', fn ($expression) => "<?php echo e(\\MajidDs\\Support\\Persian::digits({$expression})); ?>");

        // @faNum($value, $decimals = 0) — format a number with Persian separators and digits...
        Blade::directive('faNum', fn ($expression) => "<?php echo e(\\MajidDs\\Support\\Persian::number({$expression})); ?>");

        // @toman($amount) / @rial($amount) — formatted money with currency label...
        Blade::directive('toman', fn ($expression) => "<?php echo e(\\MajidDs\\Support\\Persian::money({$expression}, 'toman')); ?>");
        Blade::directive('rial', fn ($expression) => "<?php echo e(\\MajidDs\\Support\\Persian::money({$expression}, 'rial')); ?>");

        // @jalali($date, $format = 'j F Y') — render a date in the Jalali calendar...
        Blade::directive('jalali', fn ($expression) => "<?php echo e(\\MajidDs\\Support\\Jalali::format({$expression})); ?>");

        // @mdsFonts — <link> tags for the Vazirmatn font (put in <head>)...
        Blade::directive('mdsFonts', fn () => <<<'HTML'
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@100..900&display=swap" rel="stylesheet">
        HTML);
    }

    protected function bootPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/mds.php' => config_path('mds.php'),
        ], 'mds-config');

        $this->publishes([
            __DIR__.'/../resources/css/mds.css' => resource_path('css/vendor/mds.css'),
        ], 'mds-assets');

        $this->publishes([
            __DIR__.'/../resources/views/mds' => resource_path('views/mds'),
        ], 'mds-views');
    }
}
