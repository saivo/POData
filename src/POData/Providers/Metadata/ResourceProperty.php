<?php

declare(strict_types=1);

namespace POData\Providers\Metadata;

use InvalidArgumentException;
use POData\Common\Messages;
use POData\Providers\Metadata\Type\IType;
use ReflectionClass;
use ReflectionException;

/**
 * Class ResourceProperty.
 * @package POData\Providers\Metadata
 */
class ResourceProperty
{
    /**
     * Property name.
     *
     * @var string
     */
    private $name;

    /**
     * Property MIME type.
     *
     * @var string
     */
    private $mimeType;

    /**
     * Property Kind, the possible values are:
     *  ResourceReference
     *  ResourceSetReference
     *  ComplexType
     *  ComplexType + Bag
     *  PrimitiveType
     *  PrimitiveType + Bag
     *  PrimitiveType + Key
     *  PrimitiveType + ETag.
     *
     * @var ResourcePropertyKind
     */
    private $kind;

    /**
     * ResourceType describes this property.
     *
     * @var ResourceType
     */
    private $propertyResourceType;

    /**
     * @param string               $name                 Name of the property
     * @param string|null          $mimeType             Mime type of the property
     * @param ResourcePropertyKind $kind                 The kind of property
     * @param ResourceType         $propertyResourceType ResourceType of the property
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        string $name,
        ?string $mimeType,
        ResourcePropertyKind $kind,
        ResourceType $propertyResourceType
    ) {
        if (!$this->isValidPropertyName($name)) {
            throw new InvalidArgumentException(
                'Property name violates OData specification.'
            );
        }

        if (!ResourceProperty::isValidResourcePropertyKind($kind)) {
            throw new InvalidArgumentException(
                Messages::resourcePropertyInvalidKindParameter('$kind')
            );
        }

        if (!ResourceProperty::isResourceKindValidForPropertyKind($kind, $propertyResourceType->getResourceTypeKind())) {
            throw new InvalidArgumentException(
                Messages::resourcePropertyPropertyKindAndResourceTypeKindMismatch(
                    '$kind',
                    '$propertyResourceType'
                )
            );
        }

        $this->name                 = $name;
        $this->mimeType             = $mimeType;
        $this->kind                 = $kind;
        $this->propertyResourceType = $propertyResourceType;
    }

    /**
     * Checks whether supplied name meets OData specification.
     *
     * @param string $name Field name to be validated
     *
     * @return bool
     */
    private function isValidPropertyName(string $name): bool
    {
        if (empty($name)) {
            return false;
        }
        if ('_' == substr($name, 0, 1)) {
            return false;
        }

        return true;
    }

    /**
     * Checks whether resource property kind is valid or not.
     *
     * @param ResourcePropertyKind $kind The kind to validate
     *
     * @return bool
     */
    public static function isValidResourcePropertyKind(ResourcePropertyKind $kind): bool
    {
        return !($kind != ResourcePropertyKind::RESOURCE_REFERENCE() &&
                 $kind != ResourcePropertyKind::RESOURCESET_REFERENCE() &&
                 $kind != ResourcePropertyKind::COMPLEX_TYPE() &&
                 ($kind != ResourcePropertyKind::COMPLEX_TYPE()->setBAG(true)) &&
                 $kind != ResourcePropertyKind::PRIMITIVE() &&
                 ($kind != ResourcePropertyKind::PRIMITIVE()->setBAG(true)) &&
                 ($kind != ResourcePropertyKind::PRIMITIVE()->setKEY(true)) &&
                 ($kind != ResourcePropertyKind::PRIMITIVE()->setETAG(true)));
    }

    /**
     * Check the specified resource kind is valid resource kind for property kind.
     *
     * @param ResourcePropertyKind $pKind The kind of resource property
     * @param ResourceTypeKind     $rKind The kind of resource type
     *
     * @return bool True if resource type kind and property kind matches
     *              otherwise false
     */
    public static function isResourceKindValidForPropertyKind(
        ResourcePropertyKind $pKind,
        ResourceTypeKind $rKind
    ): bool {
        if (self::sIsKindOf($pKind, ResourcePropertyKind::PRIMITIVE())
            && $rKind != ResourceTypeKind::PRIMITIVE()
        ) {
            return false;
        }

        if (self::sIsKindOf($pKind, ResourcePropertyKind::COMPLEX_TYPE())
            && $rKind != ResourceTypeKind::COMPLEX()
        ) {
            return false;
        }

        if ((self::sIsKindOf($pKind, ResourcePropertyKind::RESOURCE_REFERENCE())
                || self::sIsKindOf($pKind, ResourcePropertyKind::RESOURCESET_REFERENCE()))
            && $rKind != ResourceTypeKind::ENTITY()
        ) {
            return false;
        }

        return true;
    }

    /**
     * Check one kind is of another kind.
     *
     * @param ResourcePropertyKind $kind1 First kind
     * @param ResourcePropertyKind $kind2 second kind
     *
     * @return bool
     */
    public static function sIsKindOf(ResourcePropertyKind $kind1, ResourcePropertyKind $kind2): bool
    {
        return ($kind1->getValue() & $kind2->getValue()) == $kind2->getValue();
    }

    /**
     * Check whether current property is of kind specified by the parameter.
     *
     * @param ResourcePropertyKind $kind kind to check
     *
     * @return bool
     */
    public function isKindOf(ResourcePropertyKind $kind): bool
    {
        return ($this->getKind()->getValue()
                & $kind->getValue()) == $kind->getValue();
    }

    /**
     * Get property kind.
     *
     * @return ResourcePropertyKind
     */
    public function getKind(): ResourcePropertyKind
    {
        return $this->kind;
    }

    /**
     * Get the property name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get property MIME type.
     *
     * @return string
     */
    public function getMIMEType(): string
    {
        return $this->mimeType;
    }

    /**
     * Get the resource type for this property.
     *
     * @return ResourceType
     */
    public function getResourceType(): ResourceType
    {
        return $this->propertyResourceType;
    }

    /**
     * Get the kind of resource type.
     *
     * @return ResourceTypeKind
     */
    public function getTypeKind(): ResourceTypeKind
    {
        return $this->propertyResourceType->getResourceTypeKind();
    }

    /**
     * Get the instance type. If the property is of kind 'Complex',
     * 'ResourceReference' or 'ResourceSetReference' then this function returns
     * reference to ReflectionClass instance for the type. If the property of
     * kind 'Primitive' then this function returns ITYpe instance for the type.
     *
     * @throws ReflectionException
     * @return ReflectionClass|IType
     */
    public function getInstanceType()
    {
        $type = $this->propertyResourceType->getInstanceType();
        assert($type instanceof IType == static::sIsKindOf($this->getKind(), ResourcePropertyKind::PRIMITIVE()));
        return $type;
    }
}
