<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

abstract class UnitTestCase extends TestCase
{
    use ProphecyTrait;
}
