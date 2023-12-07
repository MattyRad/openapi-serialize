<?php

namespace MattyRad\OpenApi\Tests\Unit\_Fixtures;

class First implements Greeter
{
    public function getGreeting(): string
    {
        return 'hello world';
    }
}
