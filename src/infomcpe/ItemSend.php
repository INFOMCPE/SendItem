<?php


namespace infomcpe;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\utils\Config;
use pocketmine\event\Listener;
use pocketmine\utils\Utils; 
use pocketmine\Server;
use pocketmine\item\Item;
use pocketmine\network\protocol\AddItemEntityPacket;

class ItemSend extends PluginBase implements Listener {
     const Prfix = '§f[§aItemSend§f]§e ';
   public function onEnable(){
            $this->saveDefaultConfig();
               if(!file_exists($this->getDataFolder().'lang.json')){
                   $this->languageInitialization();
               }
               
            $this->session = $this->getServer()->getPluginManager()->getPlugin("SessionAPI");
            if ($this->getServer()->getPluginManager()->getPlugin("PluginDownloader")) {
            $this->getServer()->getScheduler()->scheduleAsyncTask(new CheckVersionTask($this, 326));
            
            if($this->session == NULL){
               if($this->getServer()->getPluginManager()->getPlugin("PluginDownloader")->getDescription()->getVersion() >= '1.4'){
                   $this->getServer()->getPluginManager()->getPlugin("PluginDownloader")->installByID('SessionAPI');
               }
            }
        }
         $this->getServer()->getPluginManager()->registerEvents($this, $this);
   }
   public function onDisable() {
       unlink($this->getDataFolder().'lang.json');
   }
   public function onLogin(PlayerLoginEvent $event){
         $player = $event->getPlayer();
         if($this->session->getSessionData(strtolower($player->getName()), 'displayMessage') != null){
             $player->sendMessage($this->session->getSessionData(strtolower($player->getName()), 'displayMessage'));
              $this->session->createSession(strtolower($player->getName()), 'displayMessage',NULL);
         }
      if($this->session->getSessionData(strtolower($player->getName()), 'additem') != null){
          $player->sendMessage($this->session->getSessionData(strtolower($player->getName()), 'displayMessage'));
             $itemdata = explode(':', $this->session->getSessionData(strtolower($player->getName()), 'additem'));
              $id = $itemdata[0];
              $damage = $itemdata[1];
              $count = $itemdata[2]; 
              $player->getInventory()->addItem(Item::get($id, $damage, $count));
              $this->session->deleteSession(strtolower($player->getName()));
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
                    if($player->getInventory()->canAddItem(Item::get($id, $damage, $message))){
                    if($message <= $count){
                    $this->session->createSession(strtolower($player->getName()), 'getchat', FALSE);
                    $event->setCancelled(TRUE);
                    if($this->getOnline($this->session->getSessionData(strtolower($player->getName()), 'sendto'))){
                    $player->getInventory()->removeItem(Item::get($id, $damage, $message));
                    $this->session->createSession($this->session->getSessionData(strtolower($player->getName()), 'sendto'), 'item', $id.':'.$damage.':'.$message);
                    $this->session->createSession($this->session->getSessionData(strtolower($player->getName()), 'sendto'), 'sendby', strtolower($player->getName()));
                    $this->getServer()->getPlayer($this->session->getSessionData(strtolower($player->getName()), 'sendto'))->sendMessage(ItemSend::Prfix.$this->lang('player').": ".$player->getName().' '.$this->lang("send_you").' '.Item::get($id)->getName()." ". $this->lang('in_count').": ".$message.". ".$this->lang("is_send"));
                    $this->getServer()->getScheduler()->scheduleDelayedTask(new tpTimer($this, $this->session->getSessionData(strtolower($player->getName()), 'sendto')), 20*120);
                    $player->sendMessage(ItemSend::Prfix.$this->lang("success_sendto").$this->session->getSessionData(strtolower($player->getName()), 'sendto'));
                    
                    
                    } else {
                        $player->sendMessage(ItemSend::Prfix.$this->lang("no_online"));
                         $event->setCancelled(TRUE);
                    }
                    } else {
                        $player->sendMessage(ItemSend::Prfix.$this->lang("no_blocks"));
                         $event->setCancelled(TRUE);
                    }
                    }else{
                        $player->sendMessage($this->lang('no_inventory'));
                    }
                }else{
                    $player->sendMessage(ItemSend::Prfix.$this->lang("is_numeric"));
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
             $player->sendMessage(ItemSend::Prfix.$this->lang("item_successfully_received"));
             $event->setCancelled(TRUE);
         } else {
             $player->sendMessage(ItemSend::Prfix.$this->lang("air_transport_forbidden"));
         }
         
             }
     }
	
         public function onCommand(CommandSender $sender, Command $command, $label, array $args){

		switch($command->getName()){
                    case 'itemsend':
                        if(count($args) == 0){
                              $sender->sendMessage($this->lang("all_cmd"));
                            break; 
                        }
                          switch ($args[0]) {
                            case 'send':
                                if($args[1] != NULL){
                                    if($this->getOnline($args[1])){
                                        $this->session->createSession(strtolower($sender->getName()), 'senditem', TRUE);
                                         $this->session->createSession(strtolower($sender->getName()), 'sendto', strtolower($args[1]));
                                        $sender->sendMessage(ItemSend::Prfix.$this->lang("send_successfully"));
                                    } else {
                                        $sender->sendMessage(ItemSend::Prfix.$this->lang("offline_player"));
                                    }
                                } else {
                                    $sender->sendMessage(ItemSend::Prfix.$this->lang("no_player"));
                                }
                                break;
                            case 'accept':
                                if($this->session->getSessionData($sender->getName(), 'item') != NULL){
                                   $itemdata = explode(':', $this->session->getSessionData($sender->getName(), 'item'));
                                   $id = $itemdata[0];
                                   $damage = $itemdata[1];
                                   $count = $itemdata[2]; 
                                   $sender->getInventory()->addItem(Item::get($id, $damage, $count));
                                   $this->session->createSession(strtolower($sender->getName()), 'item', null);
                                   $sender->sendMessage(ItemSend::Prfix.$this->lang('success_taken'));
                                   $this->getServer()->getPlayer($this->session->getSessionData($sender->getName(), 'sendby'))->sendMessage(ItemSend::Prfix.$this->lang("player").":".$sender->getName().$this->lang('received_request'));
                                } else {
                                    $sender->sendMessage(ItemSend::Prfix.$this->lang("no_requests"));
                                }
                                break;
                                case 'deny':
                                     if($this->session->getSessionData($sender->getName(), 'item') != NULL){
                                  $itemdata = explode(':', $this->session->getSessionData($sender->getName(), 'item'));
                                   $id = $itemdata[0];
                                   $damage = $itemdata[1];
                                   $count = $itemdata[2]; 
                                    $this->session->createSession(strtolower($sender->getName()), 'item', null);
                                   $this->getServer()->getPlayer($this->session->getSessionData($sender->getName(), 'sendby'))->getInventory()->addItem(Item::get($id, $damage, $count));
                                    $this->getServer()->getPlayer($this->session->getSessionData($sender->getName(), 'sendby'))->sendMessage(ItemSend::Prfix.$this->lang('player').":".$sender->getName().$this->lang('undo_send'));
                                    $sender->sendMessage(ItemSend::Prfix.$this->lang('success_canceled'));
                                   
                                     } else {
                                   $sender->sendMessage(ItemSend::Prfix.$this->lang("no_requests"));
                                   
                                     }
                                break;
                            case 'setlang':
                                
                                break;
                                 default:
                                
                                $sender->sendMessage(ItemSend::Prfix.$this->lang('no_sub-command'));
                                
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
         public function languageInitialization(){
             switch ($this->getConfig()->get("lang")) {
                 case 'rus':
                    $this->saveResource('rus.json');
                     if(file_exists($this->getDataFolder().'lang.json')){
                         unlink($this->getDataFolder().'lang.json');
                     }
                     rename($this->getDataFolder().'rus.json', $this->getDataFolder().'lang.json');

                     break;
                     case 'eng':
                    $this->saveResource('eng.json');
                     if(file_exists($this->getDataFolder().'lang.json')){
                         unlink($this->getDataFolder().'lang.json');
                     }
                     rename($this->getDataFolder().'eng.json', $this->getDataFolder().'lang.json');

                     break;

                 default:
                     $this->saveResource('rus.json');
                     if(file_exists($this->getDataFolder().'lang.json')){
                         unlink($this->getDataFolder().'lang.json');
                     }
                     rename($this->getDataFolder().'rus.json', $this->getDataFolder().'lang.json');
                     break;
             }
         }

         public function lang($phrase){
        $file = json_decode(file_get_contents($this->getDataFolder()."lang.json"), TRUE);
        return $file["{$phrase}"];
		}
          public function curl_get_contents($url){
  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
  $data = curl_exec($curl);
  curl_close($curl);
  return $data;
          }
}

