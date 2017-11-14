<?php
require('../db.php');

namespace Longman\TelegramBot\Commands\UserCommands;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Entities\PhotoSize;
use Longman\TelegramBot\Request;

class PublishCommand extends UserCommand
{

    protected $name = 'publish';
    protected $need_mysql = true;
    protected $description = 'Publish an advert';
    protected $usage = '/publish';
    protected $version = '1.0.0';
    protected $conversation;
    protected $db = new Db();
    public function execute()
    {
        $message = $this->getMessage();
        $chat    = $message->getChat();
        $user    = $message->getFrom();
        $text    = trim($message->getText(true));
        $chat_id = $chat->getId();
        $user_id = $user->getId();
        //Preparing Response
        $data = [
            'chat_id' => $chat_id,
        ];
        if ($chat->isGroupChat() || $chat->isSuperGroup()) {
            //reply to message id is applied by default
            //Force reply is applied by default so it can work with privacy on
            $data['reply_markup'] = Keyboard::forceReply(['selective' => true]);
        }
        //Conversation start
        $this->conversation = new Conversation($user_id, $chat_id, $this->getName());
        $notes = &$this->conversation->notes;
        !is_array($notes) && $notes = [];
        //cache data from the tracking session if any
        $state = 0;
        if (isset($notes['state'])) {
            $state = $notes['state'];
        }
        $result = Request::emptyResponse();
        //State machine
        //Entrypoint of the machine state if given by the track
        //Every time a step is achieved the track is updated
        switch ($state) {
            case 0:
                if ($text === '') {
                    $notes['state'] = 0;
                    $this->conversation->update();
                    $data['text']         = 'Type description:';
                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                    $result = Request::sendMessage($data);
                    break;
                }
                $notes['description'] = $text;
                $text          = '';
            // no break
            case 1:
                if ($message->getPhoto() === null) {
                    $notes['state'] = 1;
                    $this->conversation->update();
                    $data['text'] = 'Insert picture:';
                    $result = Request::sendMessage($data);
                    break;
                }
                /** @var PhotoSize $photo */
                $photo             = $message->getPhoto()[0];
                $notes['photo_id'] = $photo->getFileId();
            // no break
            case 2:
                $this->conversation->update();
                $out_text = '/Publish result:' . PHP_EOL;
                unset($notes['state']);
                foreach ($notes as $k => $v) {
                    $out_text .= PHP_EOL . ucfirst($k) . ': ' . $v;
                }
                $data['photo']        = $notes['photo_id'];
                $data['reply_markup'] = Keyboard::remove(['selective' => true]);
                $data['caption']      = $out_text;
                $this->conversation->stop();
                $result = Request::sendPhoto($data);

                $description = $this->db->quote($notes['description']);
                $this->db->query(
                    "INSERT INTO `posts` (`chat_id`,`user_id`,`description`,`photo_id`) 
                     VALUES (" . $chat_id . "," . $user_id . "," . $description . "," . $data['photo'] . ")");
                break;
        }
        return $result;
    }
}