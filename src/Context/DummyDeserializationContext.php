<?php
declare(strict_types = 1);

namespace Mikemirten\Component\DoctrineCriteriaSerializer\Context;

use Mikemirten\Component\DoctrineCriteriaSerializer\DeserializationContext;

/**
 * Dummy deserialization context
 * Doing nothing, just passes values through without any changes
 *
 * @package Mikemirten\Component\DoctrineCriteriaSerializer\Context
 */
class DummyDeserializationContext implements DeserializationContext
{
    /**
     * {@inheritdoc}
     */
    public function processFilterValue(string $name, $value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function processSortValue(string $name, $value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function processOffsetValue($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function processLimitValue($value)
    {
        return $value;
    }
}