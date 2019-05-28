<?php declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/4/23
 * Time: 18:50
 */

namespace ESD\Plugins\Aop\ExampleClass;

use ESD\Core\Server\Beans\Response;
use ESD\Plugins\Aop\OrderAspect;
use ESD\Server\Co\Server;
use Go\Aop\Intercept\MethodInvocation;
use Go\Lang\Annotation\After;
use Go\Lang\Annotation\Around;
use Go\Lang\Annotation\Before;
use Go\Lang\Annotation\Pointcut;
use Monolog\Logger;

class MonitorAspect extends OrderAspect
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
     * @Around("within(ESD\Core\Server\Port\IServerPort+) && execution(public **->onHttpRequest(*))")
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

    /**
     * @return string
     */
    public function getName(): string
    {
        return "MonitorAspect";
    }
}