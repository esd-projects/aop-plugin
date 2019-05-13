<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/23
 * Time: 18:50
 */

namespace ESD\Plugins\Aop\ExampleClass;


use Go\Aop\Aspect;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\After;
use Go\Lang\Annotation\Around;
use Go\Lang\Annotation\Before;
use Go\Lang\Annotation\Pointcut;
use ESD\BaseServer\Server\Beans\Response;
use ESD\BaseServer\Server\Server;
use Monolog\Logger;

class MonitorAspect implements Aspect
{
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
     * @Before("$this->processStart")
     */
    protected function beforeProcessStart(MethodInvocation $invocation)
    {
        $log = Server::$instance->getContext()->getByClassName(Logger::class);
        $log->info("before");
    }

    /**
     * after onProcessStart
     *
     * @param MethodInvocation $invocation Invocation
     * @After("$this->processStart")
     */
    protected function afterProcessStart(MethodInvocation $invocation)
    {
        $log = Server::$instance->getContext()->getByClassName(Logger::class);
        $log->info("after");
    }


    /**
     * around onHttpRequest
     *
     * @param MethodInvocation $invocation Invocation
     * @Around("within(ESD\BaseServer\Server\IServerPort+) && execution(public **->onHttpRequest(*))")
     */
    protected function aroundRequest(MethodInvocation $invocation)
    {
        $log = Server::$instance->getContext()->getByClassName(Logger::class);
        $log->info("aroundRequest");
        list($request, $response) = $invocation->getArguments();
        if ($response instanceof Response) {
            $response->end("HelloAOP");
        }
        return;
    }

}