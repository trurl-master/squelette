<?php

namespace Squelette\Base;

use \Exception;
use \PDO;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use Squelette\Meta as ChildMeta;
use Squelette\MetaQuery as ChildMetaQuery;
use Squelette\Map\MetaTableMap;

/**
 * Base class that represents a query for the 'meta' table.
 *
 *
 *
 * @method     ChildMetaQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildMetaQuery orderByName($order = Criteria::ASC) Order by the name column
 * @method     ChildMetaQuery orderByTitle($order = Criteria::ASC) Order by the title column
 * @method     ChildMetaQuery orderByDescription($order = Criteria::ASC) Order by the description column
 * @method     ChildMetaQuery orderByKeywords($order = Criteria::ASC) Order by the keywords column
 * @method     ChildMetaQuery orderByCustom($order = Criteria::ASC) Order by the custom column
 *
 * @method     ChildMetaQuery groupById() Group by the id column
 * @method     ChildMetaQuery groupByName() Group by the name column
 * @method     ChildMetaQuery groupByTitle() Group by the title column
 * @method     ChildMetaQuery groupByDescription() Group by the description column
 * @method     ChildMetaQuery groupByKeywords() Group by the keywords column
 * @method     ChildMetaQuery groupByCustom() Group by the custom column
 *
 * @method     ChildMetaQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildMetaQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildMetaQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildMetaQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildMetaQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildMetaQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildMeta findOne(ConnectionInterface $con = null) Return the first ChildMeta matching the query
 * @method     ChildMeta findOneOrCreate(ConnectionInterface $con = null) Return the first ChildMeta matching the query, or a new ChildMeta object populated from the query conditions when no match is found
 *
 * @method     ChildMeta findOneById(int $id) Return the first ChildMeta filtered by the id column
 * @method     ChildMeta findOneByName(string $name) Return the first ChildMeta filtered by the name column
 * @method     ChildMeta findOneByTitle(string $title) Return the first ChildMeta filtered by the title column
 * @method     ChildMeta findOneByDescription(string $description) Return the first ChildMeta filtered by the description column
 * @method     ChildMeta findOneByKeywords(string $keywords) Return the first ChildMeta filtered by the keywords column
 * @method     ChildMeta findOneByCustom(string $custom) Return the first ChildMeta filtered by the custom column *

 * @method     ChildMeta requirePk($key, ConnectionInterface $con = null) Return the ChildMeta by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildMeta requireOne(ConnectionInterface $con = null) Return the first ChildMeta matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildMeta requireOneById(int $id) Return the first ChildMeta filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildMeta requireOneByName(string $name) Return the first ChildMeta filtered by the name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildMeta requireOneByTitle(string $title) Return the first ChildMeta filtered by the title column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildMeta requireOneByDescription(string $description) Return the first ChildMeta filtered by the description column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildMeta requireOneByKeywords(string $keywords) Return the first ChildMeta filtered by the keywords column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildMeta requireOneByCustom(string $custom) Return the first ChildMeta filtered by the custom column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildMeta[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildMeta objects based on current ModelCriteria
 * @method     ChildMeta[]|ObjectCollection findById(int $id) Return ChildMeta objects filtered by the id column
 * @method     ChildMeta[]|ObjectCollection findByName(string $name) Return ChildMeta objects filtered by the name column
 * @method     ChildMeta[]|ObjectCollection findByTitle(string $title) Return ChildMeta objects filtered by the title column
 * @method     ChildMeta[]|ObjectCollection findByDescription(string $description) Return ChildMeta objects filtered by the description column
 * @method     ChildMeta[]|ObjectCollection findByKeywords(string $keywords) Return ChildMeta objects filtered by the keywords column
 * @method     ChildMeta[]|ObjectCollection findByCustom(string $custom) Return ChildMeta objects filtered by the custom column
 * @method     ChildMeta[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class MetaQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Squelette\Base\MetaQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'main', $modelName = '\\Squelette\\Meta', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildMetaQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildMetaQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildMetaQuery) {
            return $criteria;
        }
        $query = new ChildMetaQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildMeta|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(MetaTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = MetaTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
            // the object is already in the instance pool
            return $obj;
        }

        return $this->findPkSimple($key, $con);
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildMeta A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, name, title, description, keywords, custom FROM meta WHERE id = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildMeta $obj */
            $obj = new ChildMeta();
            $obj->hydrate($row);
            MetaTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return ChildMeta|array|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, ConnectionInterface $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($dataFetcher);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(12, 56, 832), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return ObjectCollection|array|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection($this->getDbName());
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($dataFetcher);
    }

    /**
     * Filter the query by primary key
     *
     * @param     mixed $key Primary key to use for the query
     *
     * @return $this|ChildMetaQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(MetaTableMap::COL_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildMetaQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(MetaTableMap::COL_ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE id = 1234
     * $query->filterById(array(12, 34)); // WHERE id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE id > 12
     * </code>
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildMetaQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(MetaTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(MetaTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MetaTableMap::COL_ID, $id, $comparison);
    }

    /**
     * Filter the query on the name column
     *
     * Example usage:
     * <code>
     * $query->filterByName('fooValue');   // WHERE name = 'fooValue'
     * $query->filterByName('%fooValue%', Criteria::LIKE); // WHERE name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $name The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildMetaQuery The current query, for fluid interface
     */
    public function filterByName($name = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($name)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MetaTableMap::COL_NAME, $name, $comparison);
    }

    /**
     * Filter the query on the title column
     *
     * Example usage:
     * <code>
     * $query->filterByTitle('fooValue');   // WHERE title = 'fooValue'
     * $query->filterByTitle('%fooValue%', Criteria::LIKE); // WHERE title LIKE '%fooValue%'
     * </code>
     *
     * @param     string $title The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildMetaQuery The current query, for fluid interface
     */
    public function filterByTitle($title = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($title)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MetaTableMap::COL_TITLE, $title, $comparison);
    }

    /**
     * Filter the query on the description column
     *
     * Example usage:
     * <code>
     * $query->filterByDescription('fooValue');   // WHERE description = 'fooValue'
     * $query->filterByDescription('%fooValue%', Criteria::LIKE); // WHERE description LIKE '%fooValue%'
     * </code>
     *
     * @param     string $description The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildMetaQuery The current query, for fluid interface
     */
    public function filterByDescription($description = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($description)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MetaTableMap::COL_DESCRIPTION, $description, $comparison);
    }

    /**
     * Filter the query on the keywords column
     *
     * Example usage:
     * <code>
     * $query->filterByKeywords('fooValue');   // WHERE keywords = 'fooValue'
     * $query->filterByKeywords('%fooValue%', Criteria::LIKE); // WHERE keywords LIKE '%fooValue%'
     * </code>
     *
     * @param     string $keywords The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildMetaQuery The current query, for fluid interface
     */
    public function filterByKeywords($keywords = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($keywords)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MetaTableMap::COL_KEYWORDS, $keywords, $comparison);
    }

    /**
     * Filter the query on the custom column
     *
     * Example usage:
     * <code>
     * $query->filterByCustom('fooValue');   // WHERE custom = 'fooValue'
     * $query->filterByCustom('%fooValue%', Criteria::LIKE); // WHERE custom LIKE '%fooValue%'
     * </code>
     *
     * @param     string $custom The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildMetaQuery The current query, for fluid interface
     */
    public function filterByCustom($custom = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($custom)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(MetaTableMap::COL_CUSTOM, $custom, $comparison);
    }

    /**
     * Exclude object from result
     *
     * @param   ChildMeta $meta Object to remove from the list of results
     *
     * @return $this|ChildMetaQuery The current query, for fluid interface
     */
    public function prune($meta = null)
    {
        if ($meta) {
            $this->addUsingAlias(MetaTableMap::COL_ID, $meta->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the meta table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(MetaTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            MetaTableMap::clearInstancePool();
            MetaTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    /**
     * Performs a DELETE on the database based on the current ModelCriteria
     *
     * @param ConnectionInterface $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public function delete(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(MetaTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(MetaTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            MetaTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            MetaTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // MetaQuery
