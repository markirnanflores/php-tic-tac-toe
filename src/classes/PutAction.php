<?php
/**
 * Class PutAction
 * Class that handles PUT http requests
 * Creates a new move given game id
 */
class PutAction extends Action
{
    protected function getParams(): array
    {
        if ($this->isRequestJson()) {
            return $_REQUEST;
        }
        $raw_data = file_get_contents('php://input');
        $boundary = substr($raw_data, 0, strpos($raw_data, "\r\n"));
        if(empty($boundary)){
            parse_str($raw_data,$data);
            return $data;
        }
        // Fetch each part
        $parts = array_slice(explode($boundary, $raw_data), 1);
        $data = array();

        foreach ($parts as $part) {
            // If this is the last part, break
            if ($part == "--\r\n") break; 

            // Separate content from headers
            $part = ltrim($part, "\r\n");
            list($raw_headers, $body) = explode("\r\n\r\n", $part, 2);

            // Parse the headers list
            $raw_headers = explode("\r\n", $raw_headers);
            $headers = array();
            foreach ($raw_headers as $header) {
                list($name, $value) = explode(':', $header);
                $headers[strtolower($name)] = ltrim($value, ' '); 
            } 

            // Parse the Content-Disposition to get the field name, etc.
            if (isset($headers['content-disposition'])) {
                $filename = null;
                preg_match(
                    '/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/', 
                    $headers['content-disposition'], 
                    $matches
                );
                list(, $type, $name) = $matches;
                isset($matches[4]) and $filename = $matches[4]; 

                // handle your fields here
                switch ($name) {
                    // this is a file upload
                    case 'userfile':
                        file_put_contents($filename, $body);
                        break;

                    // default for all other files is to populate $data
                    default: 
                        $data[$name] = substr($body, 0, strlen($body) - 2);
                        break;
                } 
            }

        }
        return $data;
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

        if (empty($errors)) {
            $game = Game::select((int) $params['id']);
            if (empty($game)) {
                $errors[] = 'Game not found';
            } else if (!empty($game->winner)) {
                $errors[] = 'Game is finished';
            }
            if (!empty($errors)) return $errors;
        }

        if (!isset($params['player'])) {
            $errors[] = 'Missing parameter player';
        }

        if (isset($params['player']) && !is_numeric($params['player'])) {
            $errors[] = 'Parameter player should be a number';
        }

        if (!isset($params['position'])) {
            $errors[] = 'Missing parameter position';
        }

        if (isset($params['position']) && !is_string($params['position'])) {
            $errors[] = 'Parameter id should be a string';
        }

        if (empty($errors)) {
            if ((int) $params['player'] === Move::lastPlayer((int) $params['id'])) {
                $errors[] = 'Next move is for player ' . ($params['player'] == 1 ? 2 : 1);
            }
        }

        if (empty($errors)) {
            if (!in_array($params['position'], Move::MOVE_POSITIONS)) {
                $errors[] = 'Position ' . $params['position'] . ' is not valid';
            }
        }

        if (empty($errors)) {
            if (!Move::isPositionAvailable($params['id'], $params['position'])) {
                $errors[] = 'Position ' . $params['position'] . ' already taken';
            }
        }

        return $errors;
    }

    public function run(): Response
    {
        $response = new Response;
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
        $game_id = (int) $params['id'];
        $position = $params['position'];
        $player = (int) $params['player'];
        $move_id = (new Move())->insert(
            [
                'game_id' => $game_id,
                'position' => $position,
                'player' => $player
            ]
        );
        if (empty($move_id)) {
            $response->setData(['message' => 'Something went wrong']);
            $response->setHeaders([
                $_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error',
                Response::CONTENT_TYPE_JSON
            ]);
        } else {
            $game = Game::select($game_id);
            $response->setData(['game' => $game->toArray(), 'has_won' => Game::hasPlayerWon($game_id, $player)]);
        }
        return $response;
    }

    private function isRequestJson()
    {
        json_decode(file_get_contents('php://input'), true);
        return json_last_error() === JSON_ERROR_NONE;
    }
}