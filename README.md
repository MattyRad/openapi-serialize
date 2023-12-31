# openapi-serialize


Serialize an object directly from [swagger-php](https://github.com/zircote/swagger-php) attributes.

```php
composer require mattyrad/openapi-serialize
```

```php
use OpenApi\Attributes as OpenApi;

$sample = new class() {
    public function __construct(
        #[OpenApi\Property]
        public readonly int $two_plus_two = 4,
    ) {}

    #[OpenApi\Property(property: 'greeting')]
    public function getGreeting(): string
    {
        return 'hello world';
    }
};

$serialized = MattyRad\OpenApi\Serializer::serialize($sample);

assert($serialized == ['two_plus_two' => 4, 'greeting' => 'hello world']);
```

This means that if you document all of your response data using swagger-php attributes, your API documentation will *necessarily* match the response format.

The need for tests to verify that a response matches OpenApi schema mostly becomes a formality- or altogether unnecessary.

## Examples

```php
use MattyRad\OpenApi\Serializer;
use OpenApi\Attributes as OpenApi;

abstract class HttpResource implements \JsonSerializable
{
    final public function jsonSerialize(): array|string
    {
        return Serializer::serialize($this);
    }
}

final class Greeting extends HttpResource
{
    public function __construct(
        #[OpenApi\Property]
        public readonly string $hello = 'world',
    ) {}
}

// return new JsonResponse(new Greeting)
```

Or a trait if you don't want to lock in to abstractions.

```php
use MattyRad\OpenApi;

trait SerializesFromOpenApi
{
    final public function jsonSerialize(): array|string
    {
        return Serializer::serialize($this);
    }
}

final class Greeting implements \JsonSerializable
{
    use SerializesFromOpenApi;

    public function __construct(
        #[OpenApi\Property]
        public readonly string $hello = 'world',
    ) {}
}

// return new JsonResponse(new Greeting)
```
