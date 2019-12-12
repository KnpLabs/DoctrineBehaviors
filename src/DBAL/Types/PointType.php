<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Types\Type;
use Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point;

/**
 * Mapping type for spatial POINT objects
 */
final class PointType extends Type
{
    /**
     * Gets the name of this type.
     */
    public function getName(): string
    {
        return 'point';
    }

    /**
     * Returns the SQL declaration snippet for a field of this type.
     *
     * @param array            $fieldDeclaration The field declaration.
     * @param AbstractPlatform $platform         The currently used database platform.
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return 'POINT';
    }

    /**
     * Converts SQL value to the PHP representation.
     *
     * @param string $value value in DB format
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Point
    {
        if ($value === '') {
            return null;
        }

        if ($platform instanceof MySqlPlatform) {
            $data = sscanf($value, 'POINT(%f %f)');
        } else {
            $data = sscanf($value, '(%f,%f)');
        }

        return new Point($data[0], $data[1]);
    }

    /**
     * Converts PHP representation to the SQL value.
     *
     * @param Point|mixed $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (! $value) {
            return null;
        }

        $format = $platform instanceof MySqlPlatform ? 'POINT(%F %F)' : '(%F, %F)';

        return sprintf($format, $value->getLatitude(), $value->getLongitude());
    }

    /**
     * Does working with this column require SQL conversion functions?
     *
     * This is a metadata function that is required for example in the ORM.
     * Usage of {@link convertToDatabaseValueSQL} and
     * {@link convertToPHPValueSQL} works for any type and mostly
     * does nothing. This method can additionally be used for optimization purposes.
     */
    public function canRequireSQLConversion(): bool
    {
        return true;
    }

    /**
     * Modifies the SQL expression (identifier, parameter) to convert to a database value.
     * @param string $sqlExpr
     */
    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform): string
    {
        if ($platform instanceof MySqlPlatform) {
            return sprintf('PointFromText(%s)', $sqlExpr);
        }
        return parent::convertToDatabaseValueSQL($sqlExpr, $platform);
    }

    /**
     * @param string $sqlExpr
     * @param AbstractPlatform $platform
     */
    public function convertToPHPValueSQL($sqlExpr, $platform): string
    {
        if ($platform instanceof MySqlPlatform) {
            return sprintf('AsText(%s)', $sqlExpr);
        }
        return parent::convertToPHPValueSQL($sqlExpr, $platform);
    }
}
