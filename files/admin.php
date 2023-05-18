<?php
$config = require __DIR__ . '/../config.php';

$token = $config['admin_token'];

$username = $_SERVER['PHP_AUTH_USER'] ?? null;
$password = $_SERVER['PHP_AUTH_PW'] ?? null;

if ($username !== $token || $password !== $token) {
    header('WWW-Authenticate: Basic realm="Jonas Realm"');
    header('HTTP/1.0 401 Unauthorized');
    exit(0);
}

require_once __DIR__ . '/../src/Db.php';

$db = new Db(
    $config['db']['hostname'],
    $config['db']['username'],
    $config['db']['password'],
    $config['db']['database']
);

$rows = $db->getConnection()->query('SELECT * FROM access_log ORDER BY id DESC LIMIT 10')->fetchAll();
?>
<html>
    <head>
        <title>Maitred Admin</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
    </head>
    <body>
        <table class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>IP Address</th>
                    <th>URL</th>
                    <th>Query Params</th>
                    <th>Server Params</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <?php
                        $queryParams = \json_decode($row['query_params'], true);
                        $serverParams = \json_decode($row['server_params'], true);
                    ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['ip'] ?></td>
                        <td><?= $row['url'] ?></td>
                        <td class="overflow-auto">
                            <?php if (!count($queryParams)): ?>
                                <?= 'N/A' ?>
                            <?php else: ?>
                                <?php
                                    $id = 'query-params-' . $row['id'];
                                ?>
                                <a class="btn btn-primary" data-bs-toggle="collapse" href="#<?= $id ?>" role="button" aria-expanded="false" aria-controls="<?= $id ?>">
                                    <span>Query Params</span>
                                </a>
                                <ul id="<?= $id ?>" class="collapse">
                                    <?php foreach ($queryParams as $key => $value): ?>
                                        <li><strong><?= $key ?> -</strong> <?= $value ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </td>
                        <td class="overflow-auto">
                            <?php if (!count($serverParams)): ?>
                                <?= 'N/A' ?>
                            <?php else: ?>
                                <?php
                                    $id = 'server-params-' . $row['id'];
                                ?>
                                <a class="btn btn-primary" data-bs-toggle="collapse" href="#<?= $id ?>" role="button" aria-expanded="false" aria-controls="<?= $id ?>">
                                    <span>Server Params</span>
                                </a>
                                <ul id="<?= $id ?>" class="collapse">
                                    <?php foreach ($serverParams as $key => $value): ?>
                                        <li><strong><?= $key ?> -</strong> <?= $value ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </body>
</html>
