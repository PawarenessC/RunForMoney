<?php

namespace pawarenessc\RFM\command;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\Server;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

	class TagCommand
	{
    
		public function _construct(string $pg){
			$this->command = $pg;
		}
		
		public function onCommand(CommandSender $sender, Command $command, string $label, array $args, $main) :bool
		{
			switch($label)
				{
  					case "setupui":
 					if($sender->isOp())
 					{
						$main->startMenu($sender);
					}
					else
					{
						$sender->sendMessage("§4権限がありません");
					}
	
					return true;
					break;
	
					case "tagui":
					if($main->guest == true)
					{
						$main->tagMenu($sender);
						
						return true;
						break;
					}
					else
					{
						$sender->sendMessage("§l§aMessage>>§r §c設定により参加できません");
						
						return true;
						break;
					}
					break;
					
					case "tagshop":
					$buttons[] = [ 
						'text' => "§lハンタースピードダウン", 
						'image' => [ 'type' => 'path', 'data' => "" ]  
						]; //0
						$buttons[] = [ 
						'text' => "§lスピードアップ", 
						'image' => [ 'type' => 'path', 'data' => "" ]  
						]; //1
						$buttons[] = [ 
						'text' => "§lハイジャンプ", 
						'image' => [ 'type' => 'path', 'data' => "" ]  
						]; //2
						$buttons[] = [ 
						'text' => "§l透明", 
						'image' => [ 'type' => 'path', 'data' => "" ]  
						]; //3
						$buttons[] = [ 
						'text' => "§l復活アイテム", 
						'image' => [ 'type' => 'path', 'data' => "" ]  
						]; //4
						$main->sendForm($player,"逃走中","購入したいアイテムを選択してね?\n",$buttons,4000);
        				$main->info[$name] = "form";
						break;
				}
		}
	}
	