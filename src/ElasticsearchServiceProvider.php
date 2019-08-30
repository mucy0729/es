<?php

namespace Qdd\Es;

use Qdd\Es\Commands\ReindexCommand;
use Elasticsearch\ClientBuilder as ElasticBuilder;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;
use Qdd\Es\Commands\ListIndicesCommand;
use Qdd\Es\Commands\CreateIndexCommand;
use Qdd\Es\Commands\DropIndexCommand;
use Qdd\Es\Commands\UpdateIndexCommand;

/**
 * Class ElasticsearchServiceProvider
 * @package Qdd\Es
 */
class ElasticsearchServiceProvider extends ServiceProvider
{

    /**
     * ElasticsearchServiceProvider constructor.
     * @param Application $app
     */
    function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        $this->mergeConfigFrom(
            dirname(__FILE__) . '/config/es.php', 'es'
        );

        $this->publishes([
            dirname(__FILE__) . '/config/' => config_path(),
        ], "es.config");

        // Auto configuration with lumen framework.

        if (str_contains($this->app->version(), 'Lumen')) {
            $this->app->configure("es");
        }

        // Resolve Laravel Scout engine.

        if (class_exists("Laravel\\Scout\\EngineManager")) {

            try {

                $this->app->make(EngineManager::class)->extend('es', function () {

                    $config = config('es.connections.' . config('scout.es.connection'));

                    return new ScoutEngine(
                        ElasticBuilder::create()->setHosts($config["servers"])->build(),
                        $config["index"]
                    );

                });

            } catch (BindingResolutionException $e) {

                // Class is not resolved.
                // Laravel Scout service provider was not loaded yet.

            }

        }


    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        // Package commands available for laravel or lumen higher than 5.1

        if(version_compare($this->app->version(), '5.1', ">=") or starts_with($this->app->version(), "Lumen")) {

            if ($this->app->runningInConsole()) {

                // Registering commands

                $this->commands([
                    ListIndicesCommand::class,
                    CreateIndexCommand::class,
                    UpdateIndexCommand::class,
                    DropIndexCommand::class,
                    ReindexCommand::class
                ]);

            }
        }

        $this->app->bind('es', function () {
            return new Connection();
        });
    }
}
