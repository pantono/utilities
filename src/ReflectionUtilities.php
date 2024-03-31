<?php

namespace Pantono\Utilities;

use Pantono\Contracts\Attributes\FieldName;
use Pantono\Contracts\Attributes\Filter;
use Pantono\Contracts\Attributes\Locator;
use Pantono\Contracts\Attributes\Lazy;
use ReflectionProperty;
use ReflectionNamedType;
use Pantono\Contracts\Attributes\NoSave;

class ReflectionUtilities
{
    /**
     * @return array{type: ?string, hydrator: ?string, field_name: string, filter: ?string, lazy: ?bool, format: ?string}
     */
    public static function parseAttributesIntoConfig(ReflectionProperty $property): array
    {
        $info = [
            'type' => null,
            'hydrator' => null,
            'field_name' => StringUtilities::snakeCase($property->getName()),
            'filter' => null,
            'lazy' => null,
            'format' => null,
            'no_save' => null
        ];
        $type = $property->getType();
        if ($type instanceof ReflectionNamedType) {
            $info['type'] = $type->getName();
        }
        foreach ($property->getAttributes() as $attribute) {
            $instance = $attribute->newInstance();
            if (get_class($instance) === FieldName::class) {
                $info['field_name'] = $instance->name;
            }
            if (get_class($instance) === Filter::class) {
                $info['filter'] = $instance->filter;
            }
            if (get_class($instance) === Locator::class) {
                $info['hydrator'] = $instance->serviceName . '::' . $instance->methodName;
            }
            if (get_class($instance) === Lazy::class) {
                $info['lazy'] = true;
            }
            if (get_class($instance) === NoSave::class) {
                $info['no_save'] = true;
            }
        }

        return $info;
    }
}
