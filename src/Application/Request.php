<?php
namespace Craft\Application;

/**
 * #### Request class for handling HTTP requests.
 *
 * This class encapsulates the request data, including method, URI, query parameters,
 * body parameters, headers, and server information.
 */
#region Request
class Request{
    private $requestMethod;
    private $requestUri;
    private $queryParams;
    private $bodyParams;
    private $headers;
    private $server;

    public function __construct() {
        $this->server = $_SERVER;
        $this->requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'CLI';
        $this->requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $this->queryParams = $_GET;
        $this->headers = function_exists('getallheaders') ? getallheaders() : [];

        $this->bodyParams = (stripos($this->headers['Content-Type'] ?? '', 'application/json') !== false) ? json_decode(file_get_contents('php://input'), true) ?? [] : $_POST;
    }

    public function getMethod(): string {
        return $this->requestMethod;
    }

    public function getUri(): string {
        return $this->requestUri;
    }

    public function getQueryParams(): array {
        return $this->queryParams;
    }

    public function getBodyParams(): array {
        return $this->bodyParams;
    }

    public function input(string $key, $default = null) {
        return $this->bodyParams[$key] ?? $this->queryParams[$key] ?? $default;
    }

    public function all(): array {
        return array_merge($this->queryParams, $this->bodyParams);
    }

    public function getHeaders(): array {
        return $this->headers;
    }

    public function getServer(): array {
        return $this->server;
    }
}
#endregion