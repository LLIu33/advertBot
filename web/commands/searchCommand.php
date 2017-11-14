<?php
namespace Longman\TelegramBot\Commands\UserCommands;
use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\DB;

class SearchCommand extends UserCommand
{

    protected $name = 'search';
    protected $description = 'Search published advert';
    protected $usage = '/search <text>';
    protected $version = '1.0.0';
    public function execute()
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $text    = trim($message->getText(true));
        if ($text === '') {
            $text = 'Command usage: ' . $this->getUsage();
        }

        $pdo = DB::getPdo();
        $sql = $pdo->prepare("SELECT `description`, `photo_id` FROM `posts` WHERE `description` LIKE :keyword");
        $sql->bindValue(':keyword', "%".$text."%");
        $sql->execute();
        $result = $sql->fetchAll();

        $text    = '/search result:' . PHP_EOL;
        $data = [
            'chat_id' => $chat_id,
            'text'    => $text,
        ];
        Request::sendMessage($data);

        foreach( $result as $row ) {
            $resultData = [
                'chat_id' => $chat_id,
                'photo' => $row['photo_id'],
                'text'    => $row['description']
            ];
            Request::sendPhoto($resultData);
        }

        return true;
    }
}