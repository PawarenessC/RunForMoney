<?php

namespace pawarenessc\RFM\event;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\Server;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;

use pocketmine\level\Level;
use pocketmine\level\Position;
//use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\HeartParticle;
use pocketmine\level\particle\DestroyBlockParticle;

use pocketmine\block\Block;

use pocketmine\math\Vector3;

use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

use pawarenessc\RF

class PlayerEventListener implements Listener
{
	
		public function __construct($owner)
		{
        	$this->owner = $owner;
    	}
 	
 	
 		public function onJoin(PlayerJoinEvent $event)
 		{	
			$player = $event->getPlayer();
			$name = $player->getName();
			$player->setAllowMovementCheats(true);
			
			$this->owner->type[$name] = 4;
			
			if(!$this->owner->kk->exists($name))
			{
				$this->owner->kk->set($name, 0);
				$this->owner->kk->save();
			}
			
			if(!$this->owner->nige->exists($name))
			{
				$this->owner->nige->set($name, 0);
				$this->owner->nige->save();
			}
			
			if(!$this->owner->kkb->exists($name))
			{
				$this->owner->kkb->set($name, 0);
				$this->owner->kkb->save();
			}
			
			if(!$this->owner->join->exists($name))
			{
				$this->owner->join->set($name, 0);
				$this->owner->join->save();
			}
			if(!$this->owner->runnerc->exists($name))
			{
				$this->owner->runnerc->set($name, 0);
				$this->owner->runnerc->save();
			}
			
			if(!$this->owner->hunterc->exists($name))
			{
					$this->owner->hunterc->set($name, 0);
				$this->owner->hunterc->save();
			}
			
		}
	
		public function onLogin(PlayerLoginEvent $event)
 		{
 			$player = $event->getPlayer();
			$name = $player->getName();
			
			$this->owner->type[$name] = 4;
		}
		
		public function onQuit(PlayerQuitEvent $event)
		{
			$player = $event->getPlayer();
			$name = $player->getName();
			
			if($this->owner->type[$name] == 1)
			{
   				$this->owner->t--;
			}
  			
  			if($this->owner->type[$name] == 2)
  			{
  				$this->owner->h--;
  			}
  		}
  	
  		public function onMove(PlayerMoveEvent $event)
  		{
  			$player = $event->getPlayer();
  			$name = $player->getName();
  			
  			if($this->owner->type[$name] == 2 && $this->owner->game == true) // ハンターだったら
  			{
  				$level = $player->getLevel();
				$pos = new Vector3($player->getX(),$player->getY()+1,$player->getZ());
				
				//$pt = new DustParticle($pos, mt_rand(), mt_rand(), mt_rand(), mt_rand());
				$pt = new HeartParticle($pos);
				$count = 5;
				
				for($i = 0;$i < $count; ++$i)
				{
					$level->addParticle($pt);
				}
			}
		}
		
		public function onBlockTap(PlayerInteractEvent $event)
		{
			$player = $event->getPlayer();
			$name = $player->getName();
			
			$block = $event->getBlock()->getId();
			
			$id = $event->getItem()->getId();
			
			$item = $event->getItem();
			
			$cname = $item->getCustomName();
			
			$h = $this->owner->h;
			$t = $this->owner->t;
			
			
			
			$hb = $this->owner->config->get("RevivalBlockID");
			$jb = $this->owner->config->get("SelfBlockID");
			$kb = $this->owner->config->get("WatchBlockID");
			
			$data  = $this->owner->xyz->getAll()["MAP1"];
			$data2 = $this->owner->xyz->getAll()["MAP2"];
			
			$weapon = $this->owner->weapon->getAll();
			
			$map = $this->owner->map;
			
			If($block == 133 && $this->owner->game == true && $this->owner->type[$name] == 1 && $this->owner->eme == true)
			{
				$money = $this->owner->mis->getAll()["EmeraldMission"]["Reward"];
				
				$player->sendMessage("§l§aMessage>>§r §eミッションクリア！");
				$this->owner->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}§aがエメラルドブロックミッションをクリアしました！");
				$this->owner->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}§fは{$money}円の報酬を手に入れた！");
				
				$this->owner->addMoney($money ,$name);
				$this->owner->eme = false;
			}
		
			if($block == $hb && $this->owner->game == true && $this->owner->type[$name] == 3) // 復活
			{
				$player->sendMessage("§l§aMessage>>§r §eアスレチッククリア！復活しました！");
				$this->owner->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}§aが復活しました！");
				$team = "runner";
				$this->owner->team($player, $team);
				
				if($map == 1)
				{
					$xyz = new Vector3($data["Runner"]["x"], $data["Runner"]["y"], $data["Runner"]["z"], $data["world"]);
        	   		$player->teleport($xyz);
				}
				else
				{
					$xyz = new Vector3($data2["Runner"]["x"], $data2["Runner"]["y"], $data2["Runner"]["z"], $data2["world"]);
        	   		$player->teleport($xyz);
				}
			}
			
			if($block == $jb && $this->owner->game == true && $this->owner->type[$name] == 1)
			{
				$player->sendMessage("§l§aMessage>>§r§d自首成功！");
				$this->owner->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}§aが自首しました！");
				$player->teleport($this->owner->getServer()->getDefaultLevel()->getSafeSpawn());
				$this->owner->t--;
				//$team = "watch";
				//$this->owner->team($player, $team);
				$this->owner->type[$name] = 3;
				
				$this->owner->addMoney($this->owner->win ,$name);
			}	
		
			if($block == $kb)
			{
				if($map == 1)
				{
					$player->sendMessage("§l§aMessage>>§r§b観戦場所へ移動します...");
					$xyz = new Vector3($data["Watch"]["x"], $data["Watch"]["y"], $data["Watch"]["z"], $data["world"]);
        	    	
        	    	$this->owner->type[$name] = 0;
        	    	$player->teleport($xyz);
        	    }
        	    else
        	    {
        	    	$player->sendMessage("§l§aMessage>>§r§b観戦場所へ移動します...");
					$xyz = new Vector3($data2["Watch"]["x"], $data2["Watch"]["y"], $data2["Watch"]["z"], $data2["world"]);
        	    	$player->teleport($xyz);
        	    }
			}
		}
	
		public function onUseItem(PlayerInteractEvent $event)
		{
			$weapon = $this->owner->weapon->getAll();
			$id = $event->getItem()->getId();
			$item = $event->getItem();
			$cname = $item->getCustomName();
			$data  = $this->owner->xyz->getAll()["MAP1"];
			$data2 = $this->owner->xyz->getAll()["MAP2"];
			
			$player = $event->getPlayer();
			$name = $player->getName();
			$map = $this->owner->map;
			
			
			
			if( $id == $weapon["SpeedUp"]["id"] && $cname == $weapon["SpeedUp"]["Name"] && $this->owner->game == true ) //スピードあぷう
			{
				$player->addEffect(new EffectInstance(Effect::getEffect(1), 600, 2, true));
				$player->getInventory()->removeItem($item);
				
				$player->sendMessage("§l§aMessage>>§r §bスピードアップアイテム§fを使った！");
			}
			
			if( $id == $weapon["HighJump"]["id"] && $cname == $weapon["HighJump"]["Name"] && $this->owner->game == true ) //じゃんぷ
			{
				$player->addEffect(new EffectInstance(Effect::getEffect(8), 200, 3, true));
				$player->getInventory()->removeItem($item);
				
				$player->sendMessage("§l§aMessage>>§r §aハイジャンプアイテム§fを使った！");
			}
			
			if( $id == $weapon["Invisible"]["id"] && $cname == $weapon["Invisible"]["Name"] && $this->owner->game == true ) //透明
			{
				$player->addEffect(new EffectInstance(Effect::getEffect(14), 200, 3, true));
				$player->getInventory()->removeItem($item);
				
				$player->sendMessage("§l§aMessage>>§r §7透明アイテム§fを使った！");
				
			}
			
			if( $id == $weapon["Revival"]["id"] && $cname == $weapon["Revival"]["Name"] && $this->owner->type[$name] == 3 && $this->owner->game == true ) //復活
			{
				$player->getInventory()->removeItem($item);
				
				$player->sendMessage("§l§aMessage>>§r §6復活アイテム§fを使った！");
				$player->sendMessage("§l§aMessage>>§r §b君は逃走者だ！");
				
				$this->owner->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}§aが復活しました！");
				
				$this->owner->type[$name] = 1;
				
				if($map == 1)
				{
					$xyz = new Vector3($data["Runner"]["x"], $data["Runner"]["y"], $data["Runner"]["z"], $data["world"]);
        	   			$player->teleport($xyz);
					$this->owner->t++;
				}
				else
				{
					$xyz = new Vector3($data2["Runner"]["x"], $data2["Runner"]["y"], $data2["Runner"]["z"], $data2["world"]);
        	   			$player->teleport($xyz);
					$this->owner->t++;
				}
			}
			
			
		}
	
		public function oncmd(PlayerCommandPreprocessEvent $event)
		{
			$player = $event->getPlayer();
			$cmd = $event->getMessage();
			
			if($cmd !== "/setupui" && $cmd !== "/tagui" && $cmd !== "/tagshop"){
				$event->setCancelled();
				$player->sendMessage("§l§aMessage>>§r §cゲーム中は逃走中コマンド以外を使用できません");
			}
		}
		
		public function EntityDamageEvent(EntityDamageEvent $event)
		{
			$data  = $this->owner->xyz->getAll()["MAP1"];
			$data2 = $this->owner->xyz->getAll()["MAP2"];
			
			$weapon = $this->owner->weapon->getAll();
			
			
			$map = $this->owner->map;
			
			if($event instanceof EntityDamageByEntityEvent)
			{
				$entity = $event->getEntity();
				$player = $event->getDamager();
				
				$item = $player->getInventory()->getItemInHand();
				$cname = $item->getCustomName();
				$id = $item->getId();
				
				
				$hunter = $player->getName();
				$runner = $entity->getName();
				
				if($this->owner->type[$hunter] == 2 && $this->owner->type[$runner] == 1 && $this->owner->game == true)
				{
					if($this->owner->addh !== true)
					{
						$kakuho = $this->owner->config->get("Reward");
						$player->sendMessage("§l§aMessage>>§r §b確保報酬として§6{$kakuho}§b円を手に入れた！");
						$this->owner->addMoney($kakuho ,$hunter);
	  				
						$entity->sendMessage("§l§aMessage>>§r §c{$hunter}§4に確保された...");
	  					$entity->sendMessage("§l§aMessage>>§r §bアスレチックをクリアして復活しよう。");
	  					$entity->addTitle("§c捕まりました...", "");
	  					
	  					if($map == 1)
	  					{
	  						$xyz = new Vector3($data["Jall"]["x"], $data["Jall"]["y"], $data["Jall"]["z"], $data["world"]);
	  						$entity->teleport($xyz);
	  					}
	  					else
	  					{
	  						$xyz = new Vector3($data2["Jall"]["x"], $data2["Jall"]["y"], $data2["Jall"]["z"], $data2["world"]);
	  						$entity->teleport($xyz);
	  					}
	  					
						$this->owner->kk->set($hunter, $this->owner->kk->get($hunter)+1);
	  					$this->owner->kk->save();
	  					
	  					$this->owner->kkb->set($runner, $this->owner->kkb->get($runner)+1);
	  					$this->owner->kkb->save();
	  					
	  					//$team = "jaller"; なんか作動しない
						//$this->owner->team($player, $team);
						$this->owner->type[$runner] = 3;
	  					$this->owner->t--;
	  					$this->owner->getServer()->broadcastMessage("§l§bINFO>>§r §e{$runner}§cが確保された...");
	  					$this->owner->getServer()->broadcastMessage("§l§bINFO>>§r §cハンター→ §f{$hunter}");
	  				}
	  				
	  				else //ハンター増加ミッション
	  				
	  				{
	  					$this->owner->getServer()->broadcastMessage("§l§bINFO>>§r §e{$runner}§cが確保された...");
	  					$this->owner->getServer()->broadcastMessage("§l§bINFO>>§r §cハンター→ §f{$hunter}");
	  					$this->owner->type[$runner] = 3;
	  					$this->owner->t--;
	  					
	  					$this->owner->getServer()->broadcastMessage("§l§bINFO>>§r §4ミッション失敗...");
	  					$this->owner->getServer()->broadcastMessage("§l§bINFO>>§r §4牢屋にいる者が全員ハンターになってしまった！");
	  					
	  					$this->owner->addh = false;
	  					
	  					$players = Server::getInstance()->getOnlinePlayers();
	  					foreach ($players as $online)
	  					{
	  						$name1 = $online->getName();
	  						
	  						if($this->owner->type[$name1] == 3)
	  						{
	  							$online->sendMessage("§l§aMessage>>§r §cハンターになったぞ！");
	  							$this->owner->type[$name1] = 2;
	  							$this->owner->h++;
	  							if($map == 1)
	  							{
	  								$xyz = new Vector3($data["Hunter"]["x"], $data["Hunter"]["y"], $data["Hunter"]["z"], $data["world"]);
	  								$online->teleport($xyz);
	  							}
	  							else
	  							{
	  								$xyz = new Vector3($data2["Hunter"]["x"], $data2["Hunter"]["y"], $data2["Hunter"]["z"], $data2["world"]);
	  								$online->teleport($xyz);
	  							}
	  						}
	  					}
	  				}
	  			}
	  			////////////////////////アイテム///////////////////////
	  			if( $this->owner->type[$hunter] == 2 && $this->owner->type[$runner] == 1 && $this->owner->game == true && $weapon["HunterSpeedDown"]["id"] == $id && $cname == $weapon["HunterSpeedDown"]["Name"] )
	  			{
	  				$go = $weapon["HunterSpeedDown"]["Stoptime"];
					$player->sendMessage("§l§aMessage>>§r §aハンターのスピードがダウンしたぞ！");
	  				$entity->sendMessage("§l§aMessage>>§r §cスピードがダウンしてしまった！");
					$entity->sendMessage("§l§aMessage>>§r §c§l{$go}§r§c秒経つまで動けないぞ！");
	  				$entity->removeEffect(1);
					$entity->setImmobile(true);
					//$this->getScheduler()->scheduleRepeatingTask(new GameTask($this, $this), 20);
					$this->getScheduler()->scheduleDelayedTask(new MiniTask($this, $entity),$go);
	  				$ev->getEntity()->getLevel()->addParticle(new DestroyBlockParticle($event->getEntity(), Block::get(7)));
	  			}
	  			
	  			//////////////////////ミッション///////////////////////
	  			if($this->owner->type[$hunter] == 1 && $this->owner->type[$runner] == 2 && $this->owner->game == true && $this->owner->pac == true) //パックマンミッション
	  			{
	  				$kakuho = $this->owner->mis->getAll()["Pac-ManMission"]["Reward"];
					$player->sendMessage("§l§aMessage>>§r §b確保報酬として§6{$kakuho}§b円を手に入れた！");
					$player->sendMessage("§l§aMessage>>§r §6よくやった！");
					$this->owner->addMoney($kakuho ,$hunter);
	  				
					$entity->sendMessage("§l§aMessage>>§r §c{$hunter}に§6パックマンミッション§4で確保された...");
	  				$entity->addTitle("§c捕まりました...", "");
	  				
	  				if($map == 1)
	  				{
	  					$xyz = new Vector3($data["Jall"]["x"], $data["Jall"]["y"], $data["Jall"]["z"], $data["world"]);
	  					$entity->teleport($xyz);
	  				}
	  				else
	  				{
	  					$xyz = new Vector3($data2["Jall"]["x"], $data2["Jall"]["y"], $data2["Jall"]["z"], $data2["world"]);
	  					$entity->teleport($xyz);
	  				}
	  				
					$this->owner->kk->set($hunter, $this->owner->kk->get($hunter)+1);
	  				$this->owner->kk->save();
	  				
	  				$this->owner->kkb->set($runner, $this->owner->kkb->get($runner)+1);
	  				$this->owner->kkb->save();
	  				
	  				//$team = "jaller"; なんか作動しない
					//$this->owner->team($player, $team);
					$this->owner->type[$runner] = 3;
	  				$this->owner->h--;
	  				$this->owner->getServer()->broadcastMessage("§l§bINFO>>§r §e{$runner}が§6パックマンミッションで確保された！");
	  				$this->owner->getServer()->broadcastMessage("§l§bINFO>>§r §c確保した逃走者→ §f{$hunter}");
	  			}
			}
		}
  
}
