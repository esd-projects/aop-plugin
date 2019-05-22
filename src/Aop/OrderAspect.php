<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/22
 * Time: 16:35
 */

namespace ESD\Plugins\Aop;


use ESD\BaseServer\Order\Order;
use Go\Aop\Aspect;

abstract class OrderAspect extends Order implements Aspect
{
}