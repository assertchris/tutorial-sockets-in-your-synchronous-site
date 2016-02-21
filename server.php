<?php

$action = "/index";
$actions = ["/index", "/get"];

if (isset($_SERVER["PATH_INFO"])) {
    if (in_array($_SERVER["PATH_INFO"], $actions)) {
        $action = $_SERVER["PATH_INFO"];
    }
}

$db = new PDO(
    "mysql:host=localhost;dbname=[your_database_name];charset=utf8",
    "[your_database_username]",
    "[your_database_password]"
);

function respond($data)
{
    header("Content-type: application/json");
    print json_encode($data) and exit;
}

function request($method, $endpoint, $parameters = "")
{
    $public = "[your_public_key]";
    $private = "[your_private_key]";

    $auth = base64_encode("{$public}:{$private}");

    $context = stream_context_create([
        "http" => [
            "method" => $method,
            "header" => [
                "Authorization: Basic {$auth}",
                "Accept: application/vnd.socketize.v1+json",
            ]
        ]
    ]);

    $url = "https://socketize.com/api/{$endpoint}?{$parameters}";
    $response = file_get_contents($url, false, $context);

    return json_decode($response, true);
}

if ($action == "/get") {
    $statement = $db->prepare("SELECT * FROM cards");
    $statement->execute();

    $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

    $rows = array_map(function($row) {
        return [
            "id" => (int) $row["id"],
            "name" => (string) $row["name"],
        ];
    }, $rows);

    respond($rows);
}

if ($action == "/index") {
    header("Content-type: text/html");
    print file_get_contents("index.html") and exit;
}
