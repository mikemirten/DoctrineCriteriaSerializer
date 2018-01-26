<?php
declare(strict_types = 1);

namespace Mikemirten\Component\DoctrineCriteriaSerializer\KatharsisQuery;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Doctrine\Common\Collections\Expr\Expression;
use Mikemirten\Component\DoctrineCriteriaSerializer\CriteriaSerializer;
use Mikemirten\Component\DoctrineCriteriaSerializer\SerializationContext;

/**
 * Serializer into query of the Katharsis JSON API library
 *
 * @see http://katharsis.io/
 * @see http://katharsis-jsonapi.readthedocs.io/en/latest/user-docs.html#query-parameters
 *
 * @package Mikemirten\Component\DoctrineCriteriaSerializer\KatharsisRequest
 */
class KatharsisQuerySerializer implements CriteriaSerializer
{
    /**
     * Map of comparison operators to katharsis
     */
    const OPERATOR_MAP = [
        Comparison::EQ  => 'EQ',
        Comparison::NEQ => 'NEQ',
        Comparison::GT  => 'GT',
        Comparison::GTE => 'GTE',
        Comparison::LT  => 'LT',
        Comparison::LTE => 'LTE',
        Comparison::IN  => 'IN',
        Comparison::NIN => 'NIN'
    ];

    /**
     * {@inheritdoc}
     */
    public function serialize(Criteria $criteria, SerializationContext $context = null): string
    {
        $definition = array_merge(
            $this->assembleFiltering($criteria),
            $this->assemblePagination($criteria),
            $this->assembleSorting($criteria)
        );

        if (empty($definition)) {
            return '';
        }

        return http_build_query($definition);
    }

    /**
     * Assemble filtering definition
     *
     * @param  Criteria $criteria
     * @return array
     */
    protected function assembleFiltering(Criteria $criteria): array
    {
        $expression = $criteria->getWhereExpression();

        if ($expression === null) {
            return [];
        }

        $definition = $this->assembleExpression($expression);

        return ['filter' => $definition];
    }

    /**
     * Assemble expression
     *
     * @param  Expression $expression
     * @param  int        $level
     * @return array
     */
    protected function assembleExpression(Expression $expression, int $level = 0): array
    {
        if ($expression instanceof Comparison) {
            return $this->assembleComparison($expression);
        }

        if ($expression instanceof CompositeExpression) {
            return $this->assembleComposite($expression, $level);
        }

        throw new \LogicException('Only comparison and composite expressions are supported.');
    }

    /**
     * Assemble comparison definition
     *
     * @param  Comparison $expression
     * @return array
     */
    protected function assembleComparison(Comparison $expression): array
    {
        $property = $expression->getField();
        $operator = $expression->getOperator();
        $value    = $expression->getValue()->getValue();

        if ($operator === Comparison::EQ) {
            return [$property => $value];
        }

        return [
            $property => [self::OPERATOR_MAP[$operator] => $value]
        ];
    }

    /**
     * Assemble composite definition
     *
     * @param  CompositeExpression $expression
     * @param  int                 $level
     * @return array
     */
    protected function assembleComposite(CompositeExpression $expression, int $level): array
    {
        $operator   = $expression->getType();
        $definition = [];

        foreach ($expression->getExpressionList() as $subexpression)
        {
            $definition[] = $this->assembleExpression($subexpression, $level + 1);
        }

        if ($operator === CompositeExpression::TYPE_OR) {
            return ['OR' => $definition];
        }

        if ($level === 0) {
            return $definition;
        }

        return ['AND' => $definition];
    }

    /**
     * Assemble pagination definition
     *
     * @param  Criteria $criteria
     * @return array
     */
    protected function assemblePagination(Criteria $criteria): array
    {
        $offset = $criteria->getFirstResult();
        $limit  = $criteria->getMaxResults();

        if ($offset === null && $limit === null) {
            return [];
        }

        $definition = [];

        if ($offset !== null) {
            $definition['offset'] = $offset;
        }

        if ($limit !== null) {
            $definition['limit'] = $limit;
        }

        return ['page' => $definition];
    }

    /**
     * Assemble sorting definition
     *
     * @param  Criteria $criteria
     * @return array
     */
    protected function assembleSorting(Criteria $criteria): array
    {
        $sorting = $criteria->getOrderings();

        if (empty($sorting)) {
            return [];
        }

        $definition = [];

        foreach ($sorting as $property => $direction)
        {
            $definition[] = ($direction === Criteria::ASC) ? $property : ('-' . $property);
        }

        return ['sort' => implode(',', $definition)];
    }
}