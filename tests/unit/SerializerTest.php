<?php

namespace MattyRad\OpenApi\Tests\Unit;

use PHPUnit\Framework\TestCase;
use MattyRad\OpenApi\Serializer;
use Throwable;
use OpenApi\Attributes as OpenApi;
use PHPUnit\Framework\Attributes as Unit;

#[Unit\CoversFunction('serialize')]
class SerializerTest extends TestCase
{
    #[Unit\Test]
    public function basic_serialization(): void
    {
        $sample = new class() {
            #[OpenApi\Property(property: 'greeting')]
            public function getGreeting(): string
            {
                return 'hello world';
            }
        };

        $serialized = Serializer::serialize($sample);

        $this->assertEquals(['greeting' => 'hello world'], $serialized);
    }

    #[Unit\Test]
    public function snake_case_names_are_serialized(): void
    {
        $sample = new class() {
            #[OpenApi\Property(property: 'another_greeting')]
            public function getAnotherGreeting(): string
            {
                return 'hola mundo';
            }
        };

        $serialized = Serializer::serialize($sample);

        $this->assertEquals(['another_greeting' => 'hola mundo'], $serialized);
    }

    #[Unit\Test]
    public function public_properties_are_serialized(): void
    {
        $sample = new class() {
            public function __construct(
                #[OpenApi\Property]
                public readonly string $greeting = 'hello world',
                #[OpenApi\Property(
                    property: 'another_greeting',
                )]
                public readonly string $_greeting = 'hello world',
                public readonly bool $not_serialized = true,
                #[OpenApi\Property] /** @phpstan-ignore-line */
                private bool $also_not_serialized = true,
            ) {
            }
        };

        $serialized = Serializer::serialize($sample);

        $this->assertEquals(['greeting' => 'hello world', 'another_greeting' => 'hello world'], $serialized);
    }

    #[Unit\Test]
    public function classes_serialize_inherited_methods(): void
    {
        $inherited = new _Fixtures\Inherited;

        $serialized = Serializer::serialize($inherited);

        $this->assertEquals(['greeting' => 'hello world'], $serialized);
    }



    #[Unit\Test]
    public function attributes_on_interface_get_serialized_for_interface_subscribers(): void
    {
        $sample = new _Fixtures\First;

        $serialized = Serializer::serialize($sample);

        $this->assertEquals(['greeting' => 'hello world'], $serialized);
    }

    #[Unit\Test]
    public function extended_classes_with_interfaces_can_still_add_to_serialization(): void
    {
        $sample = new _Fixtures\Second;

        $serialized = Serializer::serialize($sample);

        $this->assertEquals(['data' => [], 'greeting' => 'hello world'], $serialized);
    }

    #[Unit\Test]
    public function method_overrides_take_the_most_child_class_function(): void
    {
        $sample = new _Fixtures\Third;

        $serialized = Serializer::serialize($sample);

        $this->assertEquals(['data' => [], 'greeting' => 'buenos dias'], $serialized);
    }

    #[Unit\Test]
    public function nested_openapi_objects_are_serialized(): void
    {
        $sample = new _Fixtures\NestedSample;

        $serialized = Serializer::serialize($sample);

        $this->assertEquals(['data' => ['greeting' => 'hello world']], $serialized);
    }

    /**
     * @test
     * @dataProvider visibilityProvider
     *
     * @param array<string, ?string>|Throwable $expected
     */
    public function auth(bool $can_ack, bool $can_view, array|Throwable $expected): void
    {
        if ($expected instanceof Throwable) {
            $this->expectException($expected::class);
        }

        $sample = new _Fixtures\ProtectedSample;

        $auth = new class($can_ack, $can_view) {
            public function __construct(
                private readonly bool $can_ack,
                private readonly bool $can_view,
            ) {
            }

            public function hasPermission(): bool
            {
                if (! $this->can_ack) {
                    throw new \Exception('401');
                }

                return $this->can_view;
            }
        };

        $actual = Serializer::serialize($sample, compact('auth'));

        $this->assertEquals($expected, $actual);
    }

    public static function visibilityProvider(): array /** @phpstan-ignore-line */
    {
        return [
            [true, true,  ['secret' => 's3cr3t!']],
            [true, false, ['secret' => null]],
            [false, false, new \Exception('401')],
            [false, true,  new \Exception('401')],
        ];
    }

    #[Unit\Test]
    public function omitting_required_context_doesnt_provide_data(): void
    {
        $expected = [
            // intentionally blank
        ];

        $sample = new _Fixtures\ProtectedSample;

        $actual = Serializer::serialize($sample);

        $this->assertEquals($expected, $actual);
    }

}
