<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/23
 * Time: 18:23
 */

namespace ESD\Plugins\Aop;

use ESD\BaseServer\Plugins\Event\EventDispatcher;
use ESD\BaseServer\Server\Context;
use ESD\BaseServer\Server\Plugin\AbstractPlugin;
use ESD\BaseServer\Server\Plugin\PluginManagerEvent;

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
     * @var ApplicationAspectKernel
     */
    private $applicationAspectKernel;

    /**
     * AopPlugin constructor.
     * @param AopConfig|null $aopConfig
     * @throws \DI\DependencyException
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
     * 在服务启动前
     * @param Context $context
     * @return mixed
     * @throws \ESD\BaseServer\Exception
     * @throws \ESD\BaseServer\Server\Exception\ConfigException
     */
    public function beforeServerStart(Context $context)
    {
        //有文件操作必须关闭全局RuntimeCoroutine
        enableRuntimeCoroutine(false);
        $eventDispatcher = $context->getDeepByClassName(EventDispatcher::class);
        $this->aopConfig->buildConfig();
        $cacheDir = $this->aopConfig->getCacheDir() ?? $context->getServer()->getServerConfig()->getBinDir() . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . "aop";
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        $this->applicationAspectKernel = ApplicationAspectKernel::getInstance();
        $this->applicationAspectKernel->setConfig($this->aopConfig);
        $this->setToDIContainer(ApplicationAspectKernel::class, $this->applicationAspectKernel);
        //自动添加src目录
        $serverConfig = $context->getServer()->getServerConfig();
        $this->aopConfig->addIncludePath($serverConfig->getSrcDir());
        $this->aopConfig->setCacheDir($cacheDir);
        $this->aopConfig->merge();
        //初始化
        $this->applicationAspectKernel->init([
            'debug' => $serverConfig->isDebug(), // use 'false' for production mode
            'appDir' => $context->getServer()->getServerConfig()->getRootDir(), // Application root directory
            'cacheDir' => $cacheDir, // Cache directory
            // Include paths restricts the directories where aspects should be applied, or empty for all source files
            'includePaths' => $this->aopConfig->getIncludePaths()
        ]);
        if ($eventDispatcher instanceof EventDispatcher) {
            goWithContext(function () use ($eventDispatcher, $context) {
                $channel = $eventDispatcher->listen(PluginManagerEvent::PlugAfterServerStartEvent, null, true);
                $channel->pop();
                $this->applicationAspectKernel->initAspect();
            });
        }
    }

    /**
     * 在进程启动前
     * @param Context $context
     * @return mixed
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
     * @return ApplicationAspectKernel
     */
    public function getApplicationAspectKernel(): ApplicationAspectKernel
    {
        return $this->applicationAspectKernel;
    }
}