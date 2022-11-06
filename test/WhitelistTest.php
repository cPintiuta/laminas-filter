<?php

declare(strict_types=1);

namespace LaminasTest\Filter;

use Laminas\Filter\AllowList;
use Laminas\Filter\FilterPluginManager;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase;

class WhitelistTest extends TestCase
{
    public function testWithPluginManager(): void
    {
        $pluginManager = new FilterPluginManager(new ServiceManager());
        $filter        = $pluginManager->get('whitelist');

        self::assertInstanceOf(AllowList::class, $filter);
    }
}
