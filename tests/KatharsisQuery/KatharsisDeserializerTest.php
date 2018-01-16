<?php

namespace Mikemirten\Component\DoctrineCriteriaSerializer\KatharsisQuery;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use PHPUnit\Framework\TestCase;

class KatharsisDeserializerTest extends TestCase
{
    public function testPaginationOffset()
    {
        $deserializer = new KatharsisDeserializer();

        $criteria = $deserializer->deserialize('page[offset]=100');
        $this->assertSame(100, $criteria->getFirstResult());
    }

    public function testPaginationLimit()
    {
        $deserializer = new KatharsisDeserializer();

        $criteria = $deserializer->deserialize('page[limit]=10');
        $this->assertSame(10, $criteria->getMaxResults());
    }

    /**
     * @expectedException \Mikemirten\Component\DoctrineCriteriaSerializer\Exception\InvalidQueryException
     */
    public function testInvalidPaginationDefinition()
    {
        $deserializer = new KatharsisDeserializer();
        $deserializer->deserialize('page=10');
    }

    /**
     * @expectedException \Mikemirten\Component\DoctrineCriteriaSerializer\Exception\InvalidQueryException
     */
    public function testInvalidPaginationMember()
    {
        $deserializer = new KatharsisDeserializer();
        $deserializer->deserialize('page[max]=10');
    }

    public function testSortingSingle()
    {
        $deserializer = new KatharsisDeserializer();

        $criteria = $deserializer->deserialize('sort=firstName');
        $this->assertSame(['firstName' => Criteria::ASC], $criteria->getOrderings());
    }

    public function testSortingSingleDescending()
    {
        $deserializer = new KatharsisDeserializer();

        $criteria = $deserializer->deserialize('sort=-firstName');
        $this->assertSame(['firstName' => Criteria::DESC], $criteria->getOrderings());
    }

    public function testSortingMultiple()
    {
        $deserializer = new KatharsisDeserializer();

        $criteria  = $deserializer->deserialize('sort=firstName,-age');
        $orderings = $criteria->getOrderings();

        $this->assertSame(['firstName', 'age'], array_keys($orderings));
        $this->assertSame(Criteria::ASC, $orderings['firstName']);
        $this->assertSame(Criteria::DESC, $orderings['age']);
    }

    /**
     * @expectedException \Mikemirten\Component\DoctrineCriteriaSerializer\Exception\InvalidQueryException
     */
    public function testInvalidSortingDefinition()
    {
        $deserializer = new KatharsisDeserializer();
        $deserializer->deserialize('sort[age]=1');
    }

    public function testSimpleFiltering()
    {
        $deserializer = new KatharsisDeserializer();

        $criteria   = $deserializer->deserialize('filter[firstName]=John');
        $expression = $criteria->getWhereExpression();

        $this->assertInstanceOf(Comparison::class, $expression);
        $this->assertSame('firstName', $expression->getField());
        $this->assertSame(Comparison::EQ, $expression->getOperator());
        $this->assertSame('John', $expression->getValue()->getValue());
    }

    public function testMultipleFiltering()
    {
        $deserializer = new KatharsisDeserializer();

        $criteria   = $deserializer->deserialize('filter[firstName]=John&filter[lastName]=Doe');
        $expression = $criteria->getWhereExpression();

        $this->assertInstanceOf(CompositeExpression::class, $expression);
        $this->assertSame(CompositeExpression::TYPE_AND, $expression->getType());
        $this->assertCount(2, $expression->getExpressionList());

        $comparison1 = $expression->getExpressionList()[0];
        $comparison2 = $expression->getExpressionList()[1];

        $this->assertInstanceOf(Comparison::class, $comparison1);
        $this->assertSame('firstName', $comparison1->getField());
        $this->assertSame(Comparison::EQ, $comparison1->getOperator());
        $this->assertSame('John', $comparison1->getValue()->getValue());

        $this->assertInstanceOf(Comparison::class, $comparison2);
        $this->assertSame('lastName', $comparison2->getField());
        $this->assertSame(Comparison::EQ, $comparison2->getOperator());
        $this->assertSame('Doe', $comparison2->getValue()->getValue());
    }

    public function testOperatorFiltering()
    {
        $deserializer = new KatharsisDeserializer();

        $criteria   = $deserializer->deserialize('filter[firstName][EQ]=John');
        $expression = $criteria->getWhereExpression();

        $this->assertInstanceOf(Comparison::class, $expression);
        $this->assertSame('firstName', $expression->getField());
        $this->assertSame(Comparison::EQ, $expression->getOperator());
        $this->assertSame('John', $expression->getValue()->getValue());
    }

    /**
     * @expectedException \Mikemirten\Component\DoctrineCriteriaSerializer\Exception\InvalidQueryException
     */
    public function testInvalidFilteringDefinition()
    {
        $deserializer = new KatharsisDeserializer();
        $deserializer->deserialize('filter=John');
    }

    /**
     * @expectedException \Mikemirten\Component\DoctrineCriteriaSerializer\Exception\InvalidQueryException
     */
    public function testFilteringUnsupportedOperator()
    {
        $deserializer = new KatharsisDeserializer();
        $deserializer->deserialize('filter[firstName][ABC]=John');
    }

    /**
     * @depends testSimpleFiltering
     */
    public function testFilteringValueProcessingCallback()
    {
        $deserializer = new KatharsisDeserializer();
        $deserializer->setFilterCallback('status', function(string $status) {
            $this->assertSame('open', $status);
            return 1;
        });

        $criteria   = $deserializer->deserialize('filter[status]=open');
        $expression = $criteria->getWhereExpression();

        $this->assertSame(1, $expression->getValue()->getValue());
    }
}