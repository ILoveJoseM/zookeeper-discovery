<?php
/**
 * Created by PhpStorm.
 * User: chenyu
 * Date: 2022-03-23
 * Time: 12:12
 */

namespace JoseChan\Cloud\Zookeeper\Discovery;


use JoseChan\Cloud\Commons\Client\Discovery\Watcher;
use JoseChan\Cloud\Commons\Client\ServiceRegistry\Registration;
use JoseChan\Cloud\Zookeeper\Discovery\Discovery\ZookeeperNodeWatcher;

/**
 * 服务
 * Class ServiceInstance
 * @package JoseChan\Cloud\Zookeeper\Discovery
 */
class ServiceInstance implements Registration, \JsonSerializable
{
    private $id;
    private $name;
    private $address;
    private $port;
    private $register_time;
    private $current_connected = 0;

    private $isInit = false;

    /** @var ZookeeperNodeWatcher|null $watcher */
    private $watcher = null;

    /**
     * ServiceInstance constructor.
     * @param $name
     * @param $address
     * @param $port
     */
    public function __construct($name, $address, $port)
    {
        $this->name = $name;
        $this->address = $address;
        $this->port = $port;
        $this->init();
    }


    private function init()
    {
        if (!$this->isInit) {
            $this->id = $this->generateId();
        }
    }

    private function generateId()
    {
        return sha1($this->name . microtime() . rand(1, 100));
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $id
     * @return bool
     */
    public function setId($id)
    {
        if ($this->isInit) {
            return false;
        }
        $this->id = $id;
        return true;
    }

    /**
     * @param mixed $register_time
     * @return bool
     */
    public function setRegisterTime($register_time)
    {
        if ($this->isInit) {
            return false;
        }
        $this->register_time = $register_time;
        return true;
    }

    /**
     * @param int $current_connected
     * @return bool
     */
    public function setCurrentConnected(int $current_connected)
    {
        if ($this->isInit) {
            return false;
        }
        $this->current_connected = $current_connected;
        return true;
    }

    /**
     * @param bool $isInit
     * @return bool
     */
    public function setIsInit(bool $isInit)
    {
        if ($this->isInit) {
            return false;
        }
        $this->isInit = $isInit;
        return true;
    }

    /**
     * @param ZookeeperNodeWatcher $watcher
     */
    public function setWatcher($watcher): void
    {
        $this->watcher = $watcher;
    }

    /**
     * @return ZookeeperNodeWatcher|null
     */
    public function getWatcher(): ?ZookeeperNodeWatcher
    {
        return $this->watcher;
    }


    public function getHost()
    {
        return $this->address . ":" . $this->port;
    }

    /**
     * @return int
     */
    public function getCurrentConnected(): int
    {
        return $this->current_connected;
    }

    public function incrCurrentConnected()
    {
        $this->current_connected++;
    }

    public static function create($data)
    {
        $data = json_decode($data, true);
        $instance = new self($data['name'], $data['address'], $data['port']);
        $instance->setId($data['id']);
        $instance->setRegisterTime($data['register_time']);
        $instance->setCurrentConnected($data['current_connected']);
        $instance->setIsInit(true);

        return $instance;
    }

    public function jsonSerialize()
    {
        return json_encode([
            "id" => $this->id,
            "name" => $this->name,
            "address" => $this->address,
            "port" => $this->port,
            "register_time" => $this->register_time = time(),
            "current_connected" => $this->current_connected,
        ]);
    }

}
