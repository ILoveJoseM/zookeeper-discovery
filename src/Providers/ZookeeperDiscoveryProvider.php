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
use JoseChan\Cloud\Zookeeper\Discovery\ServiceInstance;

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

        $this->app->singleton(\Zookeeper::class, function () use ($config) {
            $host = !empty($config['host']) ? $config['host'] : "localhost:2181";
            return new \Zookeeper($host);
        });

        $this->app->singleton(ServiceRegistry::class, ZookeeperRegistry::class);
        $this->app->singleton(ServiceInstance::class, function ($app) use ($config) {
            $host = !empty($config['service_host']) ? $config['service_host'] : "127.0.0.1:80";
            $name = !empty($config['service_name']) ? $config['service_name'] : "service";

            $host = explode(":", $host);
            $port = !empty($host[1]) ? $host[1] : "80";
            $address = !empty($host[0]) ? $host[0] : "127.0.0.1";

            return new ServiceInstance($name, $address, $port);
        });

        $this->app->when(ZookeeperRegistry::class)
            ->needs('$bathPath')
            ->give($config["register_path"]);

        $this->app->bind(Watcher::class, ZookeeperServiceWatcher::class);

        $this->app->bind(Rule::class, !empty($config['load_balanced_rule']) ? $config['load_balanced_rule'] : DefaultRule::class);
        $this->app->singleton(LoadBalancedClient::class, ZookeeperLoadBalanced::class);

        $this->app->singleton(DiscoveryClient::class, function (Application $app) use ($config) {
            $watches = [];
            if (!empty($config['discovery']["service_watches"])) {
                foreach ($config['discovery']["service_watches"] as $path) {
                    $watches[] = $app->make(Watcher::class, ["path" => $path]);
                }
            }

            return new ZookeeperDiscovery($app->make(\Zookeeper::class), $app->make(LoadBalancedClient::class), $watches);
        });

    }
}
