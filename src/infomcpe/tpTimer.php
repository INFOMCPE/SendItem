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
                                    $this->main->session->createSession(strtolower($this->nickname), 'item', null);
                                   $this->main->getServer()->getPlayer($this->main->session->getSessionData($this->nickname, 'sendby'))->getInventory()->addItem(Item::get($id, $damage, $count));
                                    $this->main->getServer()->getPlayer($this->main->session->getSessionData($this->nickname, 'sendby'))->sendMessage(ItemSend::Prfix."Исчерпано время на отправку блоков для: {$this->nickname}, блоки §aуспешно§f возврашины");
                                    $this->main->getServer()->getPlayer($this->nickname)->sendMessage(ItemSend::Prfix."Вы не приняли запрос отпрвка отменена");
                                   
                }
	}
	
}
?>