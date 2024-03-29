<?php

namespace Pantono\Utilities\Tests;

use PHPUnit\Framework\TestCase;
use Pantono\Utilities\ApplicationHelper;

class ApplicationHelperTest extends TestCase
{
    public function testGetApplicationPathNotSet(): string
    {
        $this->expectException(\RuntimeException::class);
        ApplicationHelper::getApplicationRoot();
    }

    public function testGetApplicationPathConst()
    {
        define('APPLICATION_PATH', 'test');
        $this->assertEquals('test', ApplicationHelper::getApplicationRoot());
    }
}
