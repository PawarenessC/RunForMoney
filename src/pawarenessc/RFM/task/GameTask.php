<?php
namespace pawarenessc\RFM\task;

use pocketmine\scheduler\Task;
use pocketmine\scheduler\TaskScheduler;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\Server;

use pawarenessc\RFM\Main;
use pawarenessc\RFM\task\GameTask;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;

use pocketmine\level\Level;
use pocketmine\level\Position;

use pocketmine\math\Vector3;



use pocketmine\utils\Config;

class GameTask extends Task
{
	public function __construct($owner)
	{
		$this->owner = $owner;
	}
	
	public function onRun(int $ticks)
	{
		$owner = $this->owner;
		
		if($owner->game == true && $owner->cogame == true)
		{
			$owner->gametime--;
			
			$t = $owner->t;
			$h = $owner->h;
			$all = $owner->t + $owner->h;
			$data = $owner->xyz->getAll();
			$miss = $owner->mis->getAll();
			$players = $owner->getServer()->getOnlinePlayers();
			$prize = $owner->config->get("UnitPrice");
			$gamemi = $owner->config->get("GameTime");
			$huntermove = $gamemi - 11;
			$truegame = $gamemi - 1;
			$win = $owner->win;
			$owner->win = $win + $prize;
			$min = $owner->gametime;
			
			$map = $owner->map;
			
			$init = $min;
			$minutes = floor(($init / 60) % 60);
			$seconds = $init % 60;
			
			$pacm = $miss["Pac-ManMission"]["time"];
			$emem = $miss["EmeraldMission"]["time"];
			$adhm = $miss["Increases Hunter"]["time"];
			
			$ifp = $miss["Pac-ManMission"]["if"];
			$ife = $miss["EmeraldMission"]["if"];
			$ifa = $miss["Increases Hunter"]["if"];
			
			$limita = $miss["Increases Hunter"]["LimitTime"];
			
			$pacmend = $pacm - 30;
			$addhend = $adhm - $limita;
			
			$owner->Popup("§f残り時間:§l§f{$minutes}§r§b:§r§f§l{$seconds}§r§e \n§r賞金  §d".$win."§b円§r\n     §l§a逃走者 ".$t." §cvs §bハンター ".$h."\n\n\n\n");
			
			/*if($t == 0 or $t < 0)
			{
				$owner->msg("§l§bINFO>>§r §a逃走者が全滅しました！");
				$owner->msg("§l§bINFO>>§r §bハンターの勝利です！");
				$owner->msg("§l§bINFO>>§r §cハンターは§6{$win}§b円の賞金を手にいれた！");
  				$owner->endGame();
			}
			
			if($h == 0 or $h < 0)
			{
				$owner->msg("§l§bINFO>>§r §cハンターが全滅しました！");
				$owner->msg("§l§bINFO>>§r §b逃走者の勝利です！");
				$owner->msg("§l§bINFO>>§r §b逃走者が§6{$win}§b円の賞金を手にいれた！");
  				$owner->endGame();
			}*/
			
			switch($owner->gametime)
			{
				case $truegame:
				/*if(1 >= $all)
				{
					$owner->msg("§l§bINFO>>§r §c逃走中開始には2人以上必要です。");
					$owner->msg("§l§bINFO>>§r §cゲームを終了しました");
  					$owner->game = false;
					$owner->endGame();
					break;
				}
				else
				{*/
					$owner->msg("§l§bINFO>>§r §b逃走中を開始しました！！ハンターは不思議なパーティクルを身に着けているよ！");
					$owner->msg("§l§bINFO>>§r §aハンターは10秒間動けません");
  					$owner->game = true;
  				
  				foreach ($players as $player)
  				{
					$name = $player->getName();
					$player->setNameTag("");
					
					if($owner->type[$name] == 1)
					{
						if($map == 1)
						{
						
							$xyz = new Vector3($data["MAP1"]["Runner"]["x"], $data["MAP1"]["Runner"]["y"], $data["MAP1"]["Runner"]["z"], $data["MAP1"]["world"]);
							$player->teleport($xyz);
							$player->sendMessage("§l§aMessage>> §r§b逃走者になりました!");
						}
						else
						{
							$xyz = new Vector3($data["MAP2"]["Runner"]["x"], $data["MAP2"]["Runner"]["y"], $data["MAP2"]["Runner"]["z"], $data["MAP2"]["world"]);
							$player->teleport($xyz);
							$player->sendMessage("§l§aMessage>> §r§b逃走者になりました!");
						}
					}
	
	     			if ($owner->type[$name] == 2)
	     			{
	      				if($map == 1)
	      				{
	      					$xyz = new Vector3($data["MAP1"]["Hunter"]["x"], $data["MAP1"]["Hunter"]["y"], $data["MAP1"]["Hunter"]["z"], $data["MAP1"]["world"]);
	      					$player->teleport($xyz);
							$player->setImmobile(true);
							$player->addEffect(new EffectInstance(Effect::getEffect(1), 114514, 1, false));
							$player->sendMessage("§l§aMessage>> §r§cハンターになりました!");
						}
						else
						{
							$xyz = new Vector3($data["MAP2"]["Hunter"]["x"], $data["MAP2"]["Hunter"]["y"], $data["MAP2"]["Hunter"]["z"], $data["MAP2"]["world"]);
	      					$player->teleport($xyz);
							$player->setImmobile(true);
							$player->addEffect(new EffectInstance(Effect::getEffect(1), 114514, 1, false));
							$player->sendMessage("§l§aMessage>> §r§cハンターになりました!");
						}
					}
				}
				//}
				break;
			
				case $huntermove:
			
				foreach ($players as $player)
  				{
  					$player->setImmobile(false);
  				}
  			
  				$owner->msg("§l§bINFO>>§r §cハンターが動けるようになりました");
  				break;
  			
  				case $pacm:
  				if($ifp == "true")
  				{
  					$owner->msg("§l§bINFO>>§r §cミッション発生！");
					$owner->msg("=-=-=-=-§6パックマンミッション！§r-=-=-=-=");
					$owner->msg("これから30秒間、逃走者とハンターの立場が逆転する！");
					$owner->msg("逃走者はハンターを捕まえて、数を減らそう！");
  					$owner->pac = true;
  				}
  				break;
  				
  				case $emem:
  				if($ife == "true")
  				{
  					$owner->msg("§l§bINFO>>§r §cミッション発生！");
					$owner->msg("=-=-=-=-§6エメラルドブロックミッション！§r-=-=-=-=");
					$owner->msg("§eバブルくんがどこにエメラルドブロックを置いたか忘れたみたいなの！");
					$owner->msg("§e逃走者はハンターに見つからないようにエメラルドブロックを探そう！");
  					$owner->eme = true;
  				}
  				break;
  				
  				case $adhm:
  				if($ifa == "true")
  				{
  					$owner->msg("§l§bINFO>>§r §cミッション発生！");
					$owner->msg("=-=-=-=-§cハンター増加ミッション！§r-=-=-=-=");
					$owner->msg("§eこれから{$limita}秒間の間、ハンターに捕まるな！");
					$owner->msg("§e1人でも捕まってしまった場合、牢屋にいる者が全員ハンターになってしまう！");
  					$owner->addh = true;
  				}
  				break;
  				
  				case $pacmend:
  				if($ifp == "true")
  				{
  					$owner->msg("§l§bINFO>>§r §cパックマンミッションの立場が通常に戻りました");
  					$owner->msg("§l§bINFO>>§r §cハンターは、逃走者を捕まえにいこう！");
  					$owner->pac = false;
  				}
  				break;
  				
  				case $addhend:
  				if($owner->addh == true)
  				{
  					$owner->msg("§l§bINFO>>§r §cハンター増加ミッションの時間が終わったぞ！");
  					$owner->addh = false;
  				}
  				break;
  				
  				
  				case 1000:
  				case 950:
  				case 900:
  				case 850:
  				case 800:
  				case 750:
  				case 700:
  				case 650:
  				case 600:
  				case 550:
  				case 500:
  				case 450:
  				case 400:
  				case 350:
  				case 300:
  				case 250:
  				case 200:
  				case 150:
  				case 100:
  				case 50:
  				
  				$owner->msg("=-=-=-=-=-§c途中結果発表§a！§f-=-=-=-=-=");
				$owner->msg("残り{$t}人！まだ逃げ切ってる人たち↓");
					
   				foreach ($players as $player)
   				{
					$name = $player->getName();
					
					if($owner->type[$name] == 1)
					{
						$owner->msg("§l§b".$name."");
					}
				}
  				break;
  				
  				case 3:
 				foreach($players as $p)
 				{
 					$p->addTitle("3", "", 20, 20, 20);
 				}
 				break;
	
				case 2:
	 			foreach($players as $p)
	 			{
	 				$p->addTitle("2", "", 20, 20, 20);
	 			}
	 			break;
	 			
	 			case 1:
	 			foreach($players as $p)
	 			{
					$p->addTitle("1", "", 20, 20, 20);
				}
 				break;
 				
 				case 0:
 				
 				 foreach ($players as $player)
 				 {
					$owner->msg("§l§bINFO>>§r §c結果発表！");
 					$owner->msg("§l§bINFO>>§r §bゲームが終了したぞ！生き残ったのは{$t}人、逃げ切った人たち↓");
					
					$player->addTitle("§6Congratulations!", "", 20, 20, 20);
					$name = $player->getName();
					
  					if($owner->type[$name] == 1)
  					{
  						$owner->msg("{$name}");
  						$owner->addMoney($win, $name);
  						$owner->nige->set($name, $owner->nige->get($name)+1);
  						$owner->nige->save();
  					}
  				}
  					$owner->msg("§l§bINFO>>§r §b逃げ切った者達にはには§6{$win}§b円の賞金が手に入るぞ");
  					$owner->endGame();
  			}
  		}
  	}
  		
}
