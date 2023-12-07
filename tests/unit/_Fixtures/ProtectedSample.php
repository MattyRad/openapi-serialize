<?php

namespace MattyRad\OpenApi\Tests\Unit\_Fixtures;

use OpenApi\Attributes as OpenApi;

class ProtectedSample
{
    #[OpenApi\Property(property: 'secret')]
    public function getSecret(mixed $auth): ?string
    {
        if (! $auth->hasPermission($this)) {
            return null;
        }

        return 's3cr3t!';
    }
}
