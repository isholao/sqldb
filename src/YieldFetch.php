<?php

namespace Isholao\SqlDb;

/**
 * @author Ishola O <ishola.tolu@outlook.com>
 */
trait YieldFetch
{

    /**
     *
     * Performs a statement and returns the number of affected rows.
     *
     * @param string $statement The SQL statement to prepare and execute.
     *
     * @param array $values Values to bind to the query.
     *
     * @return int
     *
     */
    public function fetchAffected(string $statement, array $values = []): int
    {
        return $this->perform($statement, $values)->rowCount();
    }

    /**
     *
     * Fetches a sequential array of rows from the database; the rows
     * are returned as associative arrays.
     *
     * @param string $statement The SQL statement to prepare and execute.
     *
     * @param array $values Values to bind to the query.
     *
     * @param callable $callable A callable to be applied to each of the rows
     * to be returned.
     *
     * @return array
     *
     */
    public function fetchAll(string $statement, array $values = [],
                             callable $callable = NULL): array
    {
        return $this->fetchAllWithCallable(\PDO::FETCH_ASSOC, $statement,
                                           $values, $callable);
    }

    /**
     *
     * Support for fetchAll() and fetchCol().
     *
     * @param string $fetchType A PDO FETCH_* constant.
     *
     * @param string $statement The SQL statement to prepare and execute.
     *
     * @param array $values Values to bind to the query.
     *
     * @param callable $callable A callable to be applied to each of the rows
     * to be returned.
     *
     * @return array
     *
     */
    protected function fetchAllWithCallable(int $fetchType, string $statement,
                                            array $values = [],
                                            ?callable $callable = NULL): array
    {
        $sth = $this->perform($statement, $values);
        if ($fetchType == \PDO::FETCH_COLUMN)
        {
            $data = $sth->fetchAll($fetchType, 0);
        } else
        {
            $data = $sth->fetchAll($fetchType);
        }

        if ($callable)
        {
            foreach ($data as $key => $row)
            {
                $data[$key] = \call_user_func($callable, $row);
            }
        }

        return $data;
    }

    /**
     *
     * Fetches an associative array of rows from the database; the rows
     * are returned as associative arrays, and the array of rows is keyed
     * on the first column of each row.
     *
     * N.b.: If multiple rows have the same first column value, the last
     * row with that value will override earlier rows.
     *
     * @param string $statement The SQL statement to prepare and execute.
     *
     * @param array $values Values to bind to the query.
     *
     * @param callable $callable A callable to be applied to each of the rows
     * to be returned.
     *
     * @return array
     *
     */
    public function fetchAssoc(string $statement, array $values = [],
                               ?callable $callable = NULL): array
    {
        $sth = $this->perform($statement, $values);
        $is_callable = \is_callable($callable);

        $data = [];
        while ($row = $sth->fetch(\PDO::FETCH_ASSOC))
        {
            $key = \current($row);
            $data[$key] = $is_callable ? \call_user_func($callable, $row) : $row;
        }
        return $data;
    }

    /**
     *
     * Fetches the first column of rows as a sequential array.
     *
     * @param string $statement The SQL statement to prepare and execute.
     *
     * @param array $values Values to bind to the query.
     *
     * @param callable $callable A callable to be applied to each of the rows
     * to be returned.
     *
     * @return array
     *
     */
    public function fetchCol($statement, array $values = [],
                             ?callable $callable = NULL)
    {
        return $this->fetchAllWithCallable(\PDO::FETCH_COLUMN, $statement,
                                           $values, $callable);
    }

    /**
     *
     * Fetches one row from the database as an object where the column values
     * are mapped to object properties.
     *
     * Warning: PDO "injects property-values BEFORE invoking the constructor -
     * in other words, if your class initializes property-values to defaults
     * in the constructor, you will be overwriting the values injected by
     * fetchObject() !"
     * <http://www.php.net/manual/en/pdostatement.fetchobject.php#111744>
     *
     * @param string $statement The SQL statement to prepare and execute.
     *
     * @param array $values Values to bind to the query.
     *
     * @param string $className The name of the class to create.
     *
     * @param array $ctorArgs Arguments to pass to the object constructor.
     *
     * @return object|false
     *
     */
    public function fetchObject(string $statement, array $values = [],
                                string $className = 'StdClass',
                                array $ctorArgs = [
            ])
    {
        $sth = $this->perform($statement, $values);
        return $ctorArgs ? $sth->fetchObject($className, $ctorArgs) : $sth->fetchObject($className);
    }

    /**
     *
     * Fetches a sequential array of rows from the database; the rows
     * are returned as objects where the column values are mapped to
     * object properties.
     *
     * Warning: PDO "injects property-values BEFORE invoking the constructor -
     * in other words, if your class initializes property-values to defaults
     * in the constructor, you will be overwriting the values injected by
     * fetchObject() !"
     * <http://www.php.net/manual/en/pdostatement.fetchobject.php#111744>
     *
     * @param string $statement The SQL statement to prepare and execute.
     *
     * @param array $values Values to bind to the query.
     *
     * @param string $className The name of the class to create from each
     * row.
     *
     * @param array $ctorArgs Arguments to pass to each object constructor.
     *
     * @return array
     *
     */
    public function fetchObjects(string $statement, array $values = [],
                                 string $className = 'StdClass',
                                 array $ctorArgs = []): array
    {
        $sth = $this->perform($statement, $values);
        return $ctorArgs ? $sth->fetchAll(\PDO::FETCH_CLASS, $className,
                                           $ctorArgs) : $sth->fetchAll(\PDO::FETCH_CLASS,
                                                                        $className);
    }

    /**
     *
     * Fetches one row from the database as an associative array.
     *
     * @param string $statement The SQL statement to prepare and execute.
     *
     * @param array $values Values to bind to the query.
     *
     * @return array|false
     *
     */
    public function fetchOne(string $statement, array $values = [])
    {
        return $this->perform($statement, $values)->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     *
     * Fetches an associative array of rows as key-value pairs (first
     * column is the key, second column is the value).
     *
     * @param string $statement The SQL statement to prepare and execute.
     *
     * @param array $values Values to bind to the query.
     *
     * @param callable $callable A callable to be applied to each of the rows
     * to be returned.
     *
     * @return array
     *
     */
    public function fetchPairs(string $statement, array $values = [],
                               ?callable $callable = NULL): array
    {
        $sth = $this->perform($statement, $values);
        if ($callable)
        {
            $data = [];
            while ($row = $sth->fetch(\PDO::FETCH_NUM))
            {
                // apply the callback first so the key can be modified
                $row = \call_user_func($callable, $row);
                // now retain the data
                $data[$row[0]] = $row[1];
            }
        } else
        {
            $data = $sth->fetchAll(\PDO::FETCH_KEY_PAIR);
        }
        return $data;
    }

    /**
     *
     * Fetches the very first value (i.e., first column of the first row).
     *
     * @param string $statement The SQL statement to prepare and execute.
     *
     * @param array $values Values to bind to the query.
     *
     * @return mixed
     *
     */
    public function fetchValue(string $statement, array $values = [])
    {
        return $this->perform($statement, $values)->fetchColumn(0);
    }

    /**
     *
     * Fetches multiple from the database as an associative array.
     * The first column will be the index
     *
     * @param string $statement The SQL statement to prepare and execute.
     *
     * @param array $values Values to bind to the query.
     *
     * @param int $style a fetch style defaults to PDO::FETCH_COLUMN for single
     *      values, use PDO::FETCH_NAMED when fetching a multiple columns
     *
     * @return array
     *
     */
    public function fetchGroup(string $statement, array $values = [],
                               int $style = \PDO::FETCH_COLUMN): array
    {
        return $this->perform($statement, $values)->fetchAll(\PDO::FETCH_GROUP | $style);
    }
}
