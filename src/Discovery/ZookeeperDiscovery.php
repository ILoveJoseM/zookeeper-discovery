<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2022-03-23
 * Time: 14:53
 */

namespace JoseChan\Cloud\Zookeeper\Discovery\Discovery;


use JoseChan\Cloud\Commons\Client\Discovery\DiscoveryClient;
use JoseChan\Cloud\Commons\Client\LoadBalancer\LoadBalancedClient;
use JoseChan\Cloud\Zookeeper\Discovery\LoadBalanced\ZookeeperLoadBalanced;

class ZookeeperDiscovery implements DiscoveryClient
{
    /** @var \Zookeeper $client */
    private $client;

    /** @var ZookeeperServiceWatcher[] */
    private $serviceWatches;

    /** @var bool $isListening */
    private $isListening = false;

    /** @var ZookeeperNodeWatcher[] $nodeWatches */
    private $nodeWatches;

    /** @var ZookeeperLoadBalanced $loadBalanced */
    private $loadBalanced;

    private $serviceNode = [];


    /**
     * ZookeeperDiscovery constructor.
     * @param $client
     * @param ZookeeperServiceWatcher[] $serviceWatches
     * @param $loadBalanced
     */
    public function __construct(\Zookeeper $client, LoadBalancedClient $loadBalanced, $serviceWatches = null)
    {
        $this->client = $client;
        if (!empty($serviceWatches)) {
            /** @var ZookeeperServiceWatcher $serviceWatch */
            foreach ($serviceWatches as $serviceWatch) {
                $serviceWatch->setDiscovery($this);
            }
        }
        $this->serviceWatches = $serviceWatches;
        $this->loadBalanced = $loadBalanced;
    }

    /**
     * 发现
     */
    public function discovery()
    {
        $this->isListening = true;
        if (!empty($this->serviceWatches)) {
            foreach ($this->serviceWatches as $watch) {
                $watch->watch();
            }
        }

        if (!empty($this->nodeWatches)) {
            foreach ($this->nodeWatches as $watch) {
                $watch->watch();
            }
        }

    }

    /**
     * 判断是否有该服务
     * @param $serviceName
     * @return bool
     */
    public function hasService($serviceName)
    {
        return isset($this->serviceNode[$serviceName]);
    }

    /**
     * 本地注册服务信息
     * @param $serviceName
     * @param $serviceInstances
     */
    public function registerService($serviceName, $serviceInstances)
    {
        $this->serviceNode[$serviceName] = $serviceInstances;
        $this->loadBalanced->setServices($this->serviceNode);
    }

    /**
     * 获取负载均衡器
     * @return ZookeeperLoadBalanced
     */
    public function getLoadBalanced(): ZookeeperLoadBalanced
    {
        return $this->loadBalanced;
    }


    /**
     * 添加服务监控
     * @param ZookeeperServiceWatcher $watcher
     */
    public function addServiceWatcher($watcher)
    {
        if ($this->isListening) {
            $watcher->setDiscovery($this);
            $watcher->watch();
        }

        $this->serviceWatches[] = $watcher;
    }

    /**
     * 添加服务节点信息监控
     * @param ZookeeperNodeWatcher $watcher
     */
    public function addNodeWatcher($watcher)
    {
        if ($this->isListening) {
            $watcher->setDiscovery($this);
            $watcher->watch();
        }

        $this->nodeWatches[] = $watcher;
    }

    /**
     * @return \Zookeeper
     */
    public function getClient(): \Zookeeper
    {
        return $this->client;
    }
}
