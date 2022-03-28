<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2022-03-23
 * Time: 12:15
 */

namespace JoseChan\Cloud\Zookeeper\Discovery\Registry;


use JoseChan\Cloud\Commons\Client\ServiceRegistry\ServiceRegistry;
use JoseChan\Cloud\Zookeeper\Discovery\ServiceInstance;

/**
 * 服务注册器
 * Class ZookeeperRegistry
 * @package JoseChan\Cloud\Zookeeper\Discovery\Registry
 */
class ZookeeperRegistry implements ServiceRegistry
{
    /** @var string $bathPath */
    private $bathPath;

    /** @var \Zookeeper $client */
    private $client;

    private $registrationList = [];

    /**
     * ZookeeperRegistry constructor.
     * @param string $bathPath
     * @param \Zookeeper $client zookeeper客户端
     */
    public function __construct($bathPath, \Zookeeper $client)
    {
        if (substr($bathPath, -1, 1) != "/") {
            $bathPath .= "/";
        }
        $this->bathPath = $bathPath;
        $this->client = $client;
    }

    /**
     * @param ServiceInstance $registration
     * @return string
     */
    private function getPath($registration)
    {
        return $this->bathPath . "{$registration->getName()}/{$registration->getId()}";
    }

    /**
     * @param ServiceInstance $registration
     * @return mixed
     * @throws \Exception|\ZookeeperException
     */
    public function register($registration)
    {
        $path = $this->getPath($registration);
        $this->createPath($path);

        $path = $this->client->create($path, json_encode($registration, 320), [
            [
                'perms' => \Zookeeper::PERM_ALL,
                'scheme' => 'world',
                'id' => 'anyone'
            ]
        ], \Zookeeper::EPHEMERAL);

        if ($path !== false) {
            $this->registrationList[$registration->getName()][$registration->getId()] = ["path" => $path, "registration" => $registration, "status" => true];
        } else {
            throw new \Exception("register service failure");
        }

        return $path;
    }

    private function createPath($path)
    {
        $paths = explode("/", substr($path, 1));

        $existsPath = "";
        foreach ($paths as $subPath) {
            if ($existsPath . "/" . $subPath == $path) {
                break;
            }

            if (!$this->client->exists($existsPath . "/" . $subPath)) {
                $this->client->create($existsPath . "/" . $subPath, null, [[
                    'perms' => \Zookeeper::PERM_ALL,
                    'scheme' => 'world',
                    'id' => 'anyone'
                ]]);
            }

            $existsPath = $existsPath . "/" . $subPath;
        }
    }

    /**
     * @param ServiceInstance $registration
     * @return bool|mixed
     */
    public function deregister($registration)
    {
        if (!isset($this->registrationList[$registration->getName()][$registration->getId()])) {
            return false;
        } else {
            unset($this->registrationList[$registration->getName()][$registration->getId()]);
            return $this->client->delete($this->getPath($registration));
        }
    }
}
