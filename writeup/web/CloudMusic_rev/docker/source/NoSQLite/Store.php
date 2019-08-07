<?php

namespace NoSQLite;

use PDO;
use PDOStatement;

class Store implements \Iterator, \Countable
{
    /**
     * @var PDO
     */
    private $db = null;

    /**
     * @var string
     */
    private $name = null;

    /**
     * @var string
     */
    private $keyColumnName = 'key';

    /**
     * @var string
     */
    private $valueColumnName = 'value';

    /**
     * @var array
     */
    private $data = array();

    /**
     * @var bool
     */
    private $isDataLoadedFromDb = false;

    /**
     * @var PDOStatement
     */
    private $iterator;

    /**
     * Current value during iteration
     * @var array
     */
    private $current = null;

    /**
     * @param PDO $db PDO database instance
     * @param string $name store name
     *
     * @return \NoSQLite\Store
     */
    public function __construct($db, $name)
    {
        $this->db = $db;
        $this->name = $name;
        $this->createTable();
    }

    /**
     * @param string $key key
     *
     * @throws \InvalidArgumentException
     * @return string|null
     */
    public function get($key)
    {
        if (!is_string($key)) {
            throw new \InvalidArgumentException('Expected string as key');
        }

        if (isset($this->data[$key])) {
            return $this->data[$key];
        } elseif (!$this->isDataLoadedFromDb) {
            $stmt = $this->db->prepare(
                'SELECT * FROM ' . $this->name . ' WHERE ' . $this->keyColumnName
                . ' = :key;'
            );
            $stmt->bindParam(':key', $key, \PDO::PARAM_STR);
            $stmt->execute();

            if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
                $this->data[$row[0]] = $row[1];
                return $this->data[$key];
            }
        }

        return null;
    }

    /**
     * Get all values as array with key => value structure
     *
     * @return array
     */
    public function getAll()
    {
        if (!$this->isDataLoadedFromDb) {
            $stmt = $this->db->prepare('SELECT * FROM ' . $this->name);
            $stmt->execute();

            while ($row = $stmt->fetch(\PDO::FETCH_NUM, \PDO::FETCH_ORI_NEXT)) {
                $this->data[$row[0]] = $row[1];
            }
        }

        return $this->data;
    }

    /**
     * @param string $key key
     * @param string $value value
     *
     * @return string value stored
     * @throws \InvalidArgumentException
     */
    public function set($key, $value)
    {
        if (!is_string($key)) {
            throw new \InvalidArgumentException('Expected string as key');
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException('Expected string as value');
        }

        $queryString = 'REPLACE INTO ' . $this->name . ' VALUES (:key, :value);';
        $stmt = $this->db->prepare($queryString);
        $stmt->bindParam(':key', $key, \PDO::PARAM_STR);
        $stmt->bindParam(':value', $value, \PDO::PARAM_STR);
        $stmt->execute();
        $this->data[(string)$key] = $value;

        return $this->data[$key];
    }

    /**
     * @param string $key key
     *
     * @return null
     */
    public function delete($key)
    {
        $stmt = $this->db->prepare(
            'DELETE FROM ' . $this->name . ' WHERE ' . $this->keyColumnName
            . ' = :key;'
        );
        $stmt->bindParam(':key', $key, \PDO::PARAM_STR);
        $stmt->execute();

        unset($this->data[$key]);
    }

    /**
     * Delete all values from store
     *
     * @return null
     */
    public function deleteAll()
    {
        $stmt = $this->db->prepare('DELETE FROM ' . $this->name);
        $stmt->execute();
        $this->data = array();
    }

    /**
     * @return null
     */
    public function rewind()
    {
        $this->iterator = $this->db->query('SELECT * FROM ' . $this->name);
        $this->current = $this->iterator->fetch(\PDO::FETCH_NUM, \PDO::FETCH_ORI_NEXT);
    }

    /**
     * @return null
     */
    public function next()
    {
        $this->current = $this->iterator->fetch(\PDO::FETCH_NUM, \PDO::FETCH_ORI_NEXT);
    }

    /**
     * Check if current position is valid
     *
     * @return bool
     */
    public function valid()
    {
        return $this->current !== false;
    }

    /**
     * @return string|null
     */
    public function current()
    {
        return isset($this->current[1]) ? $this->current[1] : null;
    }

    /**
     * @return string|null
     */
    public function key()
    {
        return isset($this->current[0]) ? $this->current[0] : null;
    }

    /**
     * @return int
     */
    public function count()
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM ' . $this->name)->fetchColumn();
    }

    /**
     * Create storage table in database if not exists
     *
     * @return null
     */
    private function createTable()
    {
        $stmt = 'CREATE TABLE IF NOT EXISTS "' . $this->name;
        $stmt.= '" ("' . $this->keyColumnName . '" TEXT PRIMARY KEY, "';
        $stmt.= $this->valueColumnName . '" TEXT);';
        $this->db->exec($stmt);
    }
}
