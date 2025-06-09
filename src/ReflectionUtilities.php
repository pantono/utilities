<?php

namespace Pantono\Utilities;

use Pantono\Contracts\Attributes\FieldName;
use Pantono\Contracts\Attributes\Filter;
use Pantono\Contracts\Attributes\Locator;
use Pantono\Contracts\Attributes\Lazy;
use ReflectionProperty;
use ReflectionNamedType;
use Pantono\Contracts\Attributes\NoSave;
use Pantono\Contracts\Attributes\NoFill;

class ReflectionUtilities
{
    /**
     * @return array{type: ?string, hydrator: ?string, field_name: string, filter: ?string, lazy: ?bool, format: ?string, no_save: ?bool, no_fill: ?bool}
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
            'no_save' => null,
            'no_fill' => null
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
                if ($instance->serviceName) {
                    $info['hydrator'] = $instance->serviceName . '::' . $instance->methodName;
                }
                if ($instance->className) {
                    $info['hydrator'] = $instance->className . '::' . $instance->methodName;
                }
            }
            if (get_class($instance) === Lazy::class) {
                $info['lazy'] = true;
            }
            if (get_class($instance) === NoSave::class) {
                $info['no_save'] = true;
            }
            if (get_class($instance) === NoFill::class) {
                $info['no_fill'] = true;
            }
        }

        return $info;
    }

    /**
     * @return \ReflectionAttribute<object>[]
     * @throws \ReflectionException
     */
    public static function getAttributes(string $className, string $propertyName): array
    {
        if (!class_exists($className)) {
            return [];
        }
        if (!method_exists($className, $propertyName)) {
            return [];
        }
        $property = new ReflectionProperty($className, $propertyName);
        return $property->getAttributes();
    }

    public static function hasAttributes(string $className, string $propertyName, string $attributeClass): bool
    {
        foreach (self::getAttributes($className, $propertyName) as $attribute) {
            if ($attribute->getName() === $attributeClass) {
                return true;
            }
        }
        return false;
    }
}
