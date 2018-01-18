<?php
declare(strict_types = 1);

namespace Mikemirten\Component\DoctrineCriteriaSerializer\KatharsisQuery;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;
use Mikemirten\Component\DoctrineCriteriaSerializer\CriteriaDeserializer;
use Mikemirten\Component\DoctrineCriteriaSerializer\Exception\InvalidQueryException;

/**
 * Deserializer from query of the Katharsis JSON API library
 *
 * @see http://katharsis.io/
 * @see http://katharsis-jsonapi.readthedocs.io/en/latest/user-docs.html#query-parameters
 *
 * @package Mikemirten\Component\DoctrineCriteriaSerializer\KatharsisRequest
 */
class KatharsisQueryDeserializer implements CriteriaDeserializer
{
    /**
     * Value processing callbacks for filtering by property
     *
     * @var callable[]
     */
    protected $filterCallbacks;

    /**
     * Set value processing callback for a property
     *
     * @param string   $name
     * @param callable $callback
     */
    public function setFilterCallback(string $name,  callable $callback): void
    {
        $this->filterCallbacks[$name] = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize(string $source): Criteria
    {
        $criteria = Criteria::create();
        $this->append($source, $criteria);

        return $criteria;
    }

    /**
     * {@inheritdoc}
     */
    public function append(string $source, Criteria $criteria): void
    {
        $data = $this->parseQuery($source);

        if (isset($data['filter'])) {
            $this->validateFiltering($data['filter']);
            $this->processFiltering($data['filter'], $criteria);
        }

        if (isset($data['sort'])) {
            $this->validateSorting($data['sort']);
            $this->processSorting($data['sort'], $criteria);
        }

        if (isset($data['page'])) {
            $this->validatePagination($data['page']);
            $this->processPagination($data['page'], $criteria);
        }
    }

    /**
     * Validate filtering definition
     *
     * @param  $filtering
     * @throws InvalidQueryException
     */
    protected function validateFiltering($filtering): void
    {
        if (! is_array($filtering)) {
            throw new InvalidQueryException('Attribute "filter" of query must be an array.');
        }
    }

    /**
     * Process filtering
     *
     * @param array    $filters
     * @param Criteria $criteria
     */
    protected function processFiltering(array $filters, Criteria $criteria): void
    {
        foreach ($filters as $name => $definition)
        {
            $name = trim($name);

            if (is_array($definition)) {
                $this->processFilter($name, $definition, $criteria);
                continue;
            }

            $value = trim($definition);

            if (isset($this->filterCallbacks[$name])) {
                $value = ($this->filterCallbacks[$name])($value);
            }

            $criteria->andWhere(Criteria::expr()->eq($name, $value));
        }
    }

    /**
     * Process filter
     *
     * @param string   $name
     * @param array    $values
     * @param Criteria $criteria
     */
    protected function processFilter(string $name, array $values, Criteria $criteria): void
    {
        foreach ($values as $operator => $value)
        {
            if (is_integer($operator)) {
                throw new InvalidQueryException('Operator must be defined by a string.');
            }

            $value = is_array($value) ? array_map('trim', $value) : trim($value);

            $expression = $this->createExpression($name, trim($operator), $value);
            $criteria->andWhere($expression);
        }
    }

    /**
     * Create expression
     *
     * @param  string $name
     * @param  string $operator
     * @param  mixed $value
     * @return Expression
     * @throws InvalidQueryException
     */
    protected function createExpression(string $name, string $operator, $value): Expression
    {
        $operator = strtolower($operator);
        $builder  = Criteria::expr();

        if (! method_exists($builder, $operator)) {
            throw new InvalidQueryException(sprintf(
                'Unsupported operator "%s" given for "%s" property.',
                $operator,
                $name
            ));
        }

        if (isset($this->filterCallbacks[$name])) {
            $value = is_array($value)
                ? array_map($this->filterCallbacks[$name], $value)
                : ($this->filterCallbacks[$name])($value);
        }

        if (($operator === 'in' || $operator === 'notin') && ! is_array($value)) {
            throw new InvalidQueryException('Filtering operators "in" and "notIn" requires an array of values.');
        }

        return $builder->$operator($name, $value);
    }

    /**
     * Validate sorting definition
     *
     * @param  $sorts
     * @throws InvalidQueryException
     */
    protected function validateSorting($sorts): void
    {
        if (! is_string($sorts)) {
            throw new InvalidQueryException('Attribute "sort" of query must be a string.');
        }
    }

    /**
     * Process sorting
     *
     * @param string   $sorts
     * @param Criteria $criteria
     */
    protected function processSorting(string $sorts, Criteria $criteria): void
    {
        $sortList = array_map('trim', explode(',', $sorts));
        $sortMap  = [];

        foreach ($sortList as $item)
        {
            if (strpos($item, '-') === 0) {
                $sortMap[substr($item, 1)] = Criteria::DESC;
                continue;
            }

            $sortMap[$item] = Criteria::ASC;
        }

        $criteria->orderBy($sortMap);
    }

    /**
     * Validate pagination definition
     *
     * @param  $pagination
     * @throws InvalidQueryException
     */
    protected function validatePagination($pagination): void
    {
        if (! is_array($pagination)) {
            throw new InvalidQueryException('Attribute "page" of query must be an array.');
        }

        $unexpected = array_diff(array_keys($pagination), ['offset', 'limit']);

        if (empty($unexpected)) {
            return;
        }

        throw new InvalidQueryException(sprintf(
            'Unexpected member(s) of "page" attribute: "%s". Only "offset" and "limit" are acceptable.',
            implode('", "', $unexpected)
        ));
    }

    /**
     * Process pagination
     *
     * @param array    $pagination
     * @param Criteria $criteria
     */
    protected function processPagination(array $pagination, Criteria $criteria): void
    {
        if (isset($pagination['offset'])) {
            $criteria->setFirstResult((int) $pagination['offset']);
        }

        if (isset($pagination['limit'])) {
            $criteria->setMaxResults((int) $pagination['limit']);
        }
    }

    /**
     * Parse query from string
     *
     * @param  string $source
     * @return array
     */
    protected function parseQuery(string $source): array
    {
        $query = [];
        parse_str($source, $query);

        return $query;
    }
}