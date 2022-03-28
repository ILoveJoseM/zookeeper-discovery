<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2022-03-27
 * Time: 15:10
 */

namespace JoseChan\Cloud\Zookeeper\Discovery\Middlewares;


use GuzzleHttp\Promise\Promise;
use GuzzleHttp\Psr7\Uri;
use JoseChan\Cloud\Commons\Client\Discovery\DiscoveryClient;
use JoseChan\Cloud\Zookeeper\Discovery\Discovery\ZookeeperDiscovery;
use JoseChan\Cloud\Zookeeper\Discovery\ServiceInstance;
use Psr\Http\Message\RequestInterface;

/**
 * 中间件
 * Class LoadBalanceMiddleware
 * @package JoseChan\Cloud\Zookeeper\Discovery\Middlewares
 */
class LoadBalanceMiddleware
{

    /** @var ZookeeperDiscovery $discovery */
    private $discovery;

    /**
     * LoadBalanceMiddleware constructor.
     * @param DiscoveryClient $discovery
     */
    public function __construct(DiscoveryClient $discovery)
    {
        $this->discovery = $discovery;
    }

    public function __invoke(RequestInterface $request, $options)
    {
        $uri = $request->getUri();
        $host = $uri->getHost();

        /** @var ServiceInstance $service */
        $service = $this->discovery->getLoadBalanced()->choose($host);
        if (!empty($service)) {
            $uri->withHost($service->getHost());
        }

        $request->withUri($uri);

        return new Promise();
    }
}
