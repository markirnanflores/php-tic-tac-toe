<?php
/**
 * Class PostAction
 * Class that handles POST http requests
 * Creates a new game and returns the id
 */
class PostAction extends Action
{
    protected function getParams(): array
    {
        return $_REQUEST;
    }

    protected function validate(array $params = []): array
    {
        return [];
    }

    public function run(): Response
    {
        $response = new Response();
        $id = (new Game())->insert([]);
        $game = Game::select($id);
        if (empty($id)) {
            $response->setData(['message' => 'Something went wrong']);
            $response->setHeaders([
                $_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error',
                Response::CONTENT_TYPE_JSON
            ]);
        } else {
            $response->setData(['game' => $game->toArray()]);
        }
        return $response;
    }
}