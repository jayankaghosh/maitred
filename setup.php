<?php

require_once __DIR__ . '/src/Db.php';

class Setup
{

    public function __construct(
        private string $configPath
    )
    {
    }

    public function run(): void
    {
        $this->validateConfig();
        $this->runDatabaseSetup();
    }

    protected function ask(string $prompt): string
    {
        $value = null;
        while(!$value) {
            $value = readline($prompt. ': ');
        }
        return $value;
    }

    protected function validateConfig(): void
    {
        try {
            $config = @require $this->configPath;
        } catch (\Error) {
            $config = [];
        }

        $dbParams = ['hostname', 'username', 'password', 'database'];
        foreach ($dbParams as $dbParam) {
            if (!isset($config['db'][$dbParam])) {
                $config['db'][$dbParam] = $this->ask('DB ' . ucfirst($dbParam));
            }
        }

        if (!isset($config['serve_all'])) {
            $config['serve_all'] = in_array(strtolower($this->ask('Serve all files [y/n]')), ['y', 'yes']);
        }

        if (!isset($config['file_map'])) {
            $config['file_map'] = [];
        }

        \file_put_contents($this->configPath, "<?php\n\nreturn " . var_export($config, true) . ';');
    }

    protected function runDatabaseSetup(): void
    {
        $statements = [
            'CREATE TABLE IF NOT EXISTS access_log ( 
                id INT AUTO_INCREMENT,
                ip VARCHAR(100) NOT NULL, 
                url VARCHAR(100) NOT NULL, 
                query_params TEXT NOT NULL, 
                server_params TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY(id)
            );'
        ];
        $config = @require $this->configPath;
        $db = new Db(
            $config['db']['hostname'],
            $config['db']['username'],
            $config['db']['password'],
            $config['db']['database']
        );
        $connection = $db->getConnection();
        foreach ($statements as $statement) {
            $connection->query($statement);
        }
    }
}



$setup = new Setup(__DIR__ . '/config.php');
$setup->run();