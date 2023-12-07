<?php

namespace MattyRad\OpenApi\Tests\Unit\_Fixtures;

use OpenApi\Attributes as OpenApi;

class Second extends First
{
    /**
     * @return array<mixed, mixed>
     */
    #[OpenApi\Property(property: 'data')]
    public function getData(): array
    {
        return [];
    }
}
