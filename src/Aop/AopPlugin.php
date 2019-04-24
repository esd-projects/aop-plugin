<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/23
 * Time: 18:23
 */

namespace GoSwoole\Plugins\Aop;

use GoSwoole\BaseServer\Plugins\Event\EventDispatcher;
use GoSwoole\BaseServer\Server\Context;
use GoSwoole\BaseServer\Server\Plugin\AbstractPlugin;
use GoSwoole\BaseServer\Server\Plugin\PluginManagerEvent;

/**
 * AOP插件
 * Class AopPlugin
 * @package GoSwoole\Plugins\Aop
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
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "Aop";
    }

    private function clear_dir($path = null)
    {
        if (is_dir($path)) {    //判断是否是目录
            $p = scandir($path);     //获取目录下所有文件
            foreach ($p as $value) {
                if ($value != '.' && $value != '..') {    //排除掉当./和../
                    if (is_dir($path . '/' . $value)) {
                        $this->clear_dir($path . '/' . $value);    //递归调用删除方法
                        rmdir($path . '/' . $value);    //删除当前文件夹
                    } else {
                        unlink($path . '/' . $value);    //删除当前文件
                    }
                }
            }
        }
    }

    /**
     * 在服务启动前
     * @param Context $context
     * @return mixed
     * @throws \GoSwoole\BaseServer\Server\Exception\ConfigException
     */
    public function beforeServerStart(Context $context)
    {
        //有文件操作必须关闭全局RuntimeCoroutine
        enableRuntimeCoroutine(false);
        $this->aopConfig->buildConfig();
        $eventDispatcher = $context->getDeepByClassName(EventDispatcher::class);
        if ($eventDispatcher instanceof EventDispatcher) {
            goWithContext(function () use ($eventDispatcher, $context) {
                $channel = $eventDispatcher->listen(PluginManagerEvent::PlugAfterServerStartEvent, null, true);
                $channel->pop();
                $cacheDir = $this->aopConfig->getCacheDir() ?? $context->getServer()->getServerConfig()->getBinDir() . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . "aop";
                if (file_exists($cacheDir)) {
                    $this->clear_dir($cacheDir);
                    rmdir($cacheDir);
                }
                mkdir($cacheDir, 0777, true);
                $this->applicationAspectKernel = ApplicationAspectKernel::getInstance();
                $this->applicationAspectKernel->setConfig($this->aopConfig);
                //初始化
                $this->applicationAspectKernel->init([
                    'debug' => $this->aopConfig->isDebug(), // use 'false' for production mode
                    'appDir' => $context->getServer()->getServerConfig()->getRootDir(), // Application root directory
                    'cacheDir' => $cacheDir, // Cache directory
                    // Include paths restricts the directories where aspects should be applied, or empty for all source files
                    'includePaths' => $this->aopConfig->getIncludePaths()
                ]);
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