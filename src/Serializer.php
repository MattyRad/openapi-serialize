<?php

namespace MattyRad\OpenApi;

use OpenApi\Attributes as OpenApi;
use OpenApi\Generator;

final class Serializer
{

    private const GETTER_PREFIX = 'get';

    /**
     * @param array<mixed, mixed> $resource
     * @param array<string, mixed> $context Sent to the getter function's arguments, allowing functionality similar to https://github.com/Crell/Serde#scopes
     *
     * @return array<mixed, mixed>
     */
    public static function serialize(object|array $resource, array $context = []): string|array
    {
        if (is_array($resource)) {
            $result = [];

            foreach ($resource as $k => $v) {
                $value = (is_object($v) || is_array($v)) ? self::serialize($v, $context) : $v;

                $result[$k] = $value;
            }

            return $result;
        }

        if ($resource instanceof \DateTimeInterface) {
            return $resource->format(\DateTimeInterface::RFC3339_EXTENDED);
        }

        $serialized = [];

        foreach (array_merge(self::getReflectionProperties($resource), self::getReflectionFunctions($resource)) as $reflection) {
            $key_value_pair = self::getKeyValuePair($resource, $reflection, $context);

            if ($key_value_pair) {
                [$key, $value] = $key_value_pair;

                $serialized[$key] = $value;
            }
        }

        return $serialized;
    }

    /**
     * @return list<\ReflectionMethod>
     */
    private static function getReflectionFunctions(object $resource): array
    {
        $reflection = new \ReflectionClass($resource);

        $getInterfaceNames = fn (array $carry, \ReflectionClass $rc) => array_merge($carry, $rc->getMethods());

        $interface_reflection_functions = array_reduce($reflection->getInterfaces(), $getInterfaceNames, []);

        $resource_reflection_functions = $reflection->getMethods(\ReflectionProperty::IS_PUBLIC);

        return array_merge($interface_reflection_functions, $resource_reflection_functions);
    }

    /**
     * @return list<\ReflectionProperty>
     */
    private static function getReflectionProperties(object $resource): array
    {
        $reflection = new \ReflectionClass($resource);

        $reflection_properties = $reflection->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_READONLY);

        return array_values(
            array_filter($reflection_properties, fn (
                \ReflectionProperty $reflection_property
            ) => !$reflection_property->isStatic())
        );
    }

    /**
     * @param object|array<mixed, mixed> $resource
     * @param array<string, mixed> $context
     *
     * @return ?array{string, mixed}
     */
    private static function getKeyValuePair(object|array $resource, \ReflectionMethod|\ReflectionProperty $reflection, array $context): ?array
    {
        $openapi_reflection_attributes = $reflection->getAttributes(OpenApi\Property::class);

        foreach ($openapi_reflection_attributes as $openapi_reflection_attribute) {
            $openapi_attribute = $openapi_reflection_attribute->newInstance();

            $key_value_pair = $reflection instanceof \ReflectionMethod ?
                self::attemptGetter($resource, $context, $openapi_attribute->property) :
                self::attemptProperty($resource, $reflection, $context, $openapi_attribute->property);

            if ($key_value_pair) {
                return $key_value_pair;
            }
        }

        return null;
    }

    /**
     * @param object|array<mixed, mixed> $resource
     * @param array<string, mixed>       $context
     *
     * @return ?array{string, mixed}
     */
    private static function attemptProperty(object|array $resource, \ReflectionProperty $reflection, array $context, string $key): ?array
    {
        if (!is_object($resource)) {
            return null;
        }

        $value = $reflection->getValue($resource);

        if ($key === Generator::UNDEFINED) {
            $key = $reflection->getName();
        }

        if (is_object($value) || is_array($value)) {
            $value = self::serialize($value, $context);
        }

        return [$key, $value];
    }

    /**
     * @param object|array<mixed, mixed> $resource
     * @param array<string, mixed> $context
     *
     * @return ?array{string, mixed}
     */
    private static function attemptGetter(object|array $resource, array $context, string $key): ?array
    {
        $found_value = $value = false;

        $normalized_key = ucfirst(self::studly($key));

        $function_name = self::GETTER_PREFIX . $normalized_key;

        $function_name = (string) preg_replace('/[^A-Za-z0-9]/', '', (string) $function_name);

        assert(is_object($resource));

        if (method_exists($resource, $function_name)) {
            $parameters = (new \ReflectionClass($resource))->getMethod($function_name)->getParameters();

            $param_names = array_map(fn ($reflection_parameter) => $reflection_parameter->getName(), $parameters);

            // TODO: Handle/ignore optional params
            $param_filtered_context = array_intersect_key($context, array_flip($param_names));

            if ($param_names == array_keys($param_filtered_context)) {
                $value = $resource->{$function_name}(...$param_filtered_context);

                $found_value = true;
            }
        }

        if ($found_value) {
            $value = (is_object($value) || is_array($value)) ? self::serialize($value, $context) : $value;

            return [$key, $value];
        }

        return null;
    }

    private static function studly(string $value): string
    {
        $words = explode(' ', str_replace(['-', '_'], ' ', $value));

        $studlyWords = array_map(fn ($word) => ucfirst($word), $words);

        return implode($studlyWords);
    }
}
