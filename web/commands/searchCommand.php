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
        $data = [
            'chat_id' => $chat_id
        ];
        if ($text === '') {
            $data['text'] = "Command usage: " . $this->getUsage();
            return Request::sendMessage($data);
        }

        $pdo = DB::getPdo();
        $sql = $pdo->prepare("SELECT `description`, `photo_id` FROM `posts` WHERE `description` LIKE :keyword");
        $sql->bindValue(':keyword', "%".$text."%");
        $sql->execute();
        $result = $sql->fetchAll();

        $data['text'] = "/search result:";
        Request::sendMessage($data);

        if($sql->rowCount() == 0) {
            $data['text'] = "Sorry, no results were found";
            return Request::sendMessage($data);
        }

        foreach( $result as $row ) {
            $resultData = [
                'chat_id' => $chat_id,
                'photo' => $row['photo_id'],
                'caption'    => $row['description']
            ];
            Request::sendPhoto($resultData);
        }

        return true;
    }
}