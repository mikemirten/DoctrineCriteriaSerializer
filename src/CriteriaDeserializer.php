<?php
declare(strict_types = 1);

namespace Mikemirten\Component\DoctrineCriteriaSerializer;

use Doctrine\Common\Collections\Criteria;
use Mikemirten\Component\DoctrineCriteriaSerializer\Exception\InvalidQueryException;

/**
 * Interface of criteria serializer
 *
 * @package Mikemirten\Component\DoctrineCriteriaSerializer
 */
interface CriteriaDeserializer
{
    /**
     * Deserialize criteria
     *
     * @param string                 $source
     * @param Criteria               $criteria
     * @param DeserializationContext $context
     *
     * @throws InvalidQueryException
     */
    public function deserialize(string $source, Criteria $criteria, DeserializationContext $context = null): void;
}