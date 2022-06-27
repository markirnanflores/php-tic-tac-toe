<?php
/**
 * Class GetAction
 * Class that handles GET http requests
 * Returns information of a Game according to the parameter id
 */
class GetAction extends Action
{
    protected function getParams(): array
    {
        return $_REQUEST;
    }

    protected function validate(array $params = []): array
    {
        $errors = [];

        if (!isset($params['id'])) {
            $errors[] = 'Missing parameter id';
        }

        if (isset($params['id']) && !is_numeric($params['id'])) {
            $errors[] = 'Parameter id should be a number';
        }

        return $errors;
    }

    public function run(): Response
    {
        $response = new Response();
        $params = $this->params;
        $errors = $this->validate($params);
        if (!empty($errors)) {
            $response->setData(['errors' => $errors]);
            $response->setHeaders(
                [
                    $_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request',
                    Response::CONTENT_TYPE_JSON
                ]
            );
            return $response;
        }
        $game = Game::select((int) $params['id']);
        if (empty($game)) {
            $response->setHeaders(
                [
                    $_SERVER['SERVER_PROTOCOL'] . ' 404 Not found',
                    Response::CONTENT_TYPE_JSON
                ]
            );
            $response->setData(['errors' => 'Game not found']);
        } else {
            $response->setData($game->toArray());
        }
        
        return $response;
    }
}