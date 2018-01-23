<?php
declare(strict_types = 1);

namespace Mikemirten\Component\DoctrineCriteriaSerializer;

/**
 * Interface of deserialization context
 *
 * @package Mikemirten\Component\DoctrineCriteriaSerializer
 */
interface DeserializationContext
{
    /**
     * Process value passed with filtering definition
     *
     * @param  string $name
     * @param  mixed  $value
     * @return mixed
     */
    public function processFilterValue(string $name, $value);

    /**
     * Process value passed with sorting definition
     *
     * @param  string $name
     * @param  mixed  $value
     * @return mixed
     */
    public function processSortValue(string $name, $value);

    /**
     * Process value passed with offset definition
     *
     * @param  mixed $value
     * @return mixed
     */
    public function processOffsetValue($value);

    /**
     * Process value passed with limit definition
     *
     * @param  mixed $value
     * @return mixed
     */
    public function processLimitValue($value);
}