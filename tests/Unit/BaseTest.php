<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class BaseTest extends TestCase
{
    public function if_this_test_fails_everything_is_a_lie(): void
    {
        $this->assertTrue(true);
    }
}
