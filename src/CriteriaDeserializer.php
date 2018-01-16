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
     * @param  string $source
     * @return Criteria
     *
     * @throws InvalidQueryException
     */
    public function deserialize(string $source): Criteria;

    /**
     * Deserialize criteria and append to an existing one
     *
     * @param string   $source
     * @param Criteria $criteria
     *
     * @throws InvalidQueryException
     */
    public function append(string $source, Criteria $criteria): void;
}