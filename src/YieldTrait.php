<?php

namespace Isholao\SqlDb;

/**
 * @author Ishola O <ishola.tolu@outlook.com>
 */
trait YieldTrait
{

    /**
     *
     * Yields rows from the database.
     *
     * @param string $statement The SQL statement to prepare and execute.
     *
     * @param array $values Values to bind to the query.
     *
     * @return \Generator
     *
     */
    public function yieldAll(string $statement, array $values = []): \Generator
    {
        $sth = $this->perform($statement, $values);
        while ($row = $sth->fetch(\PDO::FETCH_ASSOC))
        {
            yield $row;
        }
    }

    /**
     *
     * Yields rows from the database keyed on the first column of each row.
     *
     * @param string $statement The SQL statement to prepare and execute.
     *
     * @param array $values Values to bind to the query.
     *
     * @return \Generator
     *
     */
    public function yieldAssoc(string $statement, array $values = []): \Generator
    {
        $sth = $this->perform($statement, $values);
        while ($row = $sth->fetch(\PDO::FETCH_ASSOC))
        {
            yield \current($row) => $row;
        }
    }

    /**
     *
     * Yields the first column of each row.
     *
     * @param string $statement The SQL statement to prepare and execute.
     *
     * @param array $values Values to bind to the query.
     *
     * @return \Generator
     *
     */
    public function yieldColumn(string $statement, array $values = []): \Generator
    {
        $sth = $this->perform($statement, $values);
        while ($row = $sth->fetch(\PDO::FETCH_NUM))
        {
            yield $row[0];
        }
    }

    /**
     *
     * Yields objects where the column values are mapped to object properties.
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
     * @param string $class_name The name of the class to create from each
     * row.
     *
     * @param array $ctor_args Arguments to pass to each object constructor.
     *
     * @return \Generator
     *
     */
    public function yieldObjects(string $statement, array $values = [],
                                 string $class_name = 'stdClass',
                                 array $ctor_args = [
            ]): \Generator
    {
        $sth = $this->perform($statement, $values);
        if (empty($ctor_args))
        {
            while ($instance = $sth->fetchObject($class_name))
            {
                yield $instance;
            }
        } else
        {
            while ($instance = $sth->fetchObject($class_name, $ctor_args))
            {
                yield $instance;
            }
        }
    }

    /**
     *
     * Yields key-value pairs (first column is the key, second column is the
     * value).
     *
     * @param string $statement The SQL statement to prepare and execute.
     *
     * @param array $values Values to bind to the query.
     *
     * @return \Generator
     *
     */
    public function yieldPairs(string $statement, array $values = []): \Generator
    {
        $sth = $this->perform($statement, $values);
        while ($row = $sth->fetch(\PDO::FETCH_NUM))
        {
            yield $row[0] => $row[1];
        }
    }

}
