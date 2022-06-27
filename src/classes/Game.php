<?php
/**
 * Class Game
 */
class Game extends Model
{
    public ?int $id = null;

    public function __construct()
    {
        parent::__construct();
        $this->moves = $this->getMoves();
    }

    public function getTable(): string
    {
        return 'games';
    }

    public function insert(array $values): ?int
    {
        $values['id'] = isset($values['id']) ?? null;
        $query = 'INSERT INTO ' . $this->getTable() . ' (`id`) VALUES (:id) ';
        $statement = $this->connection->prepare($query);
        try{
            $statement->execute($values);
        } catch (PDOException $e) {
            return null;
        }
        return $this->connection->lastInsertId();
    }

    public function update(array $values): bool
    {
        $query = 'UPDATE ' . $this->getTable() . ' SET winner = :winner WHERE id = :id ';
        $statement = $this->connection->prepare($query);
        try{
            $statement->execute($values);
        } catch (PDOException $e) {
            return false;
        }
        return true;
    }

    public static function select(int $id): ?Model
    {
        $connection = Database::getInstance()->connection();
        $query = 'SELECT * from ' . (new self())->getTable() . ' WHERE id = :id';
        $statement = $connection->prepare($query);
        $statement->execute(['id' => $id ]);
        $game = $statement->fetchObject(self::class);
        return $game === false ? null : $game;
    }

    public static function hasPlayerWon(int $id, int $player): bool
    {
        $moves = Move::playerMoves($id, $player);
        if (count($moves) == 0) {
            return false;
        }
        $positions = [];
        foreach($moves as $move) {
            $positions[] = $move->position;
        }

        $winningCombinations = [];

        foreach (Move::WINNING_COMBINATIONS as $combination) {
            $winningCombinations[$combination] = 0;
            foreach ($positions as $position) {
                if (str_contains($combination, $position)) {
                    $winningCombinations[$combination]++;
                }
            }
        }

        return in_array(3, $winningCombinations);
    }

    protected function getMoves(): array
    {
        if (!is_null($this->id)) {

        }
        $query = 'SELECT * FROM moves WHERE game_id = :id';
        $statement = $this->connection->prepare($query);
        $statement->execute(['id' => $this->id]);
        return $statement->fetchAll(PDO::FETCH_CLASS, Move::class);
    }
}