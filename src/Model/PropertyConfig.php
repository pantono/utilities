<?php

namespace Pantono\Utilities\Model;

class PropertyConfig
{
    private ?string $type = null;
    private ?string $hydrator = null;
    private string $fieldName;
    private ?string $filter = null;
    private ?bool $lazy = null;
    private ?string $format = null;
    private ?bool $noSave = null;
    private ?bool $noFill = null;
    /**
     * @var \ReflectionAttribute<object>[]
     */
    private array $attributes = [];

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): void
    {
        $this->type = $type;
    }

    public function getHydrator(): ?string
    {
        return $this->hydrator;
    }

    public function setHydrator(?string $hydrator): void
    {
        $this->hydrator = $hydrator;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function setFieldName(string $fieldName): void
    {
        $this->fieldName = $fieldName;
    }

    public function getFilter(): ?string
    {
        return $this->filter;
    }

    public function setFilter(?string $filter): void
    {
        $this->filter = $filter;
    }

    public function getLazy(): ?bool
    {
        return $this->lazy;
    }

    public function setLazy(?bool $lazy): void
    {
        $this->lazy = $lazy;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(?string $format): void
    {
        $this->format = $format;
    }

    public function getNoSave(): ?bool
    {
        return $this->noSave;
    }

    public function setNoSave(?bool $noSave): void
    {
        $this->noSave = $noSave;
    }

    public function getNoFill(): ?bool
    {
        return $this->noFill;
    }

    public function setNoFill(?bool $noFill): void
    {
        $this->noFill = $noFill;
    }

    /**
     * @return \ReflectionAttribute<object>[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param \ReflectionAttribute<object>[] $attributes
     */
    public function setAttributes(array $attributes): void
    {
        $this->attributes = $attributes;
    }

    /**
     * @param \ReflectionAttribute<object> $attribute
     */
    public function addAttribute(\ReflectionAttribute $attribute): void
    {
        $this->attributes[] = $attribute;
    }

    public function hasAttribute(string $attributeClassName): bool
    {
        foreach ($this->attributes as $attribute) {
            if (get_class($attribute) === $attributeClassName) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return \ReflectionAttribute<object>|null
     */
    public function getAttribute(string $attributeClassName): ?\ReflectionAttribute
    {
        foreach ($this->attributes as $attribute) {
            if (get_class($attribute) === $attributeClassName) {
                return $attribute;
            }
        }
        return null;
    }
}
