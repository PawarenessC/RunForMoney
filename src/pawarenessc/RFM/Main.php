<?php

namespace pawarenessc\RFM;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;


use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\Server;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\level\particle\DustParticle;

use pocketmine\block\Block;

use pocketmine\math\Vector3;

use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

use MixCoinSystem\MixCoinSystem;
use metowa1227\MoneySystemAPI\MoneySystemAPI;

class Main extends pluginBase implements Listener
{
    public function onEnable()
    {
    	$this->getServer()->getPluginManager()->registerEvents($this, $this);
    	
    	$this->getLogger()->info("=========================");
 		$this->getLogger()->info("RunForMoneyを読み込みました");
 		$this->getLogger()->info("制作者: PawarenessC");
 		$this->getLogger()->info("v0.9.4");
 		$this->getLogger()->info("=========================");
 		
    	
    	$this->xyz = new Config(
        $this->getDataFolder() . "xyz.yml", Config::YAML, array(
        "逃走者"=> array(
          "x"=>326,
          "y"=>4,
          "z"=>270,
        ),
        "牢屋"=> array(
          "x"=>305,
          "y"=>5,
          "z"=>331,
        ),
        "観戦"=> array(
          "x"=>255,
          "y"=>4,
          "z"=>255,
        ),
        "ハンター"=> array(
          "x"=>246,
          "y"=>4,
          "z"=>357,
        ),
        "ワールド"=> "world"
        ));
        
        $this->config = new Config($this->getDataFolder()."Setup.yml", Config::YAML,
			[
			"1秒ごとの単価" => 5,
			
			"復活ブロック" => 247,
			
			"自首ブロック" => 121,
			
			"観戦ブロック" => 19,
			
			"始まるまでの時間(秒)" => 120,
			
			"ゲーム時間(秒)" => 420,
			
			"Plugin" => "EconomyAPI",
				
			"確保" => 100,
			
			]);
			
		$this->system = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
		
		$this->h = 0; // 鬼
    	$this->t = 0; // 逃走者
    	$this->all = $this->h + $this->t; // 鬼、逃走者合わせて
    	
    	$this->game = false; //ゲームの状態
    	$this->win = 0; // 賞金
    	$this->cogame = false;
    	$this->guest = true;
    	
    	$this->gametime = $this->config->get("ゲーム時間(秒)");
 		$this->wt = $this->config->get("始まるまでの時間(秒)");
 	}
 	
 	
 	public function onJoin(PlayerJoinEvent $event)
 	{	
		$player = $event->getPlayer();
		$name = $player->getName();
		$player->setAllowMovementCheats(true);
		
		$this->type[$name] = 4;
		
	}
	
	public function onQuit(PlayerQuitEvent $event)
	{
		$player = $event->getPlayer();
		$name = $player->getName();
		
		if($this->type[$name] == 1)
		{
   			$this->t--;
		}
  		
  		if($this->type[$name] == 2)
  		{
  			$this->h--;
  		}
  	}
  	
  	public function onMove(PlayerMoveEvent $event)
  	{
  		$player = $event->getPlayer();
  		$name = $player->getName();
  		
  		if($this->type[$name] == 2 && $this->game == true) // ハンターだったら
  		{
  			$level = $player->getLevel();
			$pos = new Vector3($player->getX(),$player->getY()+1,$player->getZ());
			
			$pt = new DustParticle($pos, mt_rand(), mt_rand(), mt_rand(), mt_rand());
			$count = 5;
			
			for($i = 0;$i < $count; ++$i)
			{
				$level->addParticle($pt);
			}
		}
	}
	
	public function onTouch(PlayerInteractEvent $event)
	{
		$player = $event->getPlayer();
		$name = $player->getName();
		
		$block = $event->getBlock()->getId();
		
		$h = $this->h;
		$t = $this->t;
		
		$hb = $this->config->get("復活ブロック");
		$jb = $this->config->get("自首ブロック");
		$kb = $this->config->get("観戦ブロック");
		
		$data = $this->xyz->getAll();
		
		if($block == $hb && $this->game == true && $this->type[$name] == 3) // 復活
		{
			$player->sendMessage("§l§aMessage>>§r §eアスレチッククリア！復活しました！");
			$this->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}§aが復活しました！");
			$team = "runner";
			$this->team($player, $team);
			
			$xyz = new Vector3($data["逃走者"]["x"], $data["逃走者"]["y"], $data["逃走者"]["z"], $data["ワールド"]);
           	$player->teleport($xyz);
			$this->t++;
		}
		
		if($block == $jb && $this->game == true && $this->type[$name] == 1)
		{
			$player->sendMessage("§l§aMessage>>§r§d自首成功！");
			$this->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}§aが自首しました！");
			$player->teleport($this->getServer()->getDefaultLevel()->getSafeSpawn());
			$this->t--;
			$team = "watch";
			$this->team($player, $team);
		}
		
		if($block == $kb)
		{
			$player->sendMessage("§l§aMessage>>§r§b観戦場所へ移動します...");
			$xyz = new Vector3($data["観戦"]["x"], $data["観戦"]["y"], $data["観戦"]["z"], $data["ワールド"]);
            $player->teleport($xyz);
		}
	}
	
	public function EntityDamageEvent(EntityDamageEvent $event)
	{
		$data = $this->xyz->getAll();
		
		if($event instanceof EntityDamageByEntityEvent)
		{
			$entity = $event->getEntity();
			$player = $event->getDamager();
			
			$hunter = $player->getName();
			$runner = $entity->getName();
			
			if($this->type[$hunter] == 2 && $this->type[$runner] == 1 && $this->game == true)
			{
				$kakuho = $this->config->get("確保");
				$player->sendMessage("§l§aMessage>>§r §b確保報酬として§6{$kakuho}§b円を手に入れた！");
				$this->addMoney($kakuho ,$hunter);
	  			
				$entity->sendMessage("§l§aMessage>>§r §c{$hunter}§4に確保された...");
	  			$entity->sendMessage("§l§aMessage>>§r §bアスレチックをクリアして復活しよう。");
	  			$entity->addTitle("§c捕まりました...", "");
	  			$xyz = new Vector3($data["牢屋"]["x"], $data["牢屋"]["y"], $data["牢屋"]["z"], $data["ワールド"]);
            	$entity->teleport($xyz);
            	$team = "jaller";
				$this->team($player, $team);
	  			$this->t--;
	  			$this->getServer()->broadcastMessage("§l§bINFO>>§r {$runner}§cが確保された...");
	  			$this->getServer()->broadcastMessage("§l§bINFO>>§r §cハンター→ §f{$hunter}");
	  		}
		}
	}
	
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) :bool
	{
		switch($label)
			{
  				case "setupui":
 				if($sender->isOp())
 				{
					$this->startMenu($sender);
				}
				else
				{
					$sender->sendMessage("§4権限がありません");
				}

				return true;
				break;

				case "tag":
				if($this->guest == true)
				{
					$this->tagMenu($sender);
				}
				else
				{
					$sender->sendMessage("§c設定により参加できません");
				}
				return true;
				break;
			}
	}
	
	public function startscheduler()
	{
		if($this->game == false)
		{
			$this->wt--;
			$all = $this->t + $this->h;
			$this->getServer()->broadcastPopup("§l{$this->wt}§r§a秒後に開始 §r§f§l{$all}§r§e人が参加");
			if($this->wt == 0)
			{
				$this->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this, "scheduler"]), 20);
				$this->game = true;
			}
		}
	}
	
	public function scheduler()
	{
		
		$this->gametime--;
		
		$t = $this->t;
		$h = $this->h;
		$data = $this->xyz->getAll();
		$players = Server::getInstance()->getOnlinePlayers();
		$prize = $this->config->get("1秒ごとの単価");
		$gamemi = $this->config->get("ゲーム時間(秒)");
		$huntermove = $gamemi - 11;
		$truegame = $gamemi - 1;
		$win = $this->win;
		$this->win = $win + $prize;
		
		$this->getServer()->broadcastPopup("§f残り時間:§l§f{$this->gametime}§r§e秒 \n§r賞金  §d".$win."§b円§r\n     §l§a逃走者 ".$t." §cvs §bハンター ".$h."\n\n\n\n");
		
		if($t == 0 or $t < 0)
		{
			$this->getServer()->broadcastMessage("§l§bINFO>>§r §a逃走者が全滅しました！");
			$this->getServer()->broadcastMessage("§l§bINFO>>§r §bハンターの勝利です！");
			$this->getServer()->broadcastMessage("§l§bINFO>>§r §cハンターは§6{$win}§b円の賞金を手にいれた！");
  			$this->endGame();
		}
		
		switch($this->gametime)
		{
			case $truegame:
			
			$this->getServer()->broadcastMessage("§l§bINFO>>§r §b逃走中を開始しました！！ハンターは不思議なパーティクルを身に着けているよ！");
			$this->getServer()->broadcastMessage("§l§bINFO>>§r §aハンターは10秒間動けません");
  			$this->game = true;
  			
  			foreach ($players as $player)
  			{
				$name = $player->getName();
				$player->setNameTag("");
				
				if($this->type[$name] == 1)
				{
					$xyz = new Vector3($data["逃走者"]["x"], $data["逃走者"]["y"], $data["逃走者"]["z"], $data["ワールド"]);
					$player->teleport($xyz);
					$player->sendMessage("§l§aMessage>> §r§b逃走者になりました!");
				
				}

     			if ($this->type[$name] == 2)
     			{
      				$xyz = new Vector3($data["ハンター"]["x"], $data["ハンター"]["y"], $data["ハンター"]["z"], $data["ワールド"]);
      				$player->teleport($xyz);
					$player->setImmobile(true);
					$player->addEffect(new EffectInstance(Effect::getEffect(1), 114514, 1, false));
					$player->sendMessage("§l§aMessage>> §r§cハンターになりました!");
					
					
				}
			}
			break;
			
			case $huntermove:
			
			foreach ($players as $player)
  			{
  				$player->setImmobile(false);
  			}
  			
  			$this->getServer()->broadcastMessage("§l§bINFO>>§r §cハンターが動けるようになりました");
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
  			
  			$this->getServer()->broadcastMessage("=-=-=-=-=-§c途中結果発表§a！§f-=-=-=-=-=");
			$this->getServer()->broadcastMessage("残り{$t}人！まだ逃げ切ってる人たち↓");

   			foreach ($players as $player)
   			{
				$name = $player->getName();
				
				if($this->type[$name] == 1)
				{
					$this->getServer()->broadcastMessage("§l§b".$name."");
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
 			$this->getServer()->broadcastMessage("§l§bINFO>>§r §c結果発表！");
 			$this->getServer()->broadcastMessage("§l§bINFO>>§r §bゲームが終了したぞ！生き残ったのは{$t}人、逃げ切った人たち↓");
 			 foreach ($players as $player)
 			 {
				$player->addTitle("§6Congratulations!", "", 20, 20, 20);
				$name = $player->getName();
				
  				if($this->type[$name] == 1)
  				{
  					$this->getServer()->broadcastMessage("{$name}");
  					$this->addMoney($win, $name);
  				}
  			}
  				$this->getServer()->broadcastMessage("§l§bINFO>>§r §b逃げ切った者達にはには§6{$win}§b円の賞金が手に入るぞ");
  				$this->endGame();
  		}
  	}
  	
  	public function ReloadGame()
  	{
  		$this->h = 0; // 鬼
    	$this->t = 0; // 逃走者
    	$this->all = $this->h + $this->t; // 鬼、逃走者合わせて
    	$this->game = false; //ゲームの状態
    	$this->win = 0; // 賞金
    	$this->gametime = $this->config->get("ゲーム時間(秒)");
 		$this->wt = $this->config->get("始まるまでの時間(秒)");
 		$this->cogame = false;
 	}
  	
  	public function endGame()
  	{
  		$this->h = 0; // 鬼
    	$this->t = 0; // 逃走者
    	$this->all = $this->h + $this->t; // 鬼、逃走者合わせて
    	$this->game = false; //ゲームの状態
    	$this->win = 0; // 賞金
    	$this->gametime = $this->config->get("ゲーム時間(秒)");
 		$this->wt = $this->config->get("始まるまでの時間(秒)");
 		$this->cogame = false;
 		
 		$scheduler = $this->getScheduler();
 		$scheduler->cancelAllTasks();
 		
 		foreach(Server::getInstance()->getOnlinePlayers() as $player)
 		{
 			$level = $this->getServer()->getDefaultLevel();
			$name = $player->getName();
			$player->setImmobile(false);
			
			if($this->type[$name] == 1 or $this->type[$name] == 2 or $this->type[$name] == 3)
			{
   				$player->teleport($level->getSafeSpawn());
   				$player->setGamemode(0);
   				$player->setNameTag($player->getDisplayName());
   			}
   		}
   	}
   	
   	public function team($player, $team)
   	{
		$name = $player->getName();
  	
  		if($team == "runner")
  		{
			$this->type[$name] = 1;
			$t = $this->t;
			$this->t++;
		}
  		
  		if($team == "hunter")
  		{
  			$this->type[$name] = 2;
  			$h = $this->h;
  			$this->h++;
  		}
  		
  		if($team == "jailer")
  		{
  			$this->type[$name] = 3;
		}
  
  		if($team == "watch")
  		{
  			$this->type[$name] = 3;
  		}
  	}
  	
  	public function addMoney($money, $name)
  	{
 		$plugin = $this->config->get("Plugin");
 		
 		if($plugin == "EconomyAPI")
 		{
 	  		$this->system->addmoney($name ,$money);
 		}
 		
 		if($plugin == "MixCoinSystem")
 		{
 	 		MixCoinSystem::getInstance()->PlusCoin($name,$Coin);
 		}
 		
 		if($plugin == "MoneySystem")
 		{
 			MoneySystemAPI::getInstance()->AddMoneyByName($name, $money);
 		}
 		
 		if($plugin == "MoneyPlugin")
 		{
 			$this->getServer()->getPluginManager()->getPlugin("MoneyPlugin")->addmoney($name,$money);
 		}
 	}
 	
 	public function startMenu($player) 
   {
		$tanka = $this->config->get("1秒ごとの単価");
		$wti   = $this->config->get("始まるまでの時間(秒)");
		$gti   = $this->config->get("ゲーム時間(秒)");
        $name = $player->getName();
        $buttons[] = [ 
        'text' => "§l§6逃走中を始める", 
        'image' => [ 'type' => 'path', 'data' => "" ] 
        ]; //0
        $buttons[] = [ 
        'text' => "§l§4逃走中を終了する", 
        'image' => [ 'type' => 'path', 'data' => "" ] 
        ]; //1
        $buttons[] = [ 
        'text' => "§l§cプラグインを止める", 
        'image' => [ 'type' => 'path', 'data' => "" ] 
        ]; //2
        $buttons[] = [ 
        'text' => "§l§3逃走中の設定", 
        'image' => [ 'type' => 'path', 'data' => "" ] 
        ]; //3
        $buttons[] = [ 
        'text' => "§l§d逃走中参加の設定", 
        'image' => [ 'type' => 'path', 'data' => "" ] 
        ]; //4
        $buttons[] = [
        "text" => "§l§2座標の設定",
        "image" => [ "type" => "path", "data" => "" ] 
        ]; //5
        $buttons[] = [ 
        'text' => "§l§6時間を足す §7[BETA]", 
        'image' => [ 'type' => 'path', 'data' => "" ] 
        ]; //6
        $buttons[] = [ 
        'text' => "§l§b時間を減らす §7[BETA]", 
        'image' => [ 'type' => 'path', 'data' => "" ] 
        ]; //7
        $buttons[] = [ 
        'text' => "§l設定の更新", 
        'image' => [ 'type' => 'path', 'data' => "" ] 
        ]; //8
        $this->sendForm($player,"SETUP","逃走中のセットアップのFormです\n\n\n§6--現在の設定--\n単価:§b{$tanka}円\n§f待機時間:§e{$wti}秒\n§fゲーム時間:§d{$gti}秒\n\n\n",$buttons,2);
        $this->info[$name] = "form";
  }
  
  public function tagMenu($player)
  {
        $name = $player->getName();
        $buttons[] = [ 
        'text' => "§l§b逃走中に参加する", 
        'image' => [ 'type' => 'path', 'data' => "" ] 
        ]; //0
        $buttons[] = [ 
        'text' => "§l§e逃走中から抜ける", 
        'image' => [ 'type' => 'path', 'data' => "" ] 
        ]; //1
        $this->sendForm($player,"TagGame","§a選択してください",$buttons,1145145);
        $this->info[$name] = "form";
  }
  
  public function onPrecessing(DataPacketReceiveEvent $event)
  {
  	$player = $event->getPlayer();
  	$pk = $event->getPacket();
  	$name = $player->getName();
  	
  	if($pk->getName() == "ModalFormResponsePacket")
  	{
  		$data = $pk->formData;
  		$result = json_decode($data);
  		
  		if($data !== "null\n")
  		{
  			 switch($pk->formId)
  			 {
  			 	case 2:
  			 	if($data == 0) // 開始
  			 	{
  			 		if($this->game == false && $this->cogame == false)
  			 		{
  			 			$this->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this, "startscheduler"]), 20);
  			 			$this->game = false;
  			 			$this->cogame = true;
       					$this->getServer()->broadcastMessage("§l§bINFO>>§r §b逃走中を開催します！ /tagで参加しましょう！");
       						
       				}
       				else
       				{
       					$player->sendMessage("§l§aMessage>>§r §c既に開催されています");
       				}
       				break;
       			
       			}
       			elseif($data == 1) // 終了(始まってなかろうが強制終了)
       			{
       				$this->endGame();
       				$this->getServer()->broadcastMessage("§l§bINFO>>§r §c権限者によって逃走中が終了しました");
       			break;
       			
       			}
       			elseif($data == 2) // 無効化
       			{
       				$buttons[] = [ 
       				'text' => "はい", 
       				]; //0
       				
       				$buttons[] = [ 
       				'text' => "いいえ", 
       				];
       				$this->sendForm($player,"DisablePlugin","本当にプラグインを停止しますか？\n\n",$buttons,19198101);
				break;
				
				}
				elseif($data == 3) // 設定
				{
					$tanka = $this->config->get("1秒ごとの単価");
					$wti   = $this->config->get("始まるまでの時間(秒)");
					$gti   = $this->config->get("ゲーム時間(秒)");
        $data = [
				"type" => "custom_form",
				"title" => "単価を変更する",
				"content" => [
					[
						"type" => "label",
						"text" => "§c単価以外はゲーム中変更しないでください"
					],
					[
						"type" => "input",
						"text" => "単価の現在の設定:{$tanka}§b円",
						"placeholder" => "数字を入力してください",
						"default" => ""
					],
					[
						"type" => "input",
						"text" => "待機時間の現在の設定:{$wti}§b秒",
						"placeholder" => "",
						"default" => ""
					],
					[
						"type" => "input",
						"text" => "ゲーム時間の現在の設定:{$gti}§b秒",
						"placeholder" => "4",
						"default" => ""
					]
				]
			];
			$this->createWindow($player, $data, 6381961);
        break;
        
        	}
        	elseif($data == 4) // 参加の設定
        	{
        		if($this->guest == true)
        		{
		  			$setup = "true";
		  		}
		  		else
		  		{
		  			$setup = "false";
		  		}
         		$buttons[] = [ 
        		'text' => "§l§b参加可能にする", 
        		'image' => [ 'type' => 'path', 'data' => "" ] 
        		]; //0
        		$buttons[] = [ 
        		'text' => "§l§c参加禁止にする", 
        		'image' => [ 'type' => 'path', 'data' => "" ] 
        		]; //1
        		$this->sendForm($player,"逃走中参加の設定","true = 参加可能\nfalse = 参加不可能\n\n現在の設定 {$setup}",$buttons,436271);
        		$this->info[$name] = "form";
				break;
			
			}
			elseif($data == 5)
			{
				$buttons[] = [ 
				'text' => "§l§1逃走者のテレポ地点", 
				'image' => [ 'type' => 'path', 'data' => "" ]  
				]; //0
				$buttons[] = [ 
				'text' => "§l§2ハンターのテレポ地点", 
				'image' => [ 'type' => 'path', 'data' => "" ]  
				]; //1
				$buttons[] = [ 
				'text' => "§l§3牢屋のテレポ地点", 
				'image' => [ 'type' => 'path', 'data' => "" ]  
				]; //2
				$buttons[] = [ 
				'text' => "§l§1観戦のテレポ地点", 
				'image' => [ 'type' => 'path', 'data' => "" ]  
				]; //3
				$this->sendForm($player,"逃走中座標の設定","指定したい設定ボタンを押すと現在の座標が登録されます。\n",$buttons,4091);
        		$this->info[$name] = "form";
				break;
			
			}
			elseif($data == 6)
			{
				$data = [
				"type" => "custom_form",
				"title" => "時間を足す",
				"content" => [
					[
						"type" => "label",
						"text" => "追加したい時間を入力してください\n§c本来のゲーム時間を過ぎない程度に設定してください"
					],
					[
						"type" => "input",
						"text" => "§b秒数",
						"placeholder" => "",
						"default" => ""
					]
				]
			];
			$this->createWindow($player, $data, 77817);
			break;
			
			}
			elseif($data == 7)
			{
				$data = [
				"type" => "custom_form",
				"title" => "時間を減らす",
				"content" => [
					[
						"type" => "label",
						"text" => "減らしたい時間を入力してください"
					],
					[
						"type" => "input",
						"text" => "§b秒数(§40未満にならないようにお願いします！§f)",
						"placeholder" => "数字のみ入力してください",
						"default" => ""
					]
				]
			];
			$this->createWindow($player, $data, 413180);
			break;
			}
			elseif($data == 8)
			{
				if($this->game == false)
				{
					$this->ReloadGame();
					$player->sendMessage("§l§aMessage>>§r 設定を更新しました");
					break;
				}
				else
				{
					$player->sendMessage("§l§aMessage>>§r §cゲーム中です、ゲームが終わってから更新ボタンを押してください");
					break;
				}
			}
			
				case 77817: //時間足すやつ
				$addtime = $result[1];
					if($result[1] === "")
					{
						$player->sendMessage("§l§aMessage>>§r §c時間が記入されていません。");
						return true;

					}
					else
					{

						$this->gametime = $this->gametime + $addtime;
						$player->sendMessage("§l§aMessage>>§r §6".$addtime."秒追加しました");
						return true;
					}
				
				case 19198101:
				if($data == 0)
				{//無効化 はい
					$this->getServer()->getPluginManager()->disablePlugin($this);
					$player->sendMessage("§l§aMessage>>§r §a逃走中プラグインを無効化しました、再起動又はリロードをすれば再読み込みします");
       				break;
       			}
       			else
       			{
       				$this->startMenu($player);
       				break;
       			}
       			
       			break;
       			
       			case 6381961://単価を変更
       			
       			$tanka = $result[1];
       			$wtime = $result[2];
       			$gtime = $result[3];
					if($result[1] === "")
					{
						$player->sendMessage("§l§aMessage>>§r §c単価が記入されていません。");
						return true;

					}
					else
					{

						$this->config->set("1秒ごとの単価", $tanka);
						$this->config->save();
						$player->sendMessage("§a逃走中の単価を§d".$tanka."§aに更新しました");
					}
					
					if($this->game == false)
					{
						if ($result[2] === "")
						{
							$player->sendMessage("§l§aMessage>>§r 記入してないのでデフォルトをセットします (待機時間)");
							$this->config->set("始まるまでの時間(秒)", 120);
							$this->config->save();
						}
						else
						{
							$this->config->set("始まるまでの時間(秒)", $wtime);
							$this->config->save();
							$player->sendMessage("§l§aMessage>>§r §a逃走中の待機時間を§b".$wtime."§a秒に更新しました");
						}
					
						if($result[3] === "")
						{
							$player->sendMessage("§l§aMessage>>§r 記入してないのでデフォルトをセットします (ゲーム時間)");
							$this->config->set("ゲーム時間(秒)", 420);
							$this->config->save();
						}
						else
						{
							$this->config->set("ゲーム時間(秒)", $gtime);
							$this->config->save();
							$player->sendMessage("§l§aMessage>>§r §a逃走中のゲーム時間を§e".$gtime."§a秒に更新しました");
							break;
						}
					}
					else
					{
						$player->sendMessage("§l§bINFO>>§r §cゲーム中なので変更はできません (ゲーム時間) (待機時間)");
						break;
					}
				
				//疲れた、↓からはインデントの意識0。また今度するから許して
				
				case 436271:
				if($data == 0)
				{
					$this->guest = true;
					$player->sendMessage("§l§aMessage>>§r §a参加可能にしました");
					break;
				}
				else
				{
					$this->guest = false;
					$player->sendMessage("§l§aMessage>>§r §c参加不可能にしました");
					break;
				}
				break;
				
				case 413180:
				$cuttime = $result[1];
					if($result[1] === "")
					{
						$player->sendMessage("§l§aMessage>>§r §c時間が記入されていません。");
						return true;
					}
					else
					{

						$this->gametime = $this->gametime - $cuttime;
						$player->sendMessage("§l§aMessage>>§r §a逃走中の時間から".$cuttime."秒引きました");
						break;
					}
				break;
				
				
				case 4091:
				$x = $player->x;
				$y = $player->y;
				$z = $player->z;
				
				$level = $player->getLevel();
				$level_name = $level->getName();
				if($data == 0) // 逃走者
				{
					$player->sendMessage("§l§aMessage>>§r 逃走者の座標を更新しました。\nX{$x}\nY{$y}\nZ{$z}");
					
					$data = $this->xyz->get("逃走者");
					$data["x"] = $x;
					$data["y"] = $y;
					$data["z"] = $z;
					
					$this->xyz->set("逃走者", $data);
					$this->xyz->save();
					
					$this->xyz->set("ワールド", $level_name);
					$this->xyz->save();
					break;
				}	
				elseif($data == 1) // ハンター
				{
					$player->sendMessage("§l§aMessage>>§r ハンターの座標を更新しました。\nX{$x}\nY{$y}\nZ{$z}");
					
					$data = $this->xyz->get("ハンター");
					$data["x"] = $x;
					$data["y"] = $y;
					$data["z"] = $z;
					
					$this->xyz->set("ハンター", $data);
					$this->xyz->save();
					break;
				}
				elseif($data == 2) // 牢屋
				{
					$player->sendMessage("§l§aMessage>>§r 牢屋の座標を更新しました。\nX{$x}\nY{$y}\nZ{$z}");
					
					$data = $this->xyz->get("牢屋");
					$data["x"] = $x;
					$data["y"] = $y;
					$data["z"] = $z;
					
					$this->xyz->set("牢屋", $data);
					$this->xyz->save();
					break;
				}
				elseif($data == 3) // 観戦
				{
					$player->sendMessage("§l§aMessage>>§r 観戦の座標を更新しました。\nX{$x}\nY{$y}\nZ{$z}");
					
					$data = $this->xyz->get("観戦");
					$data["x"] = $x;
					$data["y"] = $y;
					$data["z"] = $z;
					
					$this->xyz->set("観戦", $data);
					$this->xyz->save();
					break;
				}
				break;
				
				/* プレイヤー用 */
				
				case 1145145://プレイヤー用
				if($data == 0)
				{
			 		$H = $this->h;
					$T = $this->t;
					$all = $H + $T;
    
    				$player->setNameTag("");
					$name = $player->getName();

     				if($this->type[$name] == 1)
     				{
						$player->sendMessage("§l§aMessage>>§r §c既に参加しています");
					
					}
					elseif($this->type[$name] == 2)
					{
      					$player->sendMessage("§l§aMessage>>§r §c既に参加しています");
     				}
     				elseif($this->type[$name] == 3)
     				{
						$player->sendMessage("§l§aMessage>>§r §c既に参加しています");
					}
					else
					{
  						if($this->game == false)
  						{
  	 						if($this->cogame == false)
  	 						{
  	  							if($all == 0)
  	  							{
  	   								$this->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this, "startscheduler"]), 20);
  									$this->game = false;
  									$this->cogame = true;
									$this->getServer()->broadcastMessage("§l§bINFO>>§r §b逃走中を開催します！ /tagで参加しましょう！");
									
									$team = "runner";
									$this->team($player, $team);
      								
      								$this->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}さんが逃走中に参加しました");
								}
							}
							else
							{
								if($H < 10)
								{
									if($H >= $T / 3)
									{
										$team = "runner";
										$this->team($player, $team);
										$this->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}さんが逃走中に参加しました");
										
										}
										elseif($H < $T)
										{
											$team = "hunter";
											$this->team($player, $team);
											$this->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}さんが逃走中に参加しました");
											
										}
										elseif($H === $T)
										{
											$team = 'runner';
											$this->team($player, $team);
											$this->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}さんが逃走中に参加しました");
										}
								}
								else
								{
									$team = 'runner';
									$this->team($player, $team);
									$this->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}さんが逃走中に参加しました");
								}
							}
						}
					}
  	
  					if($this->game === true)
  					{
						$player->sendMessage("§l§aMessage>>§r §r§b現在試合中です、途中参加するか、観戦をしてお楽しみください。");
						$player->addTitle("§cError", "試合中", 20, 20, 20);
					}
					break;
   
    				}
    				elseif($data == 1)
    				{
    					$name = $player->getName();
 	       				$level = $this->getServer()->getDefaultLevel();
       					$player->setGamemode(0);
     					
     					if($this->type[$name] == 1)
     					{
     						$this->type[$name] = 4;
						$player->sendMessage("§l§aMessage>>§r §c逃走中を抜けました");
     						$this->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}さんが逃走中を抜けました");
	 						$this->t--;
	 						$player->teleport($level->getSafeSpawn());
     					}
     					elseif($this->type[$name] == 2)
     					{
     						$this->type[$name] = 4;
						$player->sendMessage("§l§aMessage>>§r §c逃走中を抜けました");
     						$this->getServer()->broadcastMessage("§l§bINFO>>§r §c{$name}さんが逃走中を抜けました");
     						$this->h--;
	  						$player->teleport($level->getSafeSpawn());
	  					}
	  					elseif($this->type[$name] == 3)
	  					{
     						$this->type[$name] = 4;
						$player->sendMessage("§l§aMessage>>§r §c逃走中を抜けました");
     						$this->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}さんが逃走中を抜けました");
     						$player->teleport($level->getSafeSpawn()); 
     					}
     					else
     					{
     						$this->type[$name] = 4;
						$player->sendMessage("§l§aMessage>>§r §c参加していないようです");
     					}
     					break;
     				}
     			}
     		}
     	}
     }
     
    public function createWindow(Player $player, $data, int $id)
    {
		$pk = new ModalFormRequestPacket();
		$pk->formId = $id;
		$pk->formData = json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
		$player->dataPacket($pk);
	}
	
	public function sendForm(Player $player, $title, $come, $buttons, $id)
	{
  		$pk = new ModalFormRequestPacket(); 
  		$pk->formId = $id;
  		$this->pdata[$pk->formId] = $player;
  		$data = [ 
  		'type'    => 'form', 
  		'title'   => $title, 
  		'content' => $come, 
  		'buttons' => $buttons 
  		]; 
  		$pk->formData = json_encode( $data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE );
  		$player->dataPacket($pk);
  		$this->lastFormData[$player->getName()] = $data;
  	}
}


