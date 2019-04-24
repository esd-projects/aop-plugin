<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/23
 * Time: 18:50
 */

namespace GoSwoole\Plugins\Aop\ExampleClass;


use Go\Aop\Aspect;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\Before;
use Go\Lang\Annotation\Pointcut;

class MonitorAspect implements Aspect
{
    /**
     * Pointcut for onProcessStart
     *
     * @Pointcut("execution(public GoSwoole\BaseServer\ExampleClass\Server\DefaultProcess->onProcessStart(*))")
     */
    protected function processStart() {}

    /**
     * before onProcessStart
     *
     * @param MethodInvocation $invocation Invocation
     * @Before("$this->processStart")
     */
    protected function beforeProcessStart(MethodInvocation $invocation)
    {
        echo("11111111\n");
    }
}