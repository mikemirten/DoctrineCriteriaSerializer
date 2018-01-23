<?php

namespace Mikemirten\Component\DoctrineCriteriaSerializer\KatharsisQuery;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Mikemirten\Component\DoctrineCriteriaSerializer\DeserializationContext;
use PHPUnit\Framework\TestCase;

class KatharsisDeserializerTest extends TestCase
{
    public function testPaginationOffset()
    {
        $criteria = $this->createMock(Criteria::class);

        $criteria->expects($this->once())
            ->method('setFirstResult')
            ->with(100);

        $deserializer = new KatharsisQueryDeserializer();
        $deserializer->deserialize('page[offset]=100', $criteria);
    }

    public function testPaginationLimit()
    {
        $criteria = $this->createMock(Criteria::class);

        $criteria->expects($this->once())
            ->method('setMaxResults')
            ->with(10);

        $deserializer = new KatharsisQueryDeserializer();
        $deserializer->deserialize('page[limit]=10', $criteria);
    }

    /**
     * @expectedException \Mikemirten\Component\DoctrineCriteriaSerializer\Exception\InvalidQueryException
     */
    public function testInvalidPaginationDefinition()
    {
        $criteria = $this->createMock(Criteria::class);

        $deserializer = new KatharsisQueryDeserializer();
        $deserializer->deserialize('page=10', $criteria);
    }

    /**
     * @expectedException \Mikemirten\Component\DoctrineCriteriaSerializer\Exception\InvalidQueryException
     */
    public function testInvalidPaginationMember()
    {
        $criteria = $this->createMock(Criteria::class);

        $deserializer = new KatharsisQueryDeserializer();
        $deserializer->deserialize('page[max]=10', $criteria);
    }

    public function testSortingSingle()
    {
        $criteria = $this->createMock(Criteria::class);

        $criteria->expects($this->once())
            ->method('orderBy')
            ->with(['firstName' => Criteria::ASC]);

        $deserializer = new KatharsisQueryDeserializer();
        $deserializer->deserialize('sort=firstName', $criteria);
    }

    public function testSortingSingleDescending()
    {
        $criteria = $this->createMock(Criteria::class);

        $criteria->expects($this->once())
            ->method('orderBy')
            ->with(['firstName' => Criteria::DESC]);

        $deserializer = new KatharsisQueryDeserializer();
        $deserializer->deserialize('sort=-firstName', $criteria);
    }

    public function testSortingMultiple()
    {
        $criteria = $this->createMock(Criteria::class);

        $criteria->expects($this->once())
            ->method('orderBy')
            ->with([
                'firstName' => Criteria::ASC,
                'age'       => Criteria::DESC
            ]);

        $deserializer = new KatharsisQueryDeserializer();
        $deserializer->deserialize('sort=firstName,-age', $criteria);
    }

    /**
     * @expectedException \Mikemirten\Component\DoctrineCriteriaSerializer\Exception\InvalidQueryException
     */
    public function testInvalidSortingDefinition()
    {
        $criteria = $this->createMock(Criteria::class);

        $deserializer = new KatharsisQueryDeserializer();
        $deserializer->deserialize('sort[age]=1', $criteria);
    }

    public function testSimpleFiltering()
    {
        $criteria = $this->createMock(Criteria::class);
        $context  = $this->createMock(DeserializationContext::class);

        $context->method('processFilterValue')
            ->with('firstName', 'john')
            ->willReturn('John');

        $criteria->expects($this->once())
            ->method('andWhere')
            ->with($this->isInstanceOf(Comparison::class))
            ->willReturnCallback(function(Comparison $expression)
            {
                $this->assertSame('firstName', $expression->getField());
                $this->assertSame(Comparison::EQ, $expression->getOperator());
                $this->assertSame('John', $expression->getValue()->getValue());
            });

        $deserializer = new KatharsisQueryDeserializer();
        $deserializer->deserialize('filter[firstName]=john', $criteria, $context);
    }

    public function testMultipleFiltering()
    {
        $criteria = $this->createMock(Criteria::class);

        $criteria->expects($this->at(0))
            ->method('andWhere')
            ->with($this->isInstanceOf(Comparison::class))
            ->willReturnCallback(function(Comparison $expression)
            {
                $this->assertSame('firstName', $expression->getField());
                $this->assertSame(Comparison::EQ, $expression->getOperator());
                $this->assertSame('John', $expression->getValue()->getValue());
            });

        $criteria->expects($this->at(1))
            ->method('andWhere')
            ->with($this->isInstanceOf(Comparison::class))
            ->willReturnCallback(function(Comparison $expression)
            {
                $this->assertSame('lastName', $expression->getField());
                $this->assertSame(Comparison::EQ, $expression->getOperator());
                $this->assertSame('Doe', $expression->getValue()->getValue());
            });

        $deserializer = new KatharsisQueryDeserializer();
        $deserializer->deserialize('filter[firstName]=John&filter[lastName]=Doe', $criteria);
    }

    public function testOperatorFiltering()
    {
        $criteria = $this->createMock(Criteria::class);

        $criteria->expects($this->once())
            ->method('andWhere')
            ->with($this->isInstanceOf(Comparison::class))
            ->willReturnCallback(function(Comparison $expression)
            {
                $this->assertSame('firstName', $expression->getField());
                $this->assertSame(Comparison::EQ, $expression->getOperator());
                $this->assertSame('John', $expression->getValue()->getValue());
            });

        $deserializer = new KatharsisQueryDeserializer();
        $deserializer->deserialize('filter[firstName][EQ]=John', $criteria);
    }

    /**
     * @expectedException \Mikemirten\Component\DoctrineCriteriaSerializer\Exception\InvalidQueryException
     */
    public function testInvalidFilteringDefinition()
    {
        $criteria = $this->createMock(Criteria::class);

        $deserializer = new KatharsisQueryDeserializer();
        $deserializer->deserialize('filter=John', $criteria);
    }

    /**
     * @expectedException \Mikemirten\Component\DoctrineCriteriaSerializer\Exception\InvalidQueryException
     */
    public function testFilteringAbsentOperator()
    {
        $criteria = $this->createMock(Criteria::class);

        $deserializer = new KatharsisQueryDeserializer();
        $deserializer->deserialize('filter[firstName][]=John', $criteria);
    }

    /**
     * @expectedException \Mikemirten\Component\DoctrineCriteriaSerializer\Exception\InvalidQueryException
     */
    public function testFilteringUnsupportedOperator()
    {
        $criteria = $this->createMock(Criteria::class);

        $deserializer = new KatharsisQueryDeserializer();
        $deserializer->deserialize('filter[firstName][ABC]=John', $criteria);
    }

    /**
     * @expectedException \Mikemirten\Component\DoctrineCriteriaSerializer\Exception\InvalidQueryException
     */
    public function testInvalidFilteringInOperator()
    {
        $criteria = $this->createMock(Criteria::class);

        $deserializer = new KatharsisQueryDeserializer();
        $deserializer->deserialize('filter[status][in]=open', $criteria);
    }

    /**
     * @expectedException \Mikemirten\Component\DoctrineCriteriaSerializer\Exception\InvalidQueryException
     */
    public function testInvalidFilteringNotInOperator()
    {
        $criteria = $this->createMock(Criteria::class);

        $deserializer = new KatharsisQueryDeserializer();
        $deserializer->deserialize('filter[status][notIn]=open', $criteria);
    }
}