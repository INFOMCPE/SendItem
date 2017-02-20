<?php


namespace infomcpe;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\utils\Utils;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\plugin\PluginDescription;

class ItemSend extends PluginBase implements Listener {
     const Prfix = '§f[§aItemSend§f]§e ';
   public function onEnable(){
            $this->session = $this->getServer()->getPluginManager()->getPlugin("SessionAPI");
            $this->getServer()->getPluginManager()->registerEvents($this, $this);
             if ($this->getServer()->getPluginManager()->getPlugin("PluginDownloader")) {
                            $this->getServer()->getScheduler()->scheduleAsyncTask(new CheckVersionTask($this, 326));
                        }
        }
        public function onChat(PlayerChatEvent $event) {
            $player = $event->getPlayer();
            if($this->session->getSessionData(strtolower($player->getName()), 'getchat') == true){
                $message = $event->getMessage();
                if(is_numeric($message)){
                    $itemdata = explode(':', $this->session->getSessionData(strtolower($player->getName()), 'item'));
                    $id = $itemdata[0];
                    $damage = $itemdata[1];
                    $count = $itemdata[2];
                    if($message <= $count){
                    $this->session->createSession(strtolower($player->getName()), 'getchat', FALSE);
                    $event->setCancelled(TRUE);
                    if($this->getOnline($this->session->getSessionData(strtolower($player->getName()), 'sendto'))){
                    $player->getInventory()->removeItem(Item::get($id, $damage, $message));
                    $this->session->createSession($this->session->getSessionData(strtolower($player->getName()), 'sendto'), 'item', $id.':'.$damage.':'.$message);
                    $this->getServer()->getPlayer($this->session->getSessionData(strtolower($player->getName()), 'sendto'))->sendMessage(ItemSend::Prfix."Игрок ".$player->getName().' Отправил вам '.Item::get($id)->getName()." в количестве: ".$message.". \nДля того чтобы получить напишите /is accept. Чтобы отклонить /is deny");
                    
                    $player->sendMessage(ItemSend::Prfix."Успешно. Запрос на получение отправлен игроку ".$this->session->getSessionData(strtolower($player->getName()), 'sendto'));
                    } else {
                        $player->sendMessage(ItemSend::Prfix."Ошибка. Пока вы проводили все ети действия игрок которому вы собирались отправить покинул сервер");
                         $event->setCancelled(TRUE);
                    }
                    } else {
                        $player->sendMessage(ItemSend::Prfix."Ошибка. В вашем инвентаре меньше блоков чем вы указали");
                         $event->setCancelled(TRUE);
                    }
                }else{
                    $player->sendMessage(ItemSend::Prfix."Ошибка. Пожалуйста напишите количество в виде числа");
                     $event->setCancelled(TRUE);
                }
            }
        }   
     public function onPlayerTouch(PlayerInteractEvent $event){
         $player = $event->getPlayer();
         if($this->session->getSessionData($player->getName(), 'senditem') == TRUE){
             if($event->getItem()->getId() != 0){
             $sendto = $this->session->getSessionData($player->getName(), 'sendto'); 
             $this->session->deleteSession($player->getName());
             $this->session->createSession(strtolower($player->getName()), 'item', $event->getItem()->getId().':'.$event->getItem()->getDamage().':'.$event->getItem()->getCount());
             $this->session->createSession(strtolower($player->getName()), 'getchat', true);
             $this->session->createSession(strtolower($player->getName()), 'sendto', $sendto);
             $player->sendMessage(ItemSend::Prfix.'Успешно. Предмет успешно получен теперь напишите в чат количиство которе xотите отправить');
             $event->setCancelled(TRUE);
         } else {
             $player->sendMessage(ItemSend::Prfix."Ошибка. Траспортировка воздуxа запрещена!");
         }
         
             }
     }
	
         public function onCommand(CommandSender $sender, Command $command, $label, array $args){

		switch($command->getName()){
                    case 'itemsend':
                        if(count($args) == 0){
                              $sender->sendMessage("§6/is send [ник] - отправить предмет \n§6/is accept - Получить отправленый предмет \n§6/is deny - Отклонить отправленый придмет");
                            break; 
                        }
                          switch ($args[0]) {
                            case 'send':
                                if($args[1] != NULL){
                                    if($this->getOnline($args[1])){
                                        $this->session->createSession(strtolower($sender->getName()), 'senditem', TRUE);
                                         $this->session->createSession(strtolower($sender->getName()), 'sendto', strtolower($args[1]));
                                        $sender->sendMessage(ItemSend::Prfix."Успешно. Нажмите по земле тем предметом который xотите отправить");
                                    } else {
                                        $sender->sendMessage(ItemSend::Prfix."Ощибка. Игрок которому вы пытаитесь отправить не на сервере ");
                                    }
                                } else {
                                    $sender->sendMessage(ItemSend::Prfix."Ошибка. Вы не написали ник игрока");
                                }
                                break;
                            case 'accpet':
                                if($this->session->getSessionData($sender->getName(), 'item') != NULL){
                                   $itemdata = explode(':', $this->session->getSessionData($sender->getName(), 'item'));
                                   $id = $itemdata[0];
                                   $damage = $itemdata[1];
                                   $count = $itemdata[2]; 
                                   $sender->getInventory()->addItem(Item::get($id, $damage, $count));
                                   $this->session->createSession(strtolower($sender->getName()), 'item', null);
                                   $sender->sendMessage(ItemSend::Prfix.'Запрос успешно принят');
                                } else {
                                    $sender->sendMessage(ItemSend::Prfix."Ошибка. Вам никто не отпрвлял запросов");
                                }
                                break;
                                case 'deny':
                                    $sender->sendMessage(ItemSend::Prfix."Команда будет реализована в следушей версии");
                                break;
                                 default:
                                if($args[0] == null){
                                $sender->sendMessage(ItemSend::Prfix."Суб команда не найдена");
                                }
                                 break;
                          }
                }
         }
                public function getOnline($nickname) {
                   if($player = $this->getServer()->getPlayer($nickname)){
                       return TRUE;
            } else{
                return false;
            }
                   
         }
}

