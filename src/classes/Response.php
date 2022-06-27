<?php
/**
 * Class Response
 * Class that handles the response to http requests
 */
class Response
{
    public const CONTENT_TYPE_JSON = 'Content-Type: application/json; charset=utf-8';
    protected array $data;
    protected array $headers;

    public function __construct()
    {
        $this->data = [];
        $this->headers = [];
    }

    public function setData(array $data = []): Response
    {
        $this->data = $data;
        return $this;
    }

    public function setHeaders(array $headers = []): Response
    {
        $this->headers = $headers;
        return $this;
    }

    public function finish(): void
    {
        if (empty($this->headers)) {
            $this->headers = [self::CONTENT_TYPE_JSON];
        }

        foreach ($this->headers as $header) {
            header($header);
        }

        if (empty($this->data)) {
            return;
        }

        echo json_encode($this->data);
    }
}