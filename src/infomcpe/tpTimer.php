<?php

namespace infomcpe;

use pocketmine\scheduler\PluginTask;
use pocketmine\item\Item;

class tpTimer extends PluginTask{
	
	public function __construct($main, $nickname){
            $this->nickname = $nickname;
            $this->main = $main;
            parent::__construct($main);
	}
	
	public function onRun($tick){
		if($this->main->session->getSessionData($this->nickname, 'item') != NULL){
                     $itemdata = explode(':', $this->main->session->getSessionData($this->nickname, 'item'));
                                   $id = $itemdata[0];
                                   $damage = $itemdata[1];
                                   $count = $itemdata[2]; 
                                    
                                    if($this->main->getOnline($this->main->session->getSessionData($this->nickname, 'sendby')) == TRUE && $this->main->getOnline($this->nickname) == TRUE){
                                        
                                        $this->main->session->createSession(strtolower($this->nickname), 'item', null);
                                   $this->main->getServer()->getPlayer($this->main->session->getSessionData($this->nickname, 'sendby'))->getInventory()->addItem(Item::get($id, $damage, $count));
                                    $this->main->getServer()->getPlayer($this->main->session->getSessionData($this->nickname, 'sendby'))->sendMessage(ItemSend::Prfix."Исчерпано время на отправку блоков для: {$this->nickname}, блоки §aуспешно§f возврашины");
                                    $this->main->getServer()->getPlayer($this->nickname)->sendMessage(ItemSend::Prfix."Вы не приняли запрос от {$this->main->session->getSessionData($this->nickname, 'sendby')}, отпрвка отменена");
                                    } elseif ($this->main->getOnline($this->main->session->getSessionData($this->nickname, 'sendby')) == FALSE) {
                                        $this->main->session->createSession(strtolower($this->main->session->getSessionData($this->nickname, 'sendby')), 'displayMessage', ItemSend::Prfix."Пока вас не было игрок {$this->nickname} не принял ваш запрос, придметы возврашины");
                                        $this->main->session->createSession(strtolower($this->main->session->getSessionData($this->nickname, 'sendby')), 'additem', $this->main->session->getSessionData($this->nickname, 'item'));
                                    }
                                    if($this->main->getOnline($this->nickname) == FALSE){
                                        $this->main->session->createSession(strtolower($this->main->session->getSessionData($this->nickname, 'sendby')), 'displayMessage', ItemSend::Prfix."Вы не приняли запрос от {$this->main->session->getSessionData($this->nickname, 'sendby')}, отпрвка отменена");
                                    } elseif ($this->main->getOnline($this->nickname) == TRUE) {
                                        $this->main->getServer()->getPlayer($this->nickname)->sendMessage(ItemSend::Prfix."Вы не приняли запрос от {$this->main->session->getSessionData($this->nickname, 'sendby')}, отпрвка отменена");
                                    
                                    }
                                        
                }
	}
	
}
?>