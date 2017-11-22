<?php

namespace Isholao\SqlDb;

/**
 * @author Ishola O <ishola.tolu@outlook.com>
 */
class PDO
{

    use YieldTrait;
    use YieldFetch;

    protected $pdo;
    protected $username;
    protected $password;
    protected $dsn;
    protected $options;
    protected $profile = [];
    protected $attributes = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
    ];
    protected $profiler;

    function __construct(string $dsn, ?string $username = NULL,
                         ?string $password = NULL, ?array $options = NULL)
    {
        $this->dsn = $dsn;
        $this->username = $username;
        $this->password = $password;
        // if no error mode is specified, use exceptions
        if (!isset($options[\PDO::ATTR_ERRMODE]))
        {
            $options[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
        }
        $this->options = $options;
    }

    /**
     *
     * Connects to the database and sets PDO attributes.
     *
     * @return NULL
     *
     * @throws PDOException if the connection fails.
     *
     */
    public function connect()
    {
        // don't connect twice
        if ($this->pdo)
        {
            return;
        }
        // connect to the database
        $this->beginProfile(__METHOD__);
        $this->pdo = new \PDO($this->dsn, $this->username, $this->password,
                              $this->options);
        $this->endProfile();
        // set attributes
        foreach ($this->attributes as $attribute => $value)
        {
            $this->setAttribute($attribute, $value);
        }
    }

    /**
     *
     * Explicitly disconnect by unsetting the PDO instance; does not prevent
     * later reconnection, whether implicit or explicit.
     *
     * @return void
     */
    public function disconnect(): void
    {
        $this->pdo = NULL;
    }

    /**
     *
     * Begins a transaction and turns off autocommit mode.
     *
     * @return bool True on success, FALSE on failure.
     *
     * @see http://php.net/manual/en/pdo.begintransaction.php
     *
     */
    public function beginTransaction(): bool
    {
        $this->connect();
        $this->beginProfile(__METHOD__);
        $result = $this->pdo->beginTransaction();
        $this->endProfile();
        return $result;
    }

    /**
     *
     * Commits the existing transaction and restores autocommit mode.
     *
     * @return bool True on success, FALSE on failure.
     *
     * @see http://php.net/manual/en/pdo.commit.php
     *
     */
    public function commit(): bool
    {
        $this->connect();
        $this->beginProfile(__METHOD__);
        $result = $this->pdo->commit();
        $this->endProfile();
        return $result;
    }

    /**
     *
     * Gets the most recent error code.
     *
     * @return mixed
     *
     */
    public function errorCode()
    {
        $this->connect();
        return $this->pdo->errorCode();
    }

    /**
     *
     * Gets the most recent error info.
     *
     * @return array
     *
     */
    public function errorInfo(): array
    {
        $this->connect();
        return $this->pdo->errorInfo();
    }

    /**
     *
     * Executes an SQL statement and returns the number of affected rows.
     *
     * @param string $statement The SQL statement to prepare and execute.
     *
     * @return int The number of affected rows.
     *
     * @see http://php.net/manual/en/pdo.exec.php
     *
     */
    public function exec(string $statement): int
    {
        $this->connect();
        $this->beginProfile(__METHOD__);
        $affectedRows = $this->pdo->exec($statement);
        $this->endProfile($statement);
        return $affectedRows;
    }

    /**
     *
     * Gets a PDO attribute value.
     *
     * @param int $attribute The PDO::ATTR_* constant.
     *
     * @return mixed The value for the attribute.
     *
     */
    public function getAttribute(int $attribute)
    {
        $this->connect();
        return $this->pdo->getAttribute($attribute);
    }

    /**
     *
     * Returns the DSN for a lazy connection; if the underlying PDO instance
     * was injected at construction time, this will be NULL.
     *
     * @return string|NULL
     *
     */
    public function getDsn(): ?string
    {
        return $this->dsn;
    }

    /**
     *
     * Returns the underlying PDO connection object.
     *
     * @return \PDO
     *
     */
    public function getPdo(): \PDO
    {
        $this->connect();
        return $this->pdo;
    }

    /**
     *
     * Returns the profiler object.
     *
     * @return ProfilerInterface
     *
     */
    public function getProfiler(): ProfilerInterface
    {
        return $this->profiler;
    }

    /**
     *
     * Is a transaction currently active?
     *
     * @return bool
     *
     * @see http://php.net/manual/en/pdo.intransaction.php
     *
     */
    public function inTransaction(): bool
    {
        $this->connect();
        $this->beginProfile(__METHOD__);
        $result = $this->pdo->inTransaction();
        $this->endProfile();
        return $result;
    }

    /**
     *
     * Is this instance connected to a database?
     *
     * @return bool
     *
     */
    public function isConnected(): bool
    {
        return isset($this->pdo);
    }

    /**
     *
     * Returns the last inserted autoincrement sequence value.
     *
     * @param string $name The name of the sequence to check; typically needed
     * only for PostgreSQL, where it takes the form of `<table>_<column>_seq`.
     *
     * @return int
     *
     * @see http://php.net/manual/en/pdo.lastinsertid.php
     *
     */
    public function lastInsertId(string $name = NULL): int
    {
        $this->connect();
        $this->beginProfile(__METHOD__);
        $result = $this->pdo->lastInsertId($name);
        $this->endProfile();
        return $result;
    }

    /**
     *
     * Performs a query with bound values and returns the resulting
     * PDOStatement; array values will be passed through `quote()` and their
     * respective placeholders will be replaced in the query string.
     *
     * @param string $statement The SQL statement to perform.
     *
     * @param array $values Values to bind to the query
     *
     * @return \PDOStatement
     *
     * @see quote()
     *
     */
    public function perform(string $statement, array $values = []): \PDOStatement
    {
        $sth = $this->prepareWithValues($statement, $values);
        $this->beginProfile(__METHOD__);
        $sth->execute();
        $this->endProfile($statement, $values);
        return $sth;
    }

    /**
     *
     * Prepares an SQL statement for execution.
     *
     * @param string $statement The SQL statement to prepare for execution.
     *
     * @param array $options Set these attributes on the returned
     * PDOStatement.
     *
     * @return \PDOStatement
     *
     * @see http://php.net/manual/en/pdo.prepare.php
     *
     */
    public function prepare(string $statement, array $options = []): \PDOStatement
    {
        $this->connect();
        $this->beginProfile(__METHOD__);
        $sth = $this->pdo->prepare($statement, $options);
        $this->endProfile($statement, $options);
        return $sth;
    }

    /**
     *
     * Queries the database and returns a PDOStatement.
     *
     * @param string $statement The SQL statement to prepare and execute.
     *
     * @param int $fetch_mode The `PDO::FETCH_*` type to set on the returned
     * `PDOStatement::setFetchMode()`.
     *
     * @param mixed $fetch_arg1 The first additional argument to send to
     * `PDOStatement::setFetchMode()`.
     *
     * @param mixed $fetch_arg2 The second additional argument to send to
     * `PDOStatement::setFetchMode()`.
     *
     * @return \PDOStatement
     *
     * @see http://php.net/manual/en/pdo.query.php
     *
     */
    public function query(...$args): \PDOStatement
    {
        $this->connect();
        $this->beginProfile(__METHOD__);
        // remove empty constructor params list if it exists
        if (\count($args) === 4 && $args[3] === [])
        {
            unset($args[3]);
        }
        $sth = \call_user_func_array([$this->pdo, 'query'], $args);
        $this->endProfile($sth->queryString);
        return $sth;
    }

    /**
     *
     * Quotes a value for use in an SQL statement.
     *
     * This differs from `PDO::quote()` in that it will convert an array into
     * a string of comma-separated quoted values.
     *
     * @param mixed $value The value to quote.
     *
     * @param int $parameterType A data type hint for the database driver.
     *
     * @return mixed The quoted value.
     *
     * @see http://php.net/manual/en/pdo.quote.php
     *
     */
    public function quote($value, int $parameterType = \PDO::PARAM_STR)
    {
        $this->connect();
        // non-array quoting
        if (!\is_array($value))
        {
            return $this->pdo->quote($value, $parameterType);
        }
        // quote array values, not keys, then combine with commas
        foreach ($value as $k => $v)
        {
            $value[$k] = $this->pdo->quote($v, $parameterType);
        }
        return \implode(', ', $value);
    }

    /**
     *
     * Rolls back the current transaction, and restores autocommit mode.
     *
     * @return bool True on success, FALSE on failure.
     *
     * @see http://php.net/manual/en/pdo.rollback.php
     *
     */
    public function rollBack(): bool
    {
        $this->connect();
        $this->beginProfile(__METHOD__);
        $result = $this->pdo->rollBack();
        $this->endProfile();
        return $result;
    }

    /**
     *
     * Sets a PDO attribute value.
     *
     * @param mixed $attribute The PDO::ATTR_* constant.
     *
     * @param mixed $value The value for the attribute.
     *
     * @return bool True on success, FALSE on failure. Note that if PDO has not
     * not connected, all calls will be treated as successful.
     *
     */
    public function setAttribute(int $attribute, $value): bool
    {
        if ($this->pdo)
        {
            return $this->pdo->setAttribute($attribute, $value);
        }
        $this->attributes[$attribute] = $value;
        return TRUE;
    }

    /**
     *
     * Sets the profiler object.
     *
     * @param ProfilerInterface $profiler
     *
     * @return void
     *
     */
    public function setProfiler(ProfilerInterface $profiler): void
    {
        $this->profiler = $profiler;
    }

    /**
     *
     * Begins a profile entry.
     *
     * @param string $function The function starting the profile entry.
     *
     * @return void
     *
     */
    protected function beginProfile(string $function): void
    {
        // if there's no profiler, can't profile
        if (!$this->profiler)
        {
            return;
        }

        // retain starting profile info
        $this->profile['time'] = \microtime(TRUE);
        $this->profile['function'] = $function;
    }

    /**
     *
     * Ends and records a profile entry.
     *
     * @param string $statement The statement being profiled, if any.
     *
     * @param array $values The values bound to the statement, if any.
     *
     * @return void
     *
     */
    protected function endProfile(?string $statement = NULL, array $values = []): void
    {
        // is there a profiler in place?
        if ($this->profiler)
        {
            // add an entry to the profiler
            $this->profiler->addProfile(
                    \microtime(TRUE) - $this->profile['time'],
                               $this->profile['function'], $statement, $values
            );
        }

        // clear the starting profile info
        $this->profile = [];
    }

    /**
     *
     * Prepares an SQL statement with bound values.
     *
     * This method only binds values that have placeholders in the
     * statement, thereby avoiding errors from PDO regarding too many bound
     * values. It also binds all sequential (question-mark) placeholders.
     *
     * If a placeholder value is an array, the array is converted to a string
     * of comma-separated quoted values; e.g., for an `IN (...)` condition.
     * The quoted string is replaced directly into the statement instead of
     * using `PDOStatement::bindValue()` proper.
     *
     * @param string $statement The SQL statement to prepare for execution.
     *
     * @param array $values The values to bind to the statement, if any.
     *
     * @return \PDOStatement
     *
     * @see http://php.net/manual/en/pdo.prepare.php
     *
     */
    public function prepareWithValues(string $statement, ?array $values = []): \PDOStatement
    {
        // if there are no values to bind ...
        if (!$values)
        {
            return $this->prepare($statement);
        }
        // prepare the statement
        $sth = $this->prepare($statement);
        // for the placeholders we found, bind the corresponding data values
        foreach ($values as $key => $val)
        {
            $this->bindValue($sth, $key, $val);
        }
        // done
        return $sth;
    }

    /**
     *
     * Bind a value using the proper PDO::PARAM_* type.
     *
     * @param PDOStatement $sth The statement to bind to.
     *
     * @param mixed $key The placeholder key.
     *
     * @param mixed $val The value to bind to the statement.
     *
     *
     * @throws \Error when the value to be bound is not
     * bindable (e.g., array, object, or resource).
     *
     */
    protected function bindValue(\PDOStatement &$sth, $key, $val)
    {
        if (\is_int($val))
        {
            return $sth->bindValue($key, $val, \PDO::PARAM_INT);
        }
        if (\is_bool($val))
        {
            return $sth->bindValue($key, $val, \PDO::PARAM_BOOL);
        }
        if (\is_NULL($val))
        {
            return $sth->bindValue($key, $val, \PDO::PARAM_NULL);
        }
        if (!\is_scalar($val))
        {
            $type = \gettype($val);
            throw new \Error("Cannot bind value of type '{$type}' to placeholder '{$key}'");
        }
        $sth->bindValue($key, $val);
    }

}
