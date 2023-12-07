<?php

namespace MattyRad\OpenApi\Tests\Unit\_Fixtures;

use OpenApi\Attributes as OpenApi;

interface Greeter
{
    #[OpenApi\Property(property: 'greeting')]
    public function getGreeting(): string;
}
