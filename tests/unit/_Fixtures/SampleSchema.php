<?php

namespace MattyRad\OpenApi\Tests\Unit\_Fixtures;

use OpenApi\Attributes as OpenApi;

#[OpenApi\Schema(schema: 'SampleSchema')]
class SampleSchema
{
    #[OpenApi\Property(property: 'greeting')]
    public function getGreeting(): string
    {
        return 'hello world';
    }
}