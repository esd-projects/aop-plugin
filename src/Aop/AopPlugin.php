<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/23
 * Time: 18:23
 */

namespace ESD\Plugins\Aop;

use Doctrine\Common\Cache\ArrayCache;
use ESD\Core\Context\Context;
use ESD\Core\Exception;
use ESD\Core\PlugIn\AbstractPlugin;
use ESD\Core\Plugins\Config\ConfigException;
use ESD\Core\Server\Server;
use rabbit\aop\Aop;
use rabbit\aop\AopAspectKernel;
use rabbit\aop\GoAspectContainer;

/**
 * AOP插件
 * Class AopPlugin
 * @package ESD\Plugins\Aop
 */
class AopPlugin extends AbstractPlugin
{
    /**
     * @var AopConfig
     */
    private $aopConfig;
    /**
     * @var array
     */
    private $options;

    /**
     * AopPlugin constructor.
     * @param AopConfig|null $aopConfig
     * @throws \ReflectionException
     */
    public function __construct(?AopConfig $aopConfig = null)
    {
        parent::__construct();
        if ($aopConfig == null) {
            $aopConfig = new AopConfig();
        }
        $this->aopConfig = $aopConfig;
    }

    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "Aop";
    }

    /**
     * 初始化
     * @param Context $context
     * @throws ConfigException
     * @throws Exception
     * @throws \Exception
     */
    public function init(Context $context)
    {
        parent::init($context);
        //有文件操作必须关闭全局RuntimeCoroutine
        enableRuntimeCoroutine(false);
        $cacheDir = $this->aopConfig->getCacheDir() ?? Server::$instance->getServerConfig()->getBinDir() . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . "aop";
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        $this->aopConfig->merge();
        //自动添加src目录
        $serverConfig = Server::$instance->getServerConfig();
        $this->aopConfig->addIncludePath($serverConfig->getSrcDir());
        $this->aopConfig->addIncludePath($serverConfig->getVendorDir() . "/esd");
        $this->aopConfig->setCacheDir($cacheDir);
        $this->aopConfig->merge();
    }

    /**
     * 在服务启动前
     * @param Context $context
     * @throws Exception
     */
    public function beforeServerStart(Context $context)
    {
        $serverConfig = Server::$instance->getServerConfig();
        $this->options = [
            'debug' => $serverConfig->isDebug(), // use 'false' for production mode
            'appDir' => $serverConfig->getRootDir(), // Application root directory
            'cacheDir' => $this->aopConfig->getCacheDir(), // Cache directory
            'includePaths' => $this->aopConfig->getIncludePaths(),
            'excludePaths' => $this->aopConfig->getExcludePaths()
        ];
        if (!$this->aopConfig->isFileCache()) {
            $this->options['annotationCache'] = new ArrayCache();
            $this->options['containerClass'] = new GoAspectContainer();
            new Aop(AopAspectKernel::class, $this->aopConfig->getAspects(), $this->options);
        } else {
            new Aop(FileCacheAspectKernel::class, $this->aopConfig->getAspects(), $this->options);
        }

    }

    /**
     * 在进程启动前
     * @param Context $context
     */
    public function beforeProcessStart(Context $context)
    {
        $this->ready();
    }

    /**
     * @return AopConfig
     */
    public function getAopConfig(): AopConfig
    {
        return $this->aopConfig;
    }

    /**
     * @param AopConfig $aopConfig
     */
    public function setAopConfig(AopConfig $aopConfig): void
    {
        $this->aopConfig = $aopConfig;
    }

    /**
     * @return FileCacheAspectKernel
     */
    public function getApplicationAspectKernel(): FileCacheAspectKernel
    {
        return $this->applicationAspectKernel;
    }
}