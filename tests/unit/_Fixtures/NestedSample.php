<?php

namespace MattyRad\OpenApi\Tests\Unit\_Fixtures;

use OpenApi\Attributes as OpenApi;

class NestedSample
{
    #[OpenApi\Property(property: 'data')]
    public function getData(): SampleSchema
    {
        return new SampleSchema;
    }
}
