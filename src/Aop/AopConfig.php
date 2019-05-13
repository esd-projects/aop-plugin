<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/23
 * Time: 18:27
 */

namespace ESD\Plugins\Aop;


use Go\Aop\Aspect;
use ESD\BaseServer\Plugins\Config\BaseConfig;
use ESD\BaseServer\Server\Exception\ConfigException;

class AopConfig extends BaseConfig
{
    const key = "aop";
    /**
     * Cache directory
     * @var string
     */
    protected $cacheDir;
    /**
     * Include paths restricts the directories where aspects should be applied
     * @var string[]
     */
    protected $includePaths = [];

    /**
     * @var Aspect[]
     */
    private $aspects = [];

    public function __construct(...$includePaths)
    {
        parent::__construct(self::key);
        foreach ($includePaths as $includePath) {
            $this->addIncludePath($includePath);
        }
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
        $key = str_replace(ROOT_DIR,"",$includePath);
        $key = str_replace("/",".",$key);
        $this->includePaths[$key] = $includePath;
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