<?php
declare(strict_types = 1);

namespace Mikemirten\Component\Doctrine\Common\Collections\CriteriaSerializer;

use Doctrine\Common\Collections\Criteria;

/**
 * Interface of criteria serializer
 *
 * @package Mikemirten\Component\Doctrine\Common\Collections\CriteriaSerializer
 */
interface CriteriaDeserializer
{
    /**
     * Deserialize criteria
     *
     * @param  string $source
     * @return Criteria
     */
    public function deserialize(string $source): Criteria;

    /**
     * Deserialize criteria and append to an existing one
     *
     * @param string   $source
     * @param Criteria $criteria
     */
    public function append(string $source, Criteria $criteria): void;
}