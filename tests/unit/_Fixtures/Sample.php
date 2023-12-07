<?php

namespace MattyRad\OpenApi\Tests\Unit\_Fixtures;

use OpenApi\Attributes as OpenApi;

class Sample
{
    #[OpenApi\Property(property: 'greeting')]
    public function getGreeting(): string
    {
        return 'hello world';
    }
}
