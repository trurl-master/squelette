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
use Squelette\User as ChildUser;
use Squelette\UserQuery as ChildUserQuery;
use Squelette\Map\UserTableMap;

/**
 * Base class that represents a query for the 'user' table.
 *
 *
 *
 * @method     ChildUserQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildUserQuery orderByEmail($order = Criteria::ASC) Order by the email column
 * @method     ChildUserQuery orderByPassword($order = Criteria::ASC) Order by the password column
 * @method     ChildUserQuery orderByFirstName($order = Criteria::ASC) Order by the first_name column
 * @method     ChildUserQuery orderByLastName($order = Criteria::ASC) Order by the last_name column
 * @method     ChildUserQuery orderByDtCreated($order = Criteria::ASC) Order by the dt_created column
 * @method     ChildUserQuery orderByDtLastSignin($order = Criteria::ASC) Order by the dt_last_signin column
 * @method     ChildUserQuery orderByHybridauthProviderName($order = Criteria::ASC) Order by the hybridauth_provider_name column
 * @method     ChildUserQuery orderByHybridauthProviderUid($order = Criteria::ASC) Order by the hybridauth_provider_uid column
 * @method     ChildUserQuery orderByInit($order = Criteria::ASC) Order by the init column
 * @method     ChildUserQuery orderByRestore($order = Criteria::ASC) Order by the restore column
 * @method     ChildUserQuery orderByPrivilege($order = Criteria::ASC) Order by the privilege column
 *
 * @method     ChildUserQuery groupById() Group by the id column
 * @method     ChildUserQuery groupByEmail() Group by the email column
 * @method     ChildUserQuery groupByPassword() Group by the password column
 * @method     ChildUserQuery groupByFirstName() Group by the first_name column
 * @method     ChildUserQuery groupByLastName() Group by the last_name column
 * @method     ChildUserQuery groupByDtCreated() Group by the dt_created column
 * @method     ChildUserQuery groupByDtLastSignin() Group by the dt_last_signin column
 * @method     ChildUserQuery groupByHybridauthProviderName() Group by the hybridauth_provider_name column
 * @method     ChildUserQuery groupByHybridauthProviderUid() Group by the hybridauth_provider_uid column
 * @method     ChildUserQuery groupByInit() Group by the init column
 * @method     ChildUserQuery groupByRestore() Group by the restore column
 * @method     ChildUserQuery groupByPrivilege() Group by the privilege column
 *
 * @method     ChildUserQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildUserQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildUserQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildUserQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildUserQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildUserQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildUser findOne(ConnectionInterface $con = null) Return the first ChildUser matching the query
 * @method     ChildUser findOneOrCreate(ConnectionInterface $con = null) Return the first ChildUser matching the query, or a new ChildUser object populated from the query conditions when no match is found
 *
 * @method     ChildUser findOneById(int $id) Return the first ChildUser filtered by the id column
 * @method     ChildUser findOneByEmail(string $email) Return the first ChildUser filtered by the email column
 * @method     ChildUser findOneByPassword(string $password) Return the first ChildUser filtered by the password column
 * @method     ChildUser findOneByFirstName(string $first_name) Return the first ChildUser filtered by the first_name column
 * @method     ChildUser findOneByLastName(string $last_name) Return the first ChildUser filtered by the last_name column
 * @method     ChildUser findOneByDtCreated(string $dt_created) Return the first ChildUser filtered by the dt_created column
 * @method     ChildUser findOneByDtLastSignin(string $dt_last_signin) Return the first ChildUser filtered by the dt_last_signin column
 * @method     ChildUser findOneByHybridauthProviderName(string $hybridauth_provider_name) Return the first ChildUser filtered by the hybridauth_provider_name column
 * @method     ChildUser findOneByHybridauthProviderUid(string $hybridauth_provider_uid) Return the first ChildUser filtered by the hybridauth_provider_uid column
 * @method     ChildUser findOneByInit(string $init) Return the first ChildUser filtered by the init column
 * @method     ChildUser findOneByRestore(string $restore) Return the first ChildUser filtered by the restore column
 * @method     ChildUser findOneByPrivilege(int $privilege) Return the first ChildUser filtered by the privilege column *

 * @method     ChildUser requirePk($key, ConnectionInterface $con = null) Return the ChildUser by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOne(ConnectionInterface $con = null) Return the first ChildUser matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildUser requireOneById(int $id) Return the first ChildUser filtered by the id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByEmail(string $email) Return the first ChildUser filtered by the email column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByPassword(string $password) Return the first ChildUser filtered by the password column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByFirstName(string $first_name) Return the first ChildUser filtered by the first_name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByLastName(string $last_name) Return the first ChildUser filtered by the last_name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByDtCreated(string $dt_created) Return the first ChildUser filtered by the dt_created column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByDtLastSignin(string $dt_last_signin) Return the first ChildUser filtered by the dt_last_signin column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByHybridauthProviderName(string $hybridauth_provider_name) Return the first ChildUser filtered by the hybridauth_provider_name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByHybridauthProviderUid(string $hybridauth_provider_uid) Return the first ChildUser filtered by the hybridauth_provider_uid column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByInit(string $init) Return the first ChildUser filtered by the init column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByRestore(string $restore) Return the first ChildUser filtered by the restore column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByPrivilege(int $privilege) Return the first ChildUser filtered by the privilege column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildUser[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildUser objects based on current ModelCriteria
 * @method     ChildUser[]|ObjectCollection findById(int $id) Return ChildUser objects filtered by the id column
 * @method     ChildUser[]|ObjectCollection findByEmail(string $email) Return ChildUser objects filtered by the email column
 * @method     ChildUser[]|ObjectCollection findByPassword(string $password) Return ChildUser objects filtered by the password column
 * @method     ChildUser[]|ObjectCollection findByFirstName(string $first_name) Return ChildUser objects filtered by the first_name column
 * @method     ChildUser[]|ObjectCollection findByLastName(string $last_name) Return ChildUser objects filtered by the last_name column
 * @method     ChildUser[]|ObjectCollection findByDtCreated(string $dt_created) Return ChildUser objects filtered by the dt_created column
 * @method     ChildUser[]|ObjectCollection findByDtLastSignin(string $dt_last_signin) Return ChildUser objects filtered by the dt_last_signin column
 * @method     ChildUser[]|ObjectCollection findByHybridauthProviderName(string $hybridauth_provider_name) Return ChildUser objects filtered by the hybridauth_provider_name column
 * @method     ChildUser[]|ObjectCollection findByHybridauthProviderUid(string $hybridauth_provider_uid) Return ChildUser objects filtered by the hybridauth_provider_uid column
 * @method     ChildUser[]|ObjectCollection findByInit(string $init) Return ChildUser objects filtered by the init column
 * @method     ChildUser[]|ObjectCollection findByRestore(string $restore) Return ChildUser objects filtered by the restore column
 * @method     ChildUser[]|ObjectCollection findByPrivilege(int $privilege) Return ChildUser objects filtered by the privilege column
 * @method     ChildUser[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class UserQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \Squelette\Base\UserQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'main', $modelName = '\\Squelette\\User', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildUserQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildUserQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildUserQuery) {
            return $criteria;
        }
        $query = new ChildUserQuery();
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
     * @return ChildUser|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(UserTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = UserTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildUser A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT id, email, password, first_name, last_name, dt_created, dt_last_signin, hybridauth_provider_name, hybridauth_provider_uid, init, restore, privilege FROM user WHERE id = :p0';
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
            /** @var ChildUser $obj */
            $obj = new ChildUser();
            $obj->hydrate($row);
            UserTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildUser|array|mixed the result, formatted by the current formatter
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
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(UserTableMap::COL_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(UserTableMap::COL_ID, $keys, Criteria::IN);
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
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(UserTableMap::COL_ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(UserTableMap::COL_ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserTableMap::COL_ID, $id, $comparison);
    }

    /**
     * Filter the query on the email column
     *
     * Example usage:
     * <code>
     * $query->filterByEmail('fooValue');   // WHERE email = 'fooValue'
     * $query->filterByEmail('%fooValue%', Criteria::LIKE); // WHERE email LIKE '%fooValue%'
     * </code>
     *
     * @param     string $email The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByEmail($email = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($email)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserTableMap::COL_EMAIL, $email, $comparison);
    }

    /**
     * Filter the query on the password column
     *
     * Example usage:
     * <code>
     * $query->filterByPassword('fooValue');   // WHERE password = 'fooValue'
     * $query->filterByPassword('%fooValue%', Criteria::LIKE); // WHERE password LIKE '%fooValue%'
     * </code>
     *
     * @param     string $password The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByPassword($password = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($password)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserTableMap::COL_PASSWORD, $password, $comparison);
    }

    /**
     * Filter the query on the first_name column
     *
     * Example usage:
     * <code>
     * $query->filterByFirstName('fooValue');   // WHERE first_name = 'fooValue'
     * $query->filterByFirstName('%fooValue%', Criteria::LIKE); // WHERE first_name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $firstName The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByFirstName($firstName = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($firstName)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserTableMap::COL_FIRST_NAME, $firstName, $comparison);
    }

    /**
     * Filter the query on the last_name column
     *
     * Example usage:
     * <code>
     * $query->filterByLastName('fooValue');   // WHERE last_name = 'fooValue'
     * $query->filterByLastName('%fooValue%', Criteria::LIKE); // WHERE last_name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $lastName The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByLastName($lastName = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($lastName)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserTableMap::COL_LAST_NAME, $lastName, $comparison);
    }

    /**
     * Filter the query on the dt_created column
     *
     * Example usage:
     * <code>
     * $query->filterByDtCreated('2011-03-14'); // WHERE dt_created = '2011-03-14'
     * $query->filterByDtCreated('now'); // WHERE dt_created = '2011-03-14'
     * $query->filterByDtCreated(array('max' => 'yesterday')); // WHERE dt_created > '2011-03-13'
     * </code>
     *
     * @param     mixed $dtCreated The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByDtCreated($dtCreated = null, $comparison = null)
    {
        if (is_array($dtCreated)) {
            $useMinMax = false;
            if (isset($dtCreated['min'])) {
                $this->addUsingAlias(UserTableMap::COL_DT_CREATED, $dtCreated['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dtCreated['max'])) {
                $this->addUsingAlias(UserTableMap::COL_DT_CREATED, $dtCreated['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserTableMap::COL_DT_CREATED, $dtCreated, $comparison);
    }

    /**
     * Filter the query on the dt_last_signin column
     *
     * Example usage:
     * <code>
     * $query->filterByDtLastSignin('2011-03-14'); // WHERE dt_last_signin = '2011-03-14'
     * $query->filterByDtLastSignin('now'); // WHERE dt_last_signin = '2011-03-14'
     * $query->filterByDtLastSignin(array('max' => 'yesterday')); // WHERE dt_last_signin > '2011-03-13'
     * </code>
     *
     * @param     mixed $dtLastSignin The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByDtLastSignin($dtLastSignin = null, $comparison = null)
    {
        if (is_array($dtLastSignin)) {
            $useMinMax = false;
            if (isset($dtLastSignin['min'])) {
                $this->addUsingAlias(UserTableMap::COL_DT_LAST_SIGNIN, $dtLastSignin['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($dtLastSignin['max'])) {
                $this->addUsingAlias(UserTableMap::COL_DT_LAST_SIGNIN, $dtLastSignin['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserTableMap::COL_DT_LAST_SIGNIN, $dtLastSignin, $comparison);
    }

    /**
     * Filter the query on the hybridauth_provider_name column
     *
     * Example usage:
     * <code>
     * $query->filterByHybridauthProviderName('fooValue');   // WHERE hybridauth_provider_name = 'fooValue'
     * $query->filterByHybridauthProviderName('%fooValue%', Criteria::LIKE); // WHERE hybridauth_provider_name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $hybridauthProviderName The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByHybridauthProviderName($hybridauthProviderName = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($hybridauthProviderName)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserTableMap::COL_HYBRIDAUTH_PROVIDER_NAME, $hybridauthProviderName, $comparison);
    }

    /**
     * Filter the query on the hybridauth_provider_uid column
     *
     * Example usage:
     * <code>
     * $query->filterByHybridauthProviderUid('fooValue');   // WHERE hybridauth_provider_uid = 'fooValue'
     * $query->filterByHybridauthProviderUid('%fooValue%', Criteria::LIKE); // WHERE hybridauth_provider_uid LIKE '%fooValue%'
     * </code>
     *
     * @param     string $hybridauthProviderUid The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByHybridauthProviderUid($hybridauthProviderUid = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($hybridauthProviderUid)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserTableMap::COL_HYBRIDAUTH_PROVIDER_UID, $hybridauthProviderUid, $comparison);
    }

    /**
     * Filter the query on the init column
     *
     * Example usage:
     * <code>
     * $query->filterByInit('fooValue');   // WHERE init = 'fooValue'
     * $query->filterByInit('%fooValue%', Criteria::LIKE); // WHERE init LIKE '%fooValue%'
     * </code>
     *
     * @param     string $init The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByInit($init = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($init)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserTableMap::COL_INIT, $init, $comparison);
    }

    /**
     * Filter the query on the restore column
     *
     * Example usage:
     * <code>
     * $query->filterByRestore('fooValue');   // WHERE restore = 'fooValue'
     * $query->filterByRestore('%fooValue%', Criteria::LIKE); // WHERE restore LIKE '%fooValue%'
     * </code>
     *
     * @param     string $restore The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByRestore($restore = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($restore)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserTableMap::COL_RESTORE, $restore, $comparison);
    }

    /**
     * Filter the query on the privilege column
     *
     * Example usage:
     * <code>
     * $query->filterByPrivilege(1234); // WHERE privilege = 1234
     * $query->filterByPrivilege(array(12, 34)); // WHERE privilege IN (12, 34)
     * $query->filterByPrivilege(array('min' => 12)); // WHERE privilege > 12
     * </code>
     *
     * @param     mixed $privilege The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByPrivilege($privilege = null, $comparison = null)
    {
        if (is_array($privilege)) {
            $useMinMax = false;
            if (isset($privilege['min'])) {
                $this->addUsingAlias(UserTableMap::COL_PRIVILEGE, $privilege['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($privilege['max'])) {
                $this->addUsingAlias(UserTableMap::COL_PRIVILEGE, $privilege['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserTableMap::COL_PRIVILEGE, $privilege, $comparison);
    }

    /**
     * Exclude object from result
     *
     * @param   ChildUser $user Object to remove from the list of results
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function prune($user = null)
    {
        if ($user) {
            $this->addUsingAlias(UserTableMap::COL_ID, $user->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the user table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(UserTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            UserTableMap::clearInstancePool();
            UserTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(UserTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(UserTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            UserTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            UserTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // UserQuery
