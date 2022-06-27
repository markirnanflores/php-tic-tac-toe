<?php
/**
 * Class Model
 * Abstract Class
 */
abstract class Model
{
    protected PDO $connection;

    public function __construct()
    {
        $this->connection = Database::getInstance()->connection();
    }

    public function toArray(): array
    {
        $properties = get_object_vars($this);
        unset($properties['connection']);
        return $properties;
    }

    abstract protected function getTable(): string;
    abstract public function insert(array $values): ?int;
    abstract public function update(array $values): bool;
    abstract public static function select(int $id): ?Model;
}