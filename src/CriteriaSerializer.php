<?php
declare(strict_types = 1);

namespace Mikemirten\Component\Doctrine\Common\Collections\CriteriaSerializer;

use Doctrine\Common\Collections\Criteria;

/**
 * Interface of criteria serializer
 *
 * @package Mikemirten\Component\Doctrine\Common\Collections\CriteriaSerializer
 */
interface CriteriaSerializer
{
    /**
     * Serialize criteria
     *
     * @param  Criteria $criteria
     * @return string
     */
    public function serialize(Criteria $criteria): string;
}