# openapi-serialize

Serialize an object directly from [swagger-php](https://github.com/zircote/swagger-php) attributes.

```php
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

$serialized = Serializer::serialize($sample);

assert($serialized == ['two_plus_two' => 4, 'greeting' => 'hello world']);
```

This means that if your application enforces serialization prior to returning a response, your API documentation will *necessarily* be in sync with the response format.

```php
use MattyRad\OpenApi;

abstract class HttpResource implements \JsonSerializable
{
    final public function jsonSerialize(): array|string
    {
        return OpenApi\Serializer::serialize($this);
    }
}
```

Or a trait if you don't want to lock in to abstractions.

```php
use MattyRad\OpenApi;

trait SerializesFromOpenApi
{
    final public function jsonSerialize(): array|string
    {
        return OpenApi\Serializer::serialize($this);
    }
}
```