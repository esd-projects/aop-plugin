<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/23
 * Time: 18:50
 */

namespace ESD\Plugins\Aop\ExampleClass;

use ESD\Core\Plugins\Logger\GetLogger;
use ESD\Core\Server\Beans\Response;
use ESD\Plugins\Aop\OrderAspect;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\After;
use Go\Lang\Annotation\Around;
use Go\Lang\Annotation\Before;
use Go\Lang\Annotation\Pointcut;

class MonitorAspect extends OrderAspect
{
    use GetLogger;

    /**
     * Pointcut for onProcessStart
     *
     * @Pointcut("execution(public ESD\BaseServer\ExampleClass\Server\DefaultProcess->onProcessStart(*))")
     */
    protected function processStart()
    {
    }

    /**
     * before onProcessStart
     *
     * @param MethodInvocation $invocation Invocation
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @Before("$this->processStart")
     */
    protected function beforeProcessStart(MethodInvocation $invocation)
    {
        $this->info("before");
    }

    /**
     * after onProcessStart
     *
     * @param MethodInvocation $invocation Invocation
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @After("$this->processStart")
     */
    protected function afterProcessStart(MethodInvocation $invocation)
    {
        $this->info("after");
    }


    /**
     * around onHttpRequest
     *
     * @param MethodInvocation $invocation Invocation
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @Around("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onHttpRequest(*))")
     */
    protected function aroundRequest(MethodInvocation $invocation)
    {
        $this->info("aroundRequest");
        list($request, $response) = $invocation->getArguments();
        if ($response instanceof Response) {
            $response->end("HelloAOP");
        }
        return;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return "MonitorAspect";
    }
}