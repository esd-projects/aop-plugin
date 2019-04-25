<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/23
 * Time: 18:27
 */

namespace GoSwoole\Plugins\Aop;


use Go\Aop\Aspect;
use GoSwoole\BaseServer\Server\Exception\ConfigException;

class AopConfig
{
    /**
     * use 'false' for production mode
     * @var bool
     */
    private $debug = false;
    /**
     * Cache directory
     * @var string
     */
    private $cacheDir;
    /**
     * Include paths restricts the directories where aspects should be applied
     * @var string[]
     */
    private $includePaths;

    /**
     * @var Aspect[]
     */
    private $aspects = [];

    public function __construct(...$includePaths)
    {
        foreach ($includePaths as $includePath){
            $this->addIncludePath($includePath);
        }
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * @param string $cacheDir
     */
    public function setCacheDir(string $cacheDir): void
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * @return string[]
     */
    public function getIncludePaths()
    {
        return $this->includePaths;
    }

    /**
     * @param string[] $includePaths
     */
    public function setIncludePaths(array $includePaths): void
    {
        $this->includePaths = $includePaths;
    }

    /**
     * @param string $includePath
     */
    public function addIncludePath(string $includePath)
    {
        $includePath = realpath($includePath);
        if ($includePath === false) return;
        if (!array_key_exists($includePath, $this->includePaths)) {
            $this->includePaths[] = $includePath;
        }
    }

    public function addAspect(Aspect $param)
    {
        $this->aspects[] = $param;
    }

    /**
     * @return Aspect[]
     */
    public function getAspects(): array
    {
        return $this->aspects;
    }

    /**
     * 构建config
     * @throws ConfigException
     */
    public function buildConfig()
    {
        if (empty($this->includePaths)) {
            throw new ConfigException("includePaths不能为空");
        }
    }
}