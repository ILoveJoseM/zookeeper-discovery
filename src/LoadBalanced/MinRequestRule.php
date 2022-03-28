<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2022-03-27
 * Time: 16:07
 */

namespace JoseChan\Cloud\Zookeeper\Discovery\LoadBalanced;


use JoseChan\Cloud\Zookeeper\Discovery\ServiceInstance;

class MinRequestRule extends AbstractRule
{

    public function choose($serviceNodes)
    {
        $instance = null;
        $minRequest = -1;
        /** @var ServiceInstance $node */
        foreach ($serviceNodes as $node) {
            if (($request = $node->getCurrentConnected()) < $minRequest || $minRequest == -1) {
                $instance = $node;
                $minRequest = $request;
            }
        }

        $path = $instance->getWatcher()->getPath() . "/" . $instance->getId();
        /** @var \Zookeeper $client */
        $client = $this->loadBalance->getDiscovery()->getClient();
        $instance->incrCurrentConnected();
        $client->set($path, json_encode($instance));

        return $instance;
    }


}
