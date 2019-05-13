<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/23
 * Time: 18:26
 */

namespace ESD\Plugins\Aop;


use Go\Core\AspectContainer;
use Go\Core\AspectKernel;

class ApplicationAspectKernel extends AspectKernel
{
    /**
     * @var AopConfig
     */
    private $aopConfig;


    public function setConfig(AopConfig $aopConfig)
    {
        $this->aopConfig = $aopConfig;
    }


    public function init(array $options = [])
    {
        parent::init($options);
        $this->wasInitialized = false;
    }

    public function initAspect()
    {
        foreach ($this->aopConfig->getAspects() as $aspect) {
            $this->container->registerAspect($aspect);
        }
        $this->wasInitialized = true;
    }
    /**
     * Configure an AspectContainer with advisors, aspects and pointcuts
     *
     * @param AspectContainer $container
     *
     * @return void
     */
    protected function configureAop(AspectContainer $container)
    {
       return;
    }

}