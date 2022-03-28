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

/**
 * 服务监控
 * Class ZookeeperServiceWatcher
 * @package JoseChan\Cloud\Zookeeper\Discovery\Discovery
 */
class ZookeeperServiceWatcher implements Watcher
{
    private $path;

    /** @var ZookeeperDiscovery $discovery */
    private $discovery;

    /**
     * ZookeeperWatcher constructor.
     * @param $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @param ZookeeperDiscovery $discovery
     */
    public function setDiscovery(ZookeeperDiscovery $discovery): void
    {
        $this->discovery = $discovery;
    }

    /**
     * 监控
     */
    public function watch()
    {
        $list = $this->discovery->getClient()->getChildren($this->path, $this);
        if (!empty($list)) {
            foreach ($list as $value) {
                if (!$this->discovery->hasService($value)) {
                    // 服务还未注册到本地，注册监听
                    $nodeDiscovery = new ZookeeperNodeWatcher($this->path . "/" . $value, $value);
                    $this->discovery->addNodeWatcher($nodeDiscovery);
                }
            }
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
