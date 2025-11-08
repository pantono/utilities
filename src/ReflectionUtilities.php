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
use Pantono\Utilities\Model\PropertyConfig;
use Pantono\Contracts\Attributes\DateFormat;

class ReflectionUtilities
{
    /**
     * @return array{type: ?string, hydrator: ?string, hydrator_class: ?string, hydrator_method: ?string, field_name: string, filter: ?string, lazy: ?bool, format: ?string, no_save: ?bool, no_fill: ?bool}
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
            'hydrator_class' => null,
            'hydrator_method' => null,
            'hydrator_service' => null,
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
                $info['hydrator_method'] = $instance->methodName;
                if ($instance->serviceName) {
                    $info['hydrator_service'] = $instance->serviceName;
                    $info['hydrator'] = $instance->serviceName . '::' . $instance->methodName;
                }
                if ($instance->className) {
                    $info['hydrator_class'] = $instance->className;
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
            if (get_class($instance) === DateFormat::class) {
                $info['format'] = $instance->format;
            }
        }

        return $info;
    }

    public static function parseAttributesIntoConfigModel(ReflectionProperty $property): PropertyConfig
    {
        $config = new PropertyConfig();
        $type = $property->getType();
        if ($type instanceof ReflectionNamedType) {
            $config->setType($type->getName());
        }
        foreach ($property->getAttributes() as $attribute) {
            $instance = $attribute->newInstance();
            if (get_class($instance) === FieldName::class) {
                $config->setFieldName($instance->name);
            }
            if (get_class($instance) === Filter::class) {
                $config->setFilter($instance->filter);
            }
            if (get_class($instance) === Locator::class) {
                if ($instance->serviceName) {
                    $config->setHydrator($instance->serviceName . '::' . $instance->methodName);
                }
                if ($instance->className) {
                    $config->setHydrator($instance->className . '::' . $instance->methodName);;
                }
            }
            if (get_class($instance) === Lazy::class) {
                $config->setLazy(true);
            }
            if (get_class($instance) === NoSave::class) {
                $config->setNoSave(true);
            }
            if (get_class($instance) === NoFill::class) {
                $config->setNoFill(true);
            }
            $config->addAttribute($attribute);
        }

        return $config;
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

    /**
     * @return array{namespace: ?string, uses: array<string, string>}
     */
    public static function parseUseStatements(string $filePath): array
    {
        $code = file_get_contents($filePath);
        if ($code === false) {
            throw new \RuntimeException("Unable to read $filePath");
        }

        $tokens = token_get_all($code);

        $namespace = null;
        $uses = [];

        $i = 0;
        $count = count($tokens);
        $collectingNamespace = false;
        $collectingUse = false;
        $current = '';

        while ($i < $count) {
            $t = $tokens[$i];

            // Normalize token to [id, text]
            if (is_array($t)) {
                [$id, $text] = $t;
            } else {
                $id = null;
                $text = $t;
            }

            // Namespace collection
            if ($id === T_NAMESPACE) {
                $collectingNamespace = true;
                $current = '';
                $i++;
                continue;
            }
            if ($collectingNamespace) {
                if ($text === ';' || $text === '{') {
                    $namespace = trim($current, " \t\n\r\\");
                    $collectingNamespace = false;
                    $current = '';
                } elseif ($id === T_STRING || $id === T_NAME_QUALIFIED || $id === T_NAME_FULLY_QUALIFIED) {
                    $current .= $text;
                } elseif ($text === '\\') {
                    $current .= '\\';
                }
                $i++;
                continue;
            }

            // Use statements (skip inside classes, traits, interfaces by simple brace-depth heuristic)
            if ($id === T_USE) {
                // Peek backward: if previous significant token is T_CLASS/T_INTERFACE/T_TRAIT, it's a trait use
                $prevSig = self::prevSignificant($tokens, $i);
                if ($prevSig && in_array($prevSig[0], [T_CLASS, T_TRAIT, T_INTERFACE], true)) {
                    $i++;
                    continue; // skip trait-use
                }

                $collectingUse = true;
                $current = '';
                $i++;
                $groupPrefix = '';
                $isGroup = false;
                // Collect until semicolon; handle group uses: use Foo\Bar\{Baz,Qux as Alias};
                while ($i < $count) {
                    $t2 = $tokens[$i];
                    if (is_array($t2)) {
                        [$id2, $text2] = $t2;
                    } else {
                        $id2 = null;
                        $text2 = $t2;
                    }

                    if ($text2 === ';') {
                        if ($current !== '') {
                            self::commitUse($uses, $current, $groupPrefix);
                        }
                        break;
                    }

                    if ($text2 === '{') {
                        $isGroup = true;
                        $groupPrefix = rtrim($current, "\\ \t\n\r");
                        $current = '';
                        $i++;
                        // Parse group body
                        $part = '';
                        while ($i < $count) {
                            $t3 = $tokens[$i];
                            if (is_array($t3)) {
                                [$id3, $text3] = $t3;
                            } else {
                                $id3 = null;
                                $text3 = $t3;
                            }
                            if ($text3 === '}') {
                                if (trim($part) !== '') {
                                    self::commitUse($uses, $part, $groupPrefix);
                                }
                                // Expect semicolon next; handled by the outer loop
                                break;
                            }
                            if ($text3 === ',') {
                                if (trim($part) !== '') {
                                    self::commitUse($uses, $part, $groupPrefix);
                                    $part = '';
                                }
                                $i++;
                                continue;
                            }
                            $part .= $text3;
                            $i++;
                        }
                        // outer loop continues to consume until ';'
                    } else {
                        $current .= $text2;
                    }

                    $i++;
                }

                $collectingUse = false;
                $current = '';
                $i++;
                continue;
            }

            $i++;
        }

        return [
            'namespace' => $namespace ?: null,
            'uses' => $uses,
        ];
    }

    /**
     * @param array<string, string> $uses
     */
    private static function commitUse(array &$uses, string $segment, string $groupPrefix = ''): void
    {
        // Normalize whitespace
        $segment = trim(preg_replace('/\s+/', ' ', $segment) ?? '');
        if ($segment === '') {
            return;
        }

        // Handle "as"
        $alias = null;
        if (stripos($segment, ' as ') !== false) {
            $parts = preg_split('/\s+as\s+/i', $segment, 2);
            if ($parts !== false) {
                $left = $parts[0];
                $aliasPart = $parts[1] ?? '';
                $alias = trim($aliasPart);
                $segment = trim($left);
            }
        }

        $fqcn = ltrim(($groupPrefix ? rtrim($groupPrefix, '\\') . '\\' : '') . $segment, '\\');
        $short = $alias ?: self::shortName($fqcn);
        $uses[$short] = '\\' . $fqcn;
    }

    private static function shortName(string $fqcn): string
    {
        $fqcn = ltrim($fqcn, '\\');
        $pos = strrpos($fqcn, '\\');
        return $pos === false ? $fqcn : substr($fqcn, $pos + 1);
    }

    /**
     * @param list<array{0:int,1:string,2?:int}|string> $tokens
     * @return array{0:int|null,1:string}|null
     */
    private static function prevSignificant(array $tokens, int $idx): ?array
    {
        for ($i = $idx - 1; $i >= 0; $i--) {
            $t = $tokens[$i];
            if (is_array($t)) {
                if (in_array($t[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                    continue;
                }
                return $t;
            }
            if (trim($t) === '') {
                continue;
            }
            return [null, $t];
        }
        return null;
    }
}
