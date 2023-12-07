<?php

namespace MattyRad\OpenApi\Tests\Unit\_Fixtures;

class Third extends Second
{
    public function getGreeting(): string
    {
        return 'buenos dias';
    }
}
