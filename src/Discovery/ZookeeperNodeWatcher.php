<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2022-03-25
 * Time: 11:10
 */

namespace JoseChan\Cloud\Zookeeper\Discovery\Discovery;


use JoseChan\Cloud\Commons\Client\Discovery\Watcher;
use JoseChan\Cloud\Commons\Client\LoadBalancer\LoadBalancedClient;
use JoseChan\Cloud\Zookeeper\Discovery\LoadBalanced\ZookeeperLoadBalanced;
use JoseChan\Cloud\Zookeeper\Discovery\ServiceInstance;

/**
 * 节点信息监控器
 * Class ZookeeperNodeWatcher
 * @package JoseChan\Cloud\Zookeeper\Discovery\Discovery
 */
class ZookeeperNodeWatcher implements Watcher
{
    private $path;

    /** @var ZookeeperDiscovery $discovery */
    private $discovery;

    /** @var string $serviceName */
    private $serviceName;

    /**
     * ZookeeperWatcher constructor.
     * @param $path
     * @param $serviceName
     */
    public function __construct($path, $serviceName)
    {
        $this->path = $path;
        $this->serviceName = $serviceName;
    }

    /**
     * @param ZookeeperDiscovery $discovery
     */
    public function setDiscovery(ZookeeperDiscovery $discovery): void
    {
        $this->discovery = $discovery;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }


    /**
     *  监控
     */
    public function watch()
    {
        $list = $this->discovery->getClient()->getChildren($this->path, $this);
        if (!empty($list)) {
            $serviceInstances = [];
            foreach ($list as $value) {
                $json = $this->discovery->getClient()->get($this->path . "/" . $value);
                if (!empty($json)) {
                    $serviceInstances[] = $instance = ServiceInstance::create(json_decode($json, true));
                    $instance->setWatcher($this);
                }
            }

            $this->discovery->registerService($this->serviceName, $serviceInstances);
        }
    }

    public function __invoke(...$args)
    {
        $type = $args[0];

        if ($type == 4) {
            $this->watch();
        }
    }
}
