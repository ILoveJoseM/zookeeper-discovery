<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2022-03-25
 * Time: 11:47
 */

namespace JoseChan\Cloud\Zookeeper\Discovery\Providers;


use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use JoseChan\Cloud\Commons\Client\Discovery\DiscoveryClient;
use JoseChan\Cloud\Commons\Client\Discovery\Watcher;
use JoseChan\Cloud\Commons\Client\LoadBalancer\LoadBalancedClient;
use JoseChan\Cloud\Commons\Client\LoadBalancer\Rule;
use JoseChan\Cloud\Commons\Client\ServiceRegistry\ServiceRegistry;
use JoseChan\Cloud\Zookeeper\Discovery\Discovery\ZookeeperDiscovery;
use JoseChan\Cloud\Zookeeper\Discovery\Discovery\ZookeeperServiceWatcher;
use JoseChan\Cloud\Zookeeper\Discovery\LoadBalanced\DefaultRule;
use JoseChan\Cloud\Zookeeper\Discovery\LoadBalanced\ZookeeperLoadBalanced;
use JoseChan\Cloud\Zookeeper\Discovery\Registry\ZookeeperRegistry;

class ZookeeperDiscoveryProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([__DIR__ . '/../../config/zookeeper-discovery.php' => config_path("zookeeper-discovery.php")], "zookeeper-discovery");
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $config = config("zookeeper-discovery");

        if (!$config) {
            $config = include __DIR__ . '/../../config/zookeeper-discovery.php';
        }

        $this->app->bind(\Zookeeper::class, function () use ($config) {
            $host = !empty($config['host']) ? $config['host'] : "localhost:2181";
            return new \Zookeeper($host);
        });

        $this->app->bind(ServiceRegistry::class, ZookeeperRegistry::class);

        $this->app->when(ZookeeperRegistry::class)
            ->needs('$bathPath')
            ->give($config["register"]['path']);

        $this->app->bind(Watcher::class, ZookeeperServiceWatcher::class);

        $this->app->bind(Rule::class, !empty($config['load_balanced']['rule']) ? $config['load_balanced']['rule'] : DefaultRule::class);
        $this->app->singleton(LoadBalancedClient::class, ZookeeperLoadBalanced::class);

        $this->app->bind(DiscoveryClient::class, function (Application $app) use ($config) {
            $watches = [];
            if (!empty($config['discovery']["service_watches"])) {
                foreach ($config['discovery']["service_watches"] as $watch) {
                    $watches[] = $app->make(Watcher::class, $watch);
                }
            }

            return new ZookeeperDiscovery($app->make(\Zookeeper::class), $app->make(LoadBalancedClient::class), $watches);
        });

    }
}
