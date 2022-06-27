<?php
/**
 * Class Action
 * Abstract Class for handling http request
 */
abstract class Action
{
    public array $params;

    public function __construct()
    {
        $this->handleRequestJson();
        $this->params = $this->getParams();
    }

    /**
     * Adding support for parameters sent through json
     */
    private function handleRequestJson()
    {
        $request = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() === 0) {
            $_REQUEST = array_merge($_REQUEST, $request);
        }
    }

    abstract protected function getParams(): array;
    abstract protected function validate(array $params = []): array;
    abstract public function run(): Response;
}