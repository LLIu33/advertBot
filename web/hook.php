<?php

require('../vendor/autoload.php');

if(getenv('APP_ENV') === 'development') {
  $dotenv = new Dotenv\Dotenv(__DIR__);
  $dotenv->load();
}

$API_KEY = getenv("BOT_TOKEN");
$USER_ID = getenv("TELEGRAM_ID");
$BOT_NAME = "@postAdvertBot";

$url = parse_url(getenv("DATABASE_URL"));

$mysql_credentials = [
   'host'     => $url["host"],
   'user'     => $url["user"],
   'password' => $url["pass"],
   'database' => substr($url["path"], 1),
];

use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\TelegramLog;

try {
    $telegram = new Telegram($API_KEY, $BOT_NAME);
    $telegram->enableMySQL($mysql_credentials);
    $telegram->addCommandsPath(__DIR__ . "/commands");
    $telegram->enableAdmin((int)$USER_ID);
    TelegramLog::initUpdateLog($BOT_NAME . '_update.log');
    $telegram->handle();
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    var_dump($e);
}