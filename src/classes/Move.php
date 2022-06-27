<?php
/**
 * Class Move
 */
class Move extends Model
{
    /**
     * Valid positions
     * A1 | A2 | A3
     * ____________
     * B1 | B2 | B3
     * ____________
     * C1 | C2 | C3
    */

    public const MOVE_POSITIONS = [
        'A1', 'A2', 'A3',
        'B1', 'B2', 'B3',
        'C1', 'C2', 'C3',
    ];

    public const WINNING_COMBINATIONS = [
        'A1A2A3',
        'B1B2B3',
        'C1C2C3',
        'A1B1C1',
        'A2B2C2',
        'A3B3C3',
        'A1B2C3',
        'A3B2C1',
    ];

    public function getTable(): string
    {
        return 'moves';
    }

    public function insert(array $values): ?int
    {
        $query = 'INSERT INTO moves (`game_id`, `player`, `position`) VALUES (:game_id, :player, :position)';
        $statement = $this->connection->prepare($query);
        try{
            $statement->execute($values);
        } catch (PDOException $e) {
            echo($e->getMessage());
            return null;
        }
        $move_id = $this->connection->lastInsertId();
        if (Game::hasPlayerWon((int)$values['game_id'], (int)$values['player'])) {
            (new Game())->update(['id' => $values['game_id'], 'winner' => $values['player']]);
        }
        return $move_id;
    }

    public function update(array $values): bool
    {
        return true;
    }

    public static function select(int $id): ?Model
    {
        $connection = Database::getInstance()->connection();
        $query = 'SELECT * from ' . (new self())->getTable() . ' WHERE id = :id';
        $statement = $connection->prepare($query);
        $statement->execute(['id' => $id ]);
        $move = $statement->fetchObject(self::class);
        return $move === false ? null : $move;
    }

    public static function isPositionAvailable(int $game_id, string $position): bool
    {
        $connection = Database::getInstance()->connection();
        $query = 'SELECT position from ' . (new self())->getTable() . ' WHERE game_id = :game_id and position = :position ORDER BY id DESC LIMIT 1';
        $statement = $connection->prepare($query);
        $statement->execute(['game_id' => $game_id, 'position' => $position ]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return empty($result);
    }

    public static function lastPlayer(int $game_id): int
    {
        $connection = Database::getInstance()->connection();
        $query = 'SELECT player from ' . (new self())->getTable() . ' WHERE game_id = :game_id ORDER BY id DESC LIMIT 1';
        $statement = $connection->prepare($query);
        $statement->execute(['game_id' => $game_id ]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return empty($result) ? 2 : $result['player'];
    }

    public static function playerMoves(int $game_id, int $player): array
    {
        $connection = Database::getInstance()->connection();
        $query = 'SELECT * FROM moves WHERE game_id = :game_id AND player = :player';
        $statement = $connection->prepare($query);
        $statement->execute(['game_id' => $game_id, 'player' => $player]);
        return $statement->fetchAll(PDO::FETCH_CLASS, Move::class);
    }
}