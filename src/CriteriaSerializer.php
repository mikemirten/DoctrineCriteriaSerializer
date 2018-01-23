<?php
declare(strict_types = 1);

namespace Mikemirten\Component\DoctrineCriteriaSerializer;

use Doctrine\Common\Collections\Criteria;

/**
 * Interface of criteria serializer
 *
 * @package Mikemirten\Component\DoctrineCriteriaSerializer
 */
interface CriteriaSerializer
{
    /**
     * Serialize criteria
     *
     * @param  Criteria             $criteria
     * @param  SerializationContext $context
     * @return string
     */
    public function serialize(Criteria $criteria, SerializationContext $context = null): string;
}