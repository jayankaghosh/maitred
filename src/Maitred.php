<?php

require_once __DIR__ . '/Db.php';

class Maitred
{

    private Db $db;

    public function __construct(
        private array $config,
        private string $fileDirectory,
        private array $serverParams
    )
    {
        $this->db = new Db(
            $this->config['db']['hostname'],
            $this->config['db']['username'],
            $this->config['db']['password'],
            $this->config['db']['database']
        );
    }

    protected function getUrlPath(): string
    {
        return trim(strtok($this->serverParams['REQUEST_URI'] ?? '', '?'), '/');
    }

    protected function getRequestedFilePath($filename): ?string
    {
        $requestedFilePath = $this->fileDirectory . '/' . $filename;
        if (is_file($requestedFilePath) && is_readable($requestedFilePath)) {
            return $requestedFilePath;
        }
        return null;
    }

    protected function serveFile($path, $name, $responseCode): void
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        if ($extension === 'php') {
            require_once $path;
        } else {
            header('Content-type: ' . mime_content_type($path));
            header('Content-Disposition: filename=' . basename($name));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            http_response_code($responseCode);
            readfile($path);
        }
        exit(0);
    }

    protected function serve404(): void
    {
        http_response_code(404);
        exit(0);
    }

    protected function track()
    {
        $data = [
            'ip' => $this->serverParams['REMOTE_ADDR'] ?? '',
            'query_params' => \json_encode($_GET),
            'server_params' => \json_encode($_SERVER)
        ];
        $this->db->insert('access_log', $data);
    }

    public function serve(): void
    {
        try {
            $serveAll = $this->config['serve_all'] ?? false;
            $fileMap = $this->config['file_map'] ?? [];

            $urlPath = $this->getUrlPath();
            $responseCode = 200;
            $track = false;
            if (isset($fileMap[$urlPath])) {
                $fileName = $fileMap[$urlPath]['file'];
                $responseCode = $fileMap[$urlPath]['response_code'] ?? 200;
                $track = $fileMap[$urlPath]['track'] ?? false;
            } elseif ($serveAll) {
                $fileName = $urlPath;
            } else {
                throw new \Exception('File not found to serve');
            }
            $requestedFilePath = $this->getRequestedFilePath($fileName);

            if (!$requestedFilePath) {
                throw new \Exception('File not found');
            }

            if ($track) {
                $this->track();
            }
            $this->serveFile($requestedFilePath, $urlPath, $responseCode);
        } catch (\Exception $exception) {
            $this->serve404();
        }

    }
}