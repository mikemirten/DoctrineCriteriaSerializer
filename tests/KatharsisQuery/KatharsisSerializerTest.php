<?php

namespace Mikemirten\Component\DoctrineCriteriaSerializer\KatharsisQuery;

use Doctrine\Common\Collections\Criteria;
use PHPUnit\Framework\TestCase;

class KatharsisSerializerTest extends TestCase
{
    public function testSimpleEq()
    {
        $serializer = new KatharsisQuerySerializer();

        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('age', 30));

        $this->assertSame(
            'filter[age]=30',
            $serializer->serialize($criteria)
        );
    }

    public function testCompositeEq()
    {
        $serializer = new KatharsisQuerySerializer();

        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->eq('age', 30))
            ->andWhere(Criteria::expr()->eq('sex', 'm'));

        $this->assertSame(
            'filter[age]=30&filter[sex]=m',
            $serializer->serialize($criteria)
        );
    }

    public function testSimpleOperator()
    {
        $serializer = new KatharsisQuerySerializer();

        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->neq('sex', 'm'));

        $this->assertSame(
            'filter[sex][NEQ]=m',
            $serializer->serialize($criteria)
        );
    }

    public function testCompositeOperator()
    {
        $serializer = new KatharsisQuerySerializer();

        $criteria = Criteria::create()
            ->andWhere(Criteria::expr()->gt('age', 20))
            ->andWhere(Criteria::expr()->lt('age', 60));

        $this->assertSame(
            'filter[age][GT]=20&filter[age][LT]=60',
            $serializer->serialize($criteria)
        );
    }

    public function testOrOperator()
    {
        $serializer = new KatharsisQuerySerializer();

        $criteria = Criteria::create()
            ->where(Criteria::expr()->orX(
                Criteria::expr()->eq('status', 'open'),
                Criteria::expr()->eq('status', 'assigned')
            ));

        $this->assertSame(
            'filter[OR][status][0]=open&filter[OR][status][1]=assigned',
            $serializer->serialize($criteria)
        );
    }
}