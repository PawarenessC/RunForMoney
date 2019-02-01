<?php

namespace pawarenessc\RFM;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

use pocketmine\event\Listener;
use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\Server;

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

use pawarenessc\RFM\event\PlayerEventListener;
use pawarenessc\RFM\event\FormEventListener;

use pawarenessc\RFM\task\StartTask;
use pawarenessc\RFM\task\GameTask;
use pawarenessc\RFM\command\TagCommand;

use MixCoinSystem\MixCoinSystem;
use metowa1227\MoneySystemAPI\MoneySystemAPI;

class Main extends pluginBase implements Listener
{
    	public $type = NULL;
    	public $map;
    	public $xyz;
    	public $config;
    	public $mis;
    	public $h;
    	public $t;
    	public $game;
    	public $win;
    	public $cogame;
    	public $guest;
    	public $pac;
    	public $eme;
    	public $addh;
    	public $gametime;
    	public $wt;
	
		public function onEnable()
    	{
    		$this->getLogger()->info("=========================");
 			$this->getLogger()->info("RunForMoneyを読み込みました");
 			$this->getLogger()->info("制作者: PawarenessC,NRedFire");
 			$this->getLogger()->info("ライセンス: NYSL Version 0.9982");
 			$this->getLogger()->info("http://www.kmonos.net/nysl/");
 			$this->getLogger()->info("v11.1.4");
 			$this->getLogger()->info("=========================");
    		
    		$this->Event();
    		$this->Config();
    		
    		/*$this->kk = new Config($this->getDataFolder() ."kakuhoa.yml", Config::YAML); //確保した回数
			$this->kkb = new Config($this->getDataFolder() ."kakuhob.yml", Config::YAML); //確保された回数
			$this->nige = new Config($this->getDataFolder() ."nige.yml", Config::YAML); //逃走成功回数
			$this->join = new Config($this->getDataFolder() ."join.yml", Config::YAML); //参加した回数
			$this->runnerc = new Config($this->getDataFolder() ."Runner.yml", Config::YAML); //逃走者になった回数
			$this->hunterc = new Config($this->getDataFolder() ."Hunter.yml", Config::YAML); //ハンターになった回数*/
			
			$this->xyz = new Config(
        $this->getDataFolder() . "xyz.yml", Config::YAML, array(
        "MAP1"=> array(
        "Runner"=> array(
          "x"=>326,
          "y"=>4,
          "z"=>270,
        ),
        "Jall"=> array(
          "x"=>305,
          "y"=>5,
          "z"=>331,
        ),
        "Watch"=> array(
          "x"=>255,
          "y"=>4,
          "z"=>255,
        ),
        "Hunter"=> array(
          "x"=>246,
          "y"=>4,
          "z"=>357,
        ),
        "world"=> "world"
        ),
        
        "MAP2"=> array(
        "Runner"=> array(
          "x"=>11,
          "y"=>4,
          "z"=>514,
        ),
        "Jall"=> array(
          "x"=>191,
          "y"=>9,
          "z"=>810,
        ),
        "Watch"=> array(
          "x"=>11,
          "y"=>11,
          "z"=>11,
        ),
        "Hunter"=> array(
          "x"=>666,
          "y"=>1818,
          "z"=>810,
        ),
        "world"=> "seki",
        "Ready(ok or no)" =>"no"
        )));
        
       
        $this->config = new Config($this->getDataFolder()."Setup.yml", Config::YAML,
			[
			"UnitPrice" => 5,
			
			"RevivalBlockID" => 247,

			"SelfBlockID" => 121,
			
			"WatchBlockID" => 19,
			
			"WaitTime" => 120,

			"GameTime" => 420,
			
			"Plugin" => "EconomyAPI",
				
			"Reward" => 100,
			
			]);

			

		$this->mis = new Config(
        $this->getDataFolder() . "Mission.yml", Config::YAML, array(
        "EmeraldMission"=> array(
          "if"=>"false",
          "time"=>200,
          "Reward"=>100,
        ),
        "Pac-ManMission"=> array(
          "if"=>"false",
          "time"=>100,
          "Reward"=>100,
        ),
        "Increases Hunter"=> array(
          "if"=>"false",
          "time"=>100,
          "LimitTime"=>30,
        ),
        "???"=> array(
          "if"=>"false",
          "time"=>100,
          "Reward"=>100,
        ),
        ));
        
        $this->weapon = new Config(
        $this->getDataFolder() . "Item.yml", Config::YAML, array(
        "HunterSpeedDown"=> array(
          "Name"=>"Down!",
          "id"=>"422",
          "Price"=>100,
        ),
        "SpeedUp"=> array(
          "Name"=>"Up!",
          "id"=>"405",
          "Price"=>100,
        ),
        "HighJump"=> array(
          "Name"=>"Jump!!",
          "id"=>"377",
          "Price"=>30,
        ),
        "Invisible"=> array(
          "Name"=>"This is a Invisible",
          "id"=>"369",
          "Price"=>100,
        ),
        "Revival"=> array(
          "Name"=>"RevivalItem",
          "id"=>"452",
          "Price"=>100,
        ),
        ));
			
			$this->system = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
    		
    		$this->saveDefaultConfig();
        	$this->reloadConfig();
        	
			$this->h = 0; // 鬼
    		$this->t = 0; // 逃走者
    		$this->all = $this->h + $this->t; // 鬼、逃走者合わせて
    		
    		$this->game = false; //ゲームの状態
    		$this->win = 0; // 賞金
    		$this->cogame = false;
    		$this->guest = true;
    		
    		/*ミッション*/
    		$this->pac = false;
    		$this->eme = false;
    		$this->addh = false;
    		/*ミッション*/
    		
    		
    		$this->map = 1;
    		
    		$this->gametime = $this->config->get("GameTime");
 			$this->wt = $this->config->get("WaitTime");
 			
 			$this->getScheduler()->scheduleRepeatingTask(new GameTask($this, $this), 20);
  			$this->getScheduler()->scheduleRepeatingTask(new StartTask($this, $this), 20);
  		}
  		
  		public function ReloadGame()
  		{
  			$this->h = 0; // 鬼
    		$this->t = 0; // 逃走者
    		$this->game = false; //ゲームの状態
    		$this->win = 0; // 賞金
    		$this->gametime = $this->config->get("GameTime");
 			$this->wt = $this->config->get("WaitTime");
 			$this->cogame = false;
 			
 			$this->pac = false;
    		$this->eme = false;
    		$this->addh = false;
 		}
  	
  	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		$class = new TagCommand($this);
		$class->onCommand($sender, $command, $label, $args, $this);
		return true;
	}
  	
  	public function endGame()
  	{
  		$this->ReloadGame();
 		
 		foreach(Server::getInstance()->getOnlinePlayers() as $player)
 		{
 			$level = $this->getServer()->getDefaultLevel();
			$name = $player->getName();
			$player->setImmobile(false);
			
			if($this->type[$name] == 0 or $this->type[$name] == 1 or $this->type[$name] == 2 or $this->type[$name] == 3)
			{
   				$player->teleport($level->getSafeSpawn());
   				$player->removeEffect(1);
				$player->setGamemode(0);
   				$player->setNameTag($player->getDisplayName());
				$this->type[$name] = 4;
   			}
   			else
   			{
   				$this->type[$name] = 4; // 保険
   			}
   		}
   	}
   	
   	public function team($player, $team)
   	{
		$name = $player->getName();
  	
  		if($team == "runner")
  		{
			$this->type[$name] = 1;
			$this->runnerc->set($name, $this->runnerc->get($name)+1);
			$this->runnerc->save();
			
			$this->join->set($name, $this->join->get($name)+1);
			$this->join->save();
			$t = $this->t;
			$this->t++;
		}
  		
  		if($team == "hunter")
  		{
  			$this->type[$name] = 2;
  			$this->join->set($name, $this->join->get($name)+1);
			$this->join->save();
			
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
 	 		MixCoinSystem::getInstance()->PlusCoin($name,$money);
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
 	
 	public function getMoney($name)
 	{
 		$plugin = $this->config->get("Plugin");
 		if($plugin == "EconomyAPI")
 		{
 	  		return $this->system->myMoney($name);
 		}
 		
 		if($plugin == "MixCoinSystem")
 		{
 			return MixCoinSystem::getInstance()->GetCoin($name);
 		}
 		
 		if($plugin == "MoneySystem")
 		{
 			return MoneySystemAPI::getInstance()->CheckByName($name);
 		}
 		
 		if($plugin == "MoneyPlugin")
 		{
 			return $this->getServer()->getPluginManager()->getPlugin("MoneyPlugin")->getMoney($name);
 		}
 	}
 	
 	public function cutMoney($name, $money)
 	{
 		$plugin = $this->config->get("Plugin");
 		if($plugin == "EconomyAPI")
 		{
 	  		$this->system->reduceMoney($name, $money);
 		}
 		
 		if($plugin == "MixCoinSystem")
 		{
 			MixCoinSystem::getInstance()->MinusCoin($name,$money);
 		}
 		
 		if($plugin == "MoneySystem")
 		{
 			MoneySystemAPI::getInstance()->TakeMoneyByName($name, $money);
 		}
 		
 		if($plugin == "MoneyPlugin")
 		{
 			$this->getServer()->getPluginManager()->getPlugin("MoneyPlugin")->removemoney($name,$money);
 		}
 	}
 	
 	public function getNige($name)
 	{
 		if($this->nige->exists($name))
 		{
 			return $this->nige->get($name);
 		}
 		else
 		{
 			$this->nige->set($name,0);
 			$this->nige->save();
 			return 0;
 		}
 	}
	
	public function getKakuho($name)
	{
		if($this->kk->exists($name))
		{
			return $this->kk->get($name);
		}
		else
		{
			$this->kk->set($name,0);
			$this->kk->save();
			return 0;
		}
	}
	
 	public function Popup($msg = "")
 	{
 		$players = $this->getServer()->getOnlinePlayers();
        foreach ($players as $player)
        {
        	$player->sendPopup($msg);
        }
    }
    
    public function msg($msg = "")
    {
    	$players = $this->getServer()->getOnlinePlayers();
        foreach ($players as $player)
        {
        	$player->sendMessage($msg);
        }
    }
    
    public function Event()
    {
    	$this->getServer()->getPluginManager()->registerEvents($this, $this);
    	$this->getServer()->getPluginManager()->registerEvents(new PlayerEventListener($this), $this);
    	$this->getServer()->getPluginManager()->registerEvents(new FormEventListener($this), $this);
 	}
 	
 	public function Config()
 	{
 		if(!file_exists($this->getDataFolder()."data/")){
            @mkdir($this->getDataFolder()."data/");
        }
        
        if(!is_file($this->getDataFolder()."data/Hunter.yml")){
                $this->saveResource("data/Hunter.yml");
                $this->hunterc = new Config($this->getDataFolder() ."data/Hunter.yml", Config::YAML); //ハンターになった回数
        }else{ $this->hunterc = new Config($this->getDataFolder() ."data/Hunter.yml", Config::YAML);} //ハンターになった回数
        
        if(!is_file($this->getDataFolder()."data/Runter.yml")){
                $this->saveResource("data/Runter.yml");
                $this->runnerc = new Config($this->getDataFolder() ."data/Runner.yml", Config::YAML); //逃走者になった回数
        }else{ $this->runnerc = new Config($this->getDataFolder() ."data/Runner.yml", Config::YAML);} //逃走者になった回数
        
        if(!is_file($this->getDataFolder()."data/Join.yml")){
                $this->saveResource("data/Join.yml");
                $this->join = new Config($this->getDataFolder() ."data/Join.yml", Config::YAML); //参加した回数
        }else{ $this->join = new Config($this->getDataFolder() ."data/Join.yml", Config::YAML);} //参加した回数
        
        if(!is_file($this->getDataFolder()."data/kakuhoa.yml")){
                $this->saveResource("data/kakuhoa.yml");
                $this->kk = new Config($this->getDataFolder() ."data/kakuhoa.yml", Config::YAML); //確保した回数
        }else{ $this->kk = new Config($this->getDataFolder() ."data/kakuhoa.yml", Config::YAML);} //確保した回数
        
        if(!is_file($this->getDataFolder()."data/kakuhob.yml")){
                $this->saveResource("data/kakuhob.yml");
                $this->kkb = new Config($this->getDataFolder() ."data/kakuhob.yml", Config::YAML); //確保された回数
        }else{ $this->kkb = new Config($this->getDataFolder() ."data/kakuhob.yml", Config::YAML);} //確保された回数
        
        if(!is_file($this->getDataFolder()."data/Nige.yml")){
                $this->saveResource("data/Nige.yml");
                $this->nige = new Config($this->getDataFolder() ."data/Nige.yml", Config::YAML); //逃走成功回数
        }else{ $this->nige = new Config($this->getDataFolder() ."data/Nige.yml", Config::YAML);} //逃走成功回数
   }
 	public function startMenu($player) 
  	{
		$tanka = $this->config->get("UnitPrice");
		$wti   = $this->config->get("WaitTime");
		$gti   = $this->config->get("GameTime");
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
        $buttons[] = [ 
        'text' => "§l§cミッションの設定", 
        'image' => [ 'type' => 'path', 'data' => "" ] 
        ]; //9
        /*$buttons[] = [ 
        'text' => "§l§7Debug", 
        'image' => [ 'type' => 'path', 'data' => "" ] 
        ]; //10*/
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
        'text' => "§l§c逃走中から抜ける", 
        'image' => [ 'type' => 'path', 'data' => "" ] 
        ]; //1
        $buttons[] = [ 
        'text' => "§l§eステータスを確認する", 
        'image' => [ 'type' => 'path', 'data' => "" ] 
        ]; //2
        $buttons[] = [ 
        "text" => "§l§6ランキングを確認する",
        "image" => [ 'type' => 'path', 'data' => "" ] 
        ]; //3
        $buttons[] = [
        "text" => "§l§d§oSHOP",
        "image" => [ 'type' => 'path', 'data' => "" ] 
        ]; //4
        $this->sendForm($player,"TagGame","§a選択してください",$buttons,1145145);
        $this->info[$name] = "form";
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


