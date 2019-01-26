<?php

namespace pawarenessc\RFM\event;

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

use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\level\particle\DustParticle;

use pocketmine\block\Block;

use pocketmine\item\Item;

use pocketmine\math\Vector3;

use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

class FormEventListener implements Listener
{
	
		public $shop = NULL;
		
		public function __construct($owner)
		{
        	$this->owner = $owner;
    	}
    	
    	public function onPrecessing(DataPacketReceiveEvent $event)
    	{
  			$owner = $this->owner;
  			$shop = $this->shop;
  			$player = $event->getPlayer();
  			$pk = $event->getPacket();
  			$name = $player->getName();
  	
  			$misse = $owner->mis->get("EmeraldMission");
  			$missp = $owner->mis->get("Pac-ManMission");
  			$missh = $owner->mis->get("Increases Hunter");
  			
  			$weapon = $owner->weapon->getAll();
  	
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
  			 				if($owner->game == false && $owner->cogame == false)
  			 				{
  			 					$owner->getScheduler()->scheduleRepeatingTask(new StartTask($owner, $owner), 20);
  			 					$owner->game = false;
  			 					$owner->cogame = true;
       							$owner->getServer()->broadcastMessage("§l§bINFO>>§r §b逃走中を開催します！ /taguiで参加しましょう！");
       						
       						}
       						else
       						{
       							$player->sendMessage("§l§aMessage>>§r §c既に開催されています");
       						}
       						break;
       			
       					}
       					elseif($data == 1) // 終了(始まってなかろうが強制終了)
       					{
       						$owner->endGame();
       						$owner->getServer()->broadcastMessage("§l§bINFO>>§r §c権限者によって逃走中が終了しました");
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
       						$owner->sendForm($player,"DisablePlugin","本当にプラグインを停止しますか？\n\n",$buttons,19198101);
							break;
				
						}
						elseif($data == 3) // 設定
						{
							$tanka = $owner->config->get("UnitPrice");
							$wti   = $owner->config->get("WaitTime");
							$gti   = $owner->config->get("GameTime");
        					
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
							"placeholder" => "11秒以上を設定してください",
							"default" => ""
							],
							[
							"type" => "input",
							"text" => "ゲーム時間の現在の設定:{$gti}§b秒",
							"placeholder" => "",
							"default" => ""
								]
								]
								];
							$owner->createWindow($player, $data, 6381961);
        					break;
        
        				}
        				elseif($data == 4) // 参加の設定
        				{
        					if($owner->guest == true)
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
        					$owner->sendForm($player,"逃走中参加の設定","true = 参加可能\nfalse = 参加不可能\n\n現在の設定 {$setup}",$buttons,436271);
        					$owner->info[$name] = "form";
							break;
			
						}
						elseif($data == 5)
						{
				
							$buttons[] = [ 
							'text' => "§l§3マップ1の座標指定", 
							'image' => [ 'type' => 'path', 'data' => "" ]  
							]; //0
							$buttons[] = [ 
							'text' => "§l§1マップ2の座標指定", 
							'image' => [ 'type' => 'path', 'data' => "" ]  
							]; //1
							$owner->sendForm($player,"逃走中座標の設定","指定したい設定ボタンを押すと現在の座標が登録されます。\n",$buttons,467389);
        					$owner->info[$name] = "form";
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
							$owner->createWindow($player, $data, 77817);
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
			$owner->createWindow($player, $data, 413180);
			break;
			}
			elseif($data == 8)
			{
				if($owner->game == false)
				{
					$owner->ReloadGame();
					$player->sendMessage("§l§aMessage>>§r 設定を更新しました");
					break;
				}
				else
				{
					$player->sendMessage("§l§aMessage>>§r §cゲーム中です、ゲームが終わってから更新ボタンを押してください");
					break;
				}
			}
			elseif($data == 9)
			{
				$buttons[] = [ 
        		'text' => "§lエメラルドブロックミッション", 
        		'image' => [ 'type' => 'path', 'data' => "" ] 
        		]; //0
        		$buttons[] = [ 
        		'text' => "§6パックマンミッション", 
        		'image' => [ 'type' => 'path', 'data' => "" ] 
        		]; //1
        		$buttons[] = [ 
        		'text' => "§cハンターの増加", 
        		'image' => [ 'type' => 'path', 'data' => "" ] 
        		]; //2
        		$buttons[] = [ 
        		"text" => "§7準備中",
        		"image" => [ 'type' => 'path', 'data' => "" ] 
        		]; //3
        		$owner->sendForm($player,"ミッションの設定","ミッションの設定を更新ができます\n\n",$buttons,8319391);
        		$owner->info[$name] = "form";
				break;
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

						$owner->gametime = $owner->gametime + $addtime;
						$player->sendMessage("§l§aMessage>>§r §6".$addtime."秒追加しました");
						return true;
					}
				
				case 19198101:
				if($data == 0)
				{//無効化 はい
					$owner->getServer()->getPluginManager()->disablePlugin($owner);
					$player->sendMessage("§l§aMessage>>§r §a逃走中プラグインを無効化しました、再起動又はリロードをすれば再読み込みします");
       				break;
       			}
       			else
       			{
       				$owner->startMenu($player);
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

						$owner->config->set("UnitPrice", $tanka);
						$owner->config->save();
						$player->sendMessage("§a逃走中の単価を§d".$tanka."§aに更新しました");
					}
					
					if($owner->game == false)
					{
						if ($result[2] === "")
						{
							$player->sendMessage("§l§aMessage>>§r 記入してないのでデフォルトをセットします (待機時間)");
							$owner->config->set("WaitTime", 120);
							$owner->config->save();
						}
						else
						{
							$owner->config->set("WaitTime", $wtime);
							$owner->config->save();
							$player->sendMessage("§l§aMessage>>§r §a逃走中の待機時間を§b".$wtime."§a秒に更新しました");
						}
					
						if($result[3] === "")
						{
							$player->sendMessage("§l§aMessage>>§r 記入してないのでデフォルトをセットします (ゲーム時間)");
							$owner->config->set("GameTime", 420);
							$owner->config->save();
						}
						else
						{
							$owner->config->set("GameTime", $gtime);
							$owner->config->save();
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
					$owner->guest = true;
					$player->sendMessage("§l§aMessage>>§r §a参加可能にしました");
					break;
				}
				else
				{
					$owner->guest = false;
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

						$owner->gametime = $owner->gametime - $cuttime;
						$player->sendMessage("§l§aMessage>>§r §a逃走中の時間から".$cuttime."秒引きました");
						break;
					}
				break;
				
				case 467389:
				if($data == 0)
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
					$owner->sendForm($player,"逃走中座標の設定","マップ1の指定したい設定ボタンを押すと現在の座標が登録されます。\n",$buttons,4091);
        			$owner->info[$name] = "form";
					break;
				}
				elseif($data == 1)
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
					$buttons[] = [ 
					'text' => "§l§6準備はどうですか？(*'ω'*)", 
					'image' => [ 'type' => 'path', 'data' => "" ]  
					]; //4
					$owner->sendForm($player,"逃走中座標の設定","マップ2の指定したい設定ボタンを押すと現在の座標が登録されます。\n",$buttons,4092);
        			$owner->info[$name] = "form";
					break;
				}
				
				
				case 4091: //マップ1
				$x = $player->x;
				$y = $player->y;
				$z = $player->z;
				
				$level = $player->getLevel();
				$level_name = $level->getName();
				if($data == 0) // 逃走者
				{
					$player->sendMessage("§l§aMessage>>§r マップ1の逃走者の座標を更新しました。\nX{$x}\nY{$y}\nZ{$z}");
					
					$data = $owner->xyz->get("MAP1");
					$data["Runner"]["x"] = $x;
					$data["Runner"]["y"] = $y;
					$data["Runner"]["z"] = $z;
					
					$data["world"] = $level_name;
					
					$owner->xyz->set("MAP1", $data);
					$owner->xyz->save();
					
					$owner->xyz->set("MAP1", $data);
					$owner->xyz->save();
					break;
				}	
				elseif($data == 1) // ハンター
				{
					$player->sendMessage("§l§aMessage>>§r マップ1のハンターの座標を更新しました。\nX{$x}\nY{$y}\nZ{$z}");
					
					$data = $owner->xyz->get("MAP1");
					$data["Hunter"]["x"] = $x;
					$data["Hunter"]["y"] = $y;
					$data["Hunter"]["z"] = $z;
					
					$owner->xyz->set("MAP1", $data);
					$owner->xyz->save();
					break;
				}
				elseif($data == 2) // 牢屋
				{
					$player->sendMessage("§l§aMessage>>§r マップ1の牢屋の座標を更新しました。\nX{$x}\nY{$y}\nZ{$z}");
					
					$data = $owner->xyz->get("MAP1");
					$data["Jall"]["x"] = $x;
					$data["Jall"]["y"] = $y;
					$data["Jall"]["z"] = $z;
					
					$owner->xyz->set("MAP1", $data);
					$owner->xyz->save();
					break;
				}
				elseif($data == 3) // 観戦
				{
					$player->sendMessage("§l§aMessage>>§r マップ1の観戦の座標を更新しました。\nX{$x}\nY{$y}\nZ{$z}");
					
					$data = $owner->xyz->get("MAP1");
					$data["Watch"]["x"] = $x;
					$data["Watch"]["y"] = $y;
					$data["Watch"]["z"] = $z;
					
					$owner->xyz->set("MAP1", $data);
					$owner->xyz->save();
					break;
				}
				break;
				
				case 4092: //マップ2
				$x = $player->x;
				$y = $player->y;
				$z = $player->z;
				
				$level = $player->getLevel();
				$level_name = $level->getName();
				if($data == 0) // 逃走者
				{
					$player->sendMessage("§l§aMessage>>§r マップ2の逃走者の座標を更新しました。\nX{$x}\nY{$y}\nZ{$z}");
					
					$data = $owner->xyz->get("MAP2");
					$data["Runner"]["x"] = $x;
					$data["Runner"]["y"] = $y;
					$data["Runner"]["z"] = $z;
					
					$data["world"] = $level_name;
					
					$owner->xyz->set("MAP2", $data);
					$owner->xyz->save();
					
					$owner->xyz->set("MAP2", $data);
					$owner->xyz->save();
					break;
				}	
				elseif($data == 1) // ハンター
				{
					$player->sendMessage("§l§aMessage>>§r マップ2のハンターの座標を更新しました。\nX{$x}\nY{$y}\nZ{$z}");
					
					$data = $owner->xyz->get("MAP2");
					$data["Hunter"]["x"] = $x;
					$data["Hunter"]["y"] = $y;
					$data["Hunter"]["z"] = $z;
					
					$owner->xyz->set("MAP2", $data);
					$owner->xyz->save();
					break;
				}
				elseif($data == 2) // 牢屋
				{
					$player->sendMessage("§l§aMessage>>§r マップ2の牢屋の座標を更新しました。\nX{$x}\nY{$y}\nZ{$z}");
					
					$data = $owner->xyz->get("MAP2");
					$data["Jall"]["x"] = $x;
					$data["Jall"]["y"] = $y;
					$data["Jall"]["z"] = $z;
					
					$owner->xyz->set("MAP2", $data);
					$owner->xyz->save();
					break;
				}
				elseif($data == 3) // 観戦
				{
					$player->sendMessage("§l§aMessage>>§r マップ2の観戦の座標を更新しました。\nX{$x}\nY{$y}\nZ{$z}");
					
					$data = $owner->xyz->get("MAP2");
					$data["Watch"]["x"] = $x;
					$data["Watch"]["y"] = $y;
					$data["Watch"]["z"] = $z;
					
					$owner->xyz->set("MAP2", $data);
					$owner->xyz->save();
					break;
				}
				elseif($data == 4) //準備
				{
					$buttons[] = [ 
        			'text' => "§lできました！", 
        			'image' => [ 'type' => 'path', 'data' => "" ] 
        			]; //0
        			$buttons[] = [ 
        			'text' => "§lまだ！", 
        			'image' => [ 'type' => 'path', 'data' => "" ] 
        			]; //1
        			$owner->sendForm($player,"§l準備はどうですか","マップ2の準備はできましたか？",$buttons,992881);
        			$owner->info[$name] = "form";
					break;
				}
				break;
				
				case 992881:
				if($data == 0)
				{
					$player->sendMessage("§l§aMessage>>§r §a設定お疲れ様です！、次回の逃走中から適用されます！");
					$data = $owner->xyz->get("MAP2");
					$data["Ready(ok or no)"] = "ok";
					
					$owner->xyz->set("MAP2", $data);
					$owner->xyz->save();
					break;
				}
				else
				{
					$player->sendMessage("§l§aMessage>>§r §c了解です！準備頑張ってください！");
					$data = $owner->xyz->get("MAP2");
					$data["Ready(ok or no)"] = "no";
					
					$owner->xyz->set("MAP2", $data);
					$owner->xyz->save();
					break;
				}
				break;
				
				case 8319391:
				
				switch($data)
				{
					case 0: //エメラルドブロックミッション
					$if = $misse["if"];
					$time = $misse["time"];
					$money = $misse["Reward"];
					$buttons[] = [ 
        			'text' => "§l発動設定の変更", 
        			'image' => [ 'type' => 'path', 'data' => "" ] 
        			]; //0
        			$buttons[] = [ 
        			'text' => "§l発動時間の変更", 
        			'image' => [ 'type' => 'path', 'data' => "" ] 
        			]; //1
        			$buttons[] = [ 
        			'text' => "§l報酬の変更", 
        			'image' => [ 'type' => 'path', 'data' => "" ] 
        			]; //2
        			$owner->sendForm($player,"エメラルドミッションの設定","エメラルドブロックミッションの設定\ntrue = 発動\nfalse = 不発動\n\n§a発動条件:§f{$if}\n§a発動時間:{$time}秒\n§a報酬:{$money}",$buttons,947290);
        			$owner->info[$name] = "form";
					break;
					
					case 1: //パックマンミッション
					$if = $missp["if"];
					$time = $missp["time"];
					$money = $missp["Reward"];
					$buttons[] = [ 
        			'text' => "§l発動設定の変更", 
        			'image' => [ 'type' => 'path', 'data' => "" ] 
        			]; //0
        			$buttons[] = [ 
        			'text' => "§l発動時間の変更", 
        			'image' => [ 'type' => 'path', 'data' => "" ] 
        			]; //1
        			$buttons[] = [ 
        			'text' => "§l報酬の変更", 
        			'image' => [ 'type' => 'path', 'data' => "" ] 
        			]; //2
        			$owner->sendForm($player,"パックマンミッションの設定","パックマンミッションの設定\ntrue = 発動\nfalse = 不発動\n\n§a発動条件:§f{$if}\n§a発動時間:{$time}秒\n§a報酬:{$money}",$buttons,70991);
        			$owner->info[$name] = "form";
					break;
					
					case 2: //ハンター増加
					$if = $missh["if"];
					$time = $missh["time"];
					$limit = $missh["LimitTime"];
					
					$buttons[] = [ 
        			'text' => "§l発動設定の変更", 
        			'image' => [ 'type' => 'path', 'data' => "" ] 
        			]; //0
        			$buttons[] = [ 
        			'text' => "§l発動時間の変更", 
        			'image' => [ 'type' => 'path', 'data' => "" ] 
        			]; //1
        			$buttons[] = [
        			"text" => "§l制限時間の変更",
        			'image' => [ 'type' => 'path', 'data' => "" ] 
        			]; //2
        			$owner->sendForm($player,"ハンター増加ミッションの設定","ハンター増加の現在の設定\ntrue = 発動\nfalse = 不発動\n\n発動条件:§f{$if}\n発動時間:{$time}秒\n制限時間{$limit}秒",$buttons,70882);
        			$owner->info[$name] = "form";
					break;
				}
				break;
				
				case 947290: //エメラルドブロック
				$if = $misse["if"];
				$time = $misse["time"];
				
				switch($data)
				{
					case 0: //発動
					$buttons[] = [ 
        			'text' => "§ltrue(発動)", 
        			'image' => [ 'type' => 'path', 'data' => "" ] 
        			]; //0
        			$buttons[] = [ 
        			'text' => "§lfalse(不発動)", 
        			'image' => [ 'type' => 'path', 'data' => "" ] 
        			]; //1
        			$owner->sendForm($player,"発動設定","現在の設定:{$if}",$buttons,7099);
        			$owner->info[$name] = "form";
					break;
					
					case 1: //時間
					$data = [
					"type" => "custom_form",
					"title" => "時間設定",
					"content" => [
						[
						"type" => "label",
						"text" => "時間を設定してください。"
						],
						[
						"type" => "input",
						"text" => "§b秒数",
						"placeholder" => "",
						"default" => ""
						]
					]
				];
					$owner->createWindow($player, $data, 666); //ね？言ったでしょ？
					break;
				
					case 2:
					$data = [
					"type" => "custom_form",
					"title" => "報酬設定",
					"content" => [
						[
						"type" => "label",
						"text" => "§l報酬(数字)を設定してください。"
						],
						[
						"type" => "input",
						"text" => "§b金額",
						"placeholder" => "",
						"default" => ""
						]
					]
				];
					$owner->createWindow($player, $data, 6666); //ね？言ったでしょ？
					break;
				}
				break;
				
				case 70991: //パックマン
				$if = $missp["if"];
				$time = $missp["time"];
				
				switch($data)
				{
					case 0: //発動
					$buttons[] = [ 
        			'text' => "§ltrue(発動)", 
        			'image' => [ 'type' => 'path', 'data' => "" ] 
        			]; //0
        			$buttons[] = [ 
        			'text' => "§lfalse(不発動)", 
        			'image' => [ 'type' => 'path', 'data' => "" ] 
        			]; //1
        			$owner->sendForm($player,"発動設定","現在の設定:{$if}",$buttons,70992);
        			$owner->info[$name] = "form";
					break;
					
					case 1: //時間
					$data = [
					"type" => "custom_form",
					"title" => "時間設定",
					"content" => [
						[
						"type" => "label",
						"text" => "時間を設定してください。"
						],
						[
						"type" => "input",
						"text" => "§b秒数",
						"placeholder" => "",
						"default" => ""
						]
					]
				];
					$owner->createWindow($player, $data, 8161901);
					break;
				
					case 2:
					$data = [
					"type" => "custom_form",
					"title" => "報酬設定",
					"content" => [
						[
						"type" => "label",
						"text" => "§l金額を設定してください。"
						],
						[
						"type" => "input",
						"text" => "§l§b金額",
						"placeholder" => "",
						"default" => ""
						]
					]
				];
					$owner->createWindow($player, $data, 920183);
					break;
				}
				break;
				
				case 70882: //ハンター増加
				$if = $missh["if"];
				$time = $missh["time"];
				$limit = $missh["LimitTime"];
				
				switch($data)
				{
					case 0: //発動
					$buttons[] = [ 
        			'text' => "§ltrue(発動)", 
        			'image' => [ 'type' => 'path', 'data' => "" ] 
        			]; //0
        			$buttons[] = [ 
        			'text' => "§lfalse(不発動)", 
        			'image' => [ 'type' => 'path', 'data' => "" ] 
        			]; //1
        			$owner->sendForm($player,"発動設定","ハンター増加現在の設定:{$if}",$buttons,70883);
        			$owner->info[$name] = "form";
					break;
					
					case 1: //時間
					$data = [
					"type" => "custom_form",
					"title" => "時間設定",
					"content" => [
						[
						"type" => "label",
						"text" => "時間を設定してください。"
						],
						[
						"type" => "input",
						"text" => "§b秒数",
						"placeholder" => "",
						"default" => ""
						]
					]
				];
					$owner->createWindow($player, $data, 70884);
					break;
				
					case 2: //制限時間
					$data = [
					"type" => "custom_form",
					"title" => "制限時間設定",
					"content" => [
						[
						"type" => "label",
						"text" => "§l時間を設定してください。"
						],
						[
						"type" => "input",
						"text" => "§l§b制限時間",
						"placeholder" => "",
						"default" => ""
						]
					]
				];
					$owner->createWindow($player, $data, 70885);
					break;
				}
				break;
				
				case 7099: //エメラルド発動
				if($data == 0)
				{
					$player->sendMessage("§l§aMessage>>§r [エメラルド] 発動 に設定しました");
					$misse["if"] = "true";
					
					$owner->mis->set("EmeraldMission", $misse);
					$owner->mis->save();
					break;
				}
				else
				{
					$player->sendMessage("§l§aMessage>>§r [エメラルド] 不発動 に設定しました");
					$misse["if"] = "false";
					
					$owner->mis->set("EmeraldMission", $misse);
					$owner->mis->save();
					break;
				}
				break;
				
				case 70992: //パックマン発動
				if($data == 0)
				{
					$player->sendMessage("§l§aMessage>>§r [パックマン] 発動 に設定しました");
					$missp["if"] = "true";
					
					$owner->mis->set("Pac-ManMission", $missp);
					$owner->mis->save();
					break;
				}
				else
				{
					$player->sendMessage("§l§aMessage>>§r [パックマン] 不発動 に設定しました");
					$missp["if"] = "false";
					
					$owner->mis->set("Pac-ManMission", $missp);
					$owner->mis->save();
					break;
				}
				break;
				
				case 666: //エメラルド時間
				$time = $result[1];
				if(is_numeric($time))
				{
					$player->sendMessage("§l§aMessage>>§r [エメラルド] 発動時間を§l{$time}§rに更新しました");
					$misse["time"] = $time;
					
					$owner->mis->set("EmeraldMission", $misse);
					$owner->mis->save();
					break;
				}
				else
				{
					$player->sendMessage("§l§aMessage>>§r §c数字を入力してください！");
					break;
				}
				break;
				
				case 8161901: //パックマン時間
				$time = $result[1];
				if(is_numeric($time))
				{
					$player->sendMessage("§l§aMessage>>§r [パックマン] 発動時間を§l{$time}§rに更新しました");
					$missp["time"] = $time;
					
					$owner->mis->set("Pac-ManMission", $missp);
					$owner->mis->save();
					break;
				}
				else
				{
					$player->sendMessage("§l§aMessage>>§r §c数字を入力してください！");
					break;
				}
				break;
				
				case 6666: //エメラルド報酬
				$money = $result[1];
				if(is_numeric($money))
				{
					$player->sendMessage("§l§aMessage>>§r [エメラルド] 報酬を§l{$money}§rに更新しました");
					$misse["Reward"] = $money;
					
					$owner->mis->set("EmeraldMission", $misse);
					$owner->mis->save();
					break;
				}
				else
				{
					$player->sendMessage("§l§aMessage>>§r §c数字を入力してください！");
					break;
				}
				break;
				
				case 920183: //パックマン報酬
				$money = $result[1];
				if(is_numeric($money))
				{
					$player->sendMessage("§l§aMessage>>§r [パックマン] 報酬を§l{$money}§rに更新しました");
					$missp["Reward"] = $money;
					
					$owner->mis->set("Pac-ManMission", $missp);
					$owner->mis->save();
					break;
				}
				else
				{
					$player->sendMessage("§l§aMessage>>§r §c数字を入力してください！");
					break;
				}
				break;
				
				
					/*ハンター増加*/
				case 70883: //条件
				if($data == 0)
				{
					$player->sendMessage("§l§aMessage>>§r [ハンター増加] 発動 に設定しました");
					$missh["if"] = "true";
					
					$owner->mis->set("Increases Hunter", $missh);
					$owner->mis->save();
					break;
				}
				else
				{
					$player->sendMessage("§l§aMessage>>§r [ハンター増加] 不発動 に設定しました");
					$missh["if"] = "false";
					
					$owner->mis->set("Increases Hunter", $missh);
					$owner->mis->save();
					break;
				}
				break;
				
				case 70884: //時間
				$time = $result[1];
				if(is_numeric($time))
				{
					$player->sendMessage("§l§aMessage>>§r [ハンター増加] 発動時間を§l{$time}§rに更新しました");
					$missh["time"] = $time;
					
					$owner->mis->set("Increases hunter", $misse);
					$owner->mis->save();
					break;
				}
				else
				{
					$player->sendMessage("§l§aMessage>>§r §c数字を入力してください！");
					break;
				}
				break;
				
				case 77085: //制限時間
				$limit = $result[1];
				if(is_numeric($limit))
				{
					$player->sendMessage("§l§aMessage>>§r [ハンター増加] 制限時間を§l{$limit}§rに更新しました");
					$missh["LimitTime"] = $limit;
					
					$owner->mis->set("Increases Hunter", $missp);
					$owner->mis->save();
					break;
				}
				else
				{
					$player->sendMessage("§l§aMessage>>§r §c数字を入力してください！");
					break;
				}
				break;
				
				
				
				
				//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				
				
				
				
				
				/* プレイヤー用 */
				
				case 1145145://プレイヤー用
				if($data == 0)
				{
			 		$H = $owner->h;
					$T = $owner->t;
					$all = $H + $T;
    
    				$player->setNameTag("");
					$name = $player->getName();

     				if($owner->type[$name] == 1)
     				{
						$player->sendMessage("§l§aMessage>>§r §c既に参加しています");
					
					}
					elseif($owner->type[$name] == 2)
					{
      					$player->sendMessage("§l§aMessage>>§r §c既に参加しています");
     				}
     				elseif($owner->type[$name] == 3)
     				{
						$player->sendMessage("§l§aMessage>>§r §c既に参加しています");
					}
					else
					{
  						if($owner->game == false)
  						{
  	 						if($owner->cogame == false)
  	 						{
  	  							if($all == 0)
  	  							{
  									$owner->game = false;
  									$owner->cogame = true;
									$owner->getServer()->broadcastMessage("§l§bINFO>>§r §b逃走中を開催します！ /taguiで参加しましょう！");
									
									$team = "runner";
									$owner->team($player, $team);
      								
      								$owner->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}さんが逃走中に参加しました");
								}
							}
							else
							{
								if($H < 10)
								{
									if($H >= $T / 3)
									{
										$team = "runner";
										$owner->team($player, $team);
										$owner->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}さんが逃走中に参加しました");
										
										}
										elseif($H < $T)
										{
											$team = "hunter";
											$owner->team($player, $team);
											$owner->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}さんが逃走中に参加しました");
											
										}
										elseif($H === $T)
										{
											$team = 'runner';
											$owner->team($player, $team);
											$owner->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}さんが逃走中に参加しました");
										}
								}
								else
								{
									$team = 'runner';
									$owner->team($player, $team);
									$owner->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}さんが逃走中に参加しました");
								}
							}
						}
					}
  	
  					if($owner->game === true)
  					{
						$player->sendMessage("§l§aMessage>>§r §r§b現在試合中です、途中参加するか、観戦をしてお楽しみください。");
						$player->addTitle("§cError", "試合中", 20, 20, 20);
					}
					break;
   
    				}
    				elseif($data == 1)
    				{
    					$name = $player->getName();
 	       				$level = $owner->getServer()->getDefaultLevel();
       					$player->setGamemode(0);
     					
     					if($owner->type[$name] == 1)
     					{
     						$owner->type[$name] = 4;
						$player->sendMessage("§l§aMessage>>§r §c逃走中を抜けました");
     						$owner->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}さんが逃走中を抜けました");
	 						$owner->t--;
	 						$player->teleport($level->getSafeSpawn());
     					}
     					elseif($owner->type[$name] == 2)
     					{
     						$owner->type[$name] = 4;
						$player->sendMessage("§l§aMessage>>§r §c逃走中を抜けました");
     						$owner->getServer()->broadcastMessage("§l§bINFO>>§r §c{$name}さんが逃走中を抜けました");
     						$owner->h--;
	  						$player->teleport($level->getSafeSpawn());
	  					}
	  					elseif($owner->type[$name] == 3)
	  					{
     						$owner->type[$name] = 4;
						$player->sendMessage("§l§aMessage>>§r §c逃走中を抜けました");
     						$owner->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}さんが逃走中を抜けました");
     						$player->teleport($level->getSafeSpawn()); 
     					}
     					else
     					{
     						$owner->type[$name] = 4;
						$player->sendMessage("§l§aMessage>>§r §c参加していないようです");
     					}
     					break;
     				
     				}
     				elseif($data == 2)
     				{
     					$name = $player->getName();
     					$data = [
     					
     					"type" => "custom_form",
     					"title" => "§b{$name}さんのステータス",
     					"content" => [
						[
						"type" => "label",
						"text" => "逃げ切った回数: §e{$owner->getNige($name)}§r回"
						],
						[
						"type" => "label",
						"text" => "確保した回数: §e{$owner->getKakuho($name)}§r回"
						]
					]
				];

						$owner->createWindow($player, $data, 492);
						break;
     				
     				}
     				elseif($data == 3)
     				{
     					$buttons[] = [ 
						'text' => "§l§a確保回数のランキング", 
						'image' => [ 'type' => 'path', 'data' => "" ]  
						]; //0
						$buttons[] = [ 
						'text' => "§l§b逃走成功回数のランキング", 
						'image' => [ 'type' => 'path', 'data' => "" ]  
						]; //1
						$buttons[] = [ 
						'text' => "§l§c捕まった回数のランキング", 
						'image' => [ 'type' => 'path', 'data' => "" ]  
						]; //2
						$buttons[] = [ 
						'text' => "§l§e参加回数のランキング", 
						'image' => [ 'type' => 'path', 'data' => "" ]  
						]; //3
						$buttons[] = [ 
						'text' => "§l§6逃走者になった回数のランキング", 
						'image' => [ 'type' => 'path', 'data' => "" ]  
						]; //4
						$buttons[] = [ 
						'text' => "§lハンターになった回数のランキング", 
						'image' => [ 'type' => 'path', 'data' => "" ]  
						]; //5
						$owner->sendForm($player,"逃走中","見たいランキングを選択してください\n",$buttons,3381);
        				$owner->info[$name] = "form";
						break;
					}
					elseif($data == 4) // SHOP
					{
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
						$owner->sendForm($player,"逃走中","購入したいアイテムを選択してね?\n",$buttons,4000);
        				$owner->info[$name] = "form";
						break;
					}
					
					case 3381:
					if($data == 0) //確保数
					{
						$name = $player->getName();
     					$form = [
     					"type" => "form",
     					"title" => "§6ランキング",
     					"content" => "=========§a確保回数§6ランキング§f=========",
     					"buttons" => array(),
     					];
     					$count = 1; // PJZ9nさんのコード使わせてもらいました、ありがとうございます！
     					$all_data = $owner->kk->getAll();
     					arsort($all_data);
     					foreach ($all_data as $key => $value)
     					{
     						$color = "§l§f";
     						if($count === 1)
     						{
     							$color = "§l§e";
     						}
     						elseif($count === 2)
     						{
     							$color = "§l§7";
     						}
     						elseif($count === 3)
     						{
     							$color = "§l§6";
     						}
     						
     						if($key == $name)
     						{
     							$form["content"] .= "\n{$color}{$count}§r. §l§a{$key}§r: §b{$value}";
     							$count++;
     						}
     						else
     						{
     							$form["content"] .= "\n{$color}{$count}§r. §l§f{$key}§r: §b{$value}";
     							$count++;
     						}
     					}
     					
     					$owner->createWindow($player, $form, 73152891);
     					break;
				
						}
						elseif($data == 1) //逃走成功回数
						{
							$name = $player->getName();
     					$form = [
     					"type" => "form",
     					"title" => "§7ランキング",
     					"content" => "=======§a逃走成功回数§6ランキング§f=======",
     					"buttons" => array(),
     					];
     					$count = 1; // PJZ9nさんのコード使わせてもらいました、ありがとうございます！
     					$all_data = $owner->nige->getAll();
     					arsort($all_data);
     					foreach ($all_data as $key => $value)
     					{
     						$color = "§l§f";
     						if($count === 1)
     						{
     							$color = "§l§e";
     						}
     						elseif($count === 2)
     						{
     							$color = "§l§7";
     						}
     						elseif($count === 3)
     						{
     							$color = "§l§6";
     						}
     						
     						if($key == $name)
     						{
     							$form["content"] .= "\n{$color}{$count}§r. §l§a{$key}§r: §b{$value}";
     							$count++;
     						}
     						else
     						{
     							$form["content"] .= "\n{$color}{$count}§r. §l§f{$key}§r: §b{$value}";
     							$count++;
     						}
     					}
     					
     					$owner->createWindow($player, $form, 78362891);
     					break;
					
						}
						elseif($data == 2) //捕まった回数
						{
							$name = $player->getName();
     						$form = [
     						"type" => "form",
     						"title" => "§7ランキング",
     						"content" => "=======§a捕まった回数§6ランキング§f=======",
     						"buttons" => array(),
     						];
     						$count = 1; // PJZ9nさんのコード使わせてもらいました、ありがとうございます！
     						$all_data = $owner->kkb->getAll();
     						arsort($all_data);
     						foreach ($all_data as $key => $value)
     						{
     							$color = "§l§f";
     							if($count === 1)
     							{
     								$color = "§l§e";
     							}
     							elseif($count === 2)
     							{
     								$color = "§l§7";
     							}
     							elseif($count === 3)
     							{
     								$color = "§l§6";
     							}
     							
     							if($key == $name)
     							{
     								$form["content"] .= "\n{$color}{$count}§r. §l§a{$key}§r: §b{$value}";
     								$count++;
     							}
     							else
     							{
     								$form["content"] .= "\n{$color}{$count}§r. §l§f{$key}§r: §b{$value}";
     								$count++;
     							}
     						}
     						
     						$owner->createWindow($player, $form, 71253);
     						break;
						}
						elseif($data == 3) //参加回数
						{
							$name = $player->getName();
     						$form = [
     						"type" => "form",
     						"title" => "§7ランキング",
     						"content" => "=======§a参加回数§6ランキング§f=======",
     						"buttons" => array(),
     						];
     						$count = 1; // PJZ9nさんのコード使わせてもらいました、ありがとうございます！
     						$all_data = $owner->join->getAll();
     						arsort($all_data);
     						foreach ($all_data as $key => $value)
     						{
     							$color = "§l§f";
     							if($count === 1)
     							{
     								$color = "§l§e";
     							}
     							elseif($count === 2)
     							{
     								$color = "§l§7";
     							}
     							elseif($count === 3)
     							{
     								$color = "§l§6";
     							}
     							
     							if($key == $name)
     							{
     								$form["content"] .= "\n{$color}{$count}§r. §l§a{$key}§r: §b{$value}";
     								$count++;
     							}
     							else
     							{
     								$form["content"] .= "\n{$color}{$count}§r. §l§f{$key}§r: §b{$value}";
     								$count++;
     							}
     						}
     						
     						$owner->createWindow($player, $form, 71);
     						break;
						}
						elseif($data == 4) //逃走者になった回数
						{
							$name = $player->getName();
     						$form = [
     						"type" => "form",
     						"title" => "§7ランキング",
     						"content" => "=======§a逃走者になった回数§6ランキング§f=======",
     						"buttons" => array(),
     						];
     						$count = 1; // PJZ9nさんのコード使わせてもらいました、ありがとうございます！
     						$all_data = $owner->runnerc->getAll();
     						arsort($all_data);
     						foreach ($all_data as $key => $value)
     						{
     							$color = "§l§f";
     							if($count === 1)
     							{
     								$color = "§l§e";
     							}
     							elseif($count === 2)
     							{
     								$color = "§l§7";
     							}
     							elseif($count === 3)
     							{
     								$color = "§l§6";
     							}
     							
     							if($key == $name)
     							{
     								$form["content"] .= "\n{$color}{$count}§r. §l§a{$key}§r: §b{$value}";
     								$count++;
     							}
     							else
     							{
     								$form["content"] .= "\n{$color}{$count}§r. §l§f{$key}§r: §b{$value}";
     								$count++;
     							}
     						}
     						
     						$owner->createWindow($player, $form, 783);
     						break;
						}
						elseif($data == 5) //ハンターになった回数
						{
							$name = $player->getName();
     						$form = [
     						"type" => "form",
     						"title" => "§7ランキング",
     						"content" => "=======§aハンターになった回数§6ランキング§f=======",
     						"buttons" => array(),
     						];
     						$count = 1; // PJZ9nさんのコード使わせてもらいました、ありがとうございます！
     						$all_data = $owner->hunterc->getAll();
     						arsort($all_data);
     						foreach ($all_data as $key => $value)
     						{
     							$color = "§l§f";
     							if($count === 1)
     							{
     								$color = "§l§e";
     							}
     							elseif($count === 2)
     							{
     								$color = "§l§7";
     							}
     							elseif($count === 3)
     							{
     								$color = "§l§6";
     							}
     							
     							if($key == $name)
     							{
     								$form["content"] .= "\n{$color}{$count}§r. §l§a{$key}§r: §b{$value}";
     								$count++;
     							}
     							else
     							{
     								$form["content"] .= "\n{$color}{$count}§r. §l§f{$key}§r: §b{$value}";
     								$count++;
     							}
     						}
     						
     						$owner->createWindow($player, $form, 78362891);
     						break;
						}
					
					case 4000:
					switch($data)
					{
						case 0: //スピードダウン
						$price = $weapon["HunterSpeedDown"]["Price"];
						$data = [
						"type" => "custom_form",
						"title" => "アイテム購入",
						"content" => [
						[
						"type" => "label",
						"text" => "§l§7ハンターのスピードダウン§f1つ §l{$price}§r§b円"
						],
						[
						"type" => "label",
						"text" => "§l§f使い方:\n§r§fアイテムを持ってハンターにタッチしよう！"
						],
						[
						"type" => "input",
						"text" => "§l§b買いたい個数を入力してください！",
						"placeholder" => "数字を入力",
						"default" => ""
						]
					]
				];
					$owner->createWindow($player, $data, 4001);
					break;
					
					case 1: //スピードアップ
					$price = $weapon["SpeedUp"]["Price"];
						$data = [
						"type" => "custom_form",
						"title" => "アイテム購入",
						"content" => [
						[
						"type" => "label",
						"text" => "§l§bスピードアップ§f1つ §l{$price}§r§b円"
						],
						[
						"type" => "label",
						"text" => "§l§f使い方:\n§r§fアイテムを持って地面をタッチしよう!"
						],
						[
						"type" => "input",
						"text" => "§l§b買いたい個数を入力してください！",
						"placeholder" => "数字を入力",
						"default" => ""
						]
					]
				];
					$owner->createWindow($player, $data, 4002);
					break;
					
					case 2: //ハイジャンプ
					$price = $weapon["HighJump"]["Price"];
						$data = [
						"type" => "custom_form",
						"title" => "アイテム購入",
						"content" => [
						[
						"type" => "label",
						"text" => "§l§aハイジャンプ§f1つ §l{$price}§r§b円"
						],
						[
						"type" => "label",
						"text" => "§l§f使い方:\n§r§fアイテムを持って地面をタッチしよう！"
						],
						[
						"type" => "input",
						"text" => "§l§b買いたい個数を入力してください！",
						"placeholder" => "数字を入力",
						"default" => ""
						]
					]
				];
					$owner->createWindow($player, $data, 4003);
					break;
					
					case 3: //透明
					$price = $weapon["Invisible"]["Price"];
						$data = [
						"type" => "custom_form",
						"title" => "アイテム購入",
						"content" => [
						[
						"type" => "label",
						"text" => "§l§7透明§f1つ §l{$price}§r§b円"
						],
						[
						"type" => "label",
						"text" => "§l§f使い方:\n§r§fアイテムを持って地面をタッチしよう！"
						],
						[
						"type" => "input",
						"text" => "§l§b買いたい個数を入力してください！",
						"placeholder" => "数字を入力",
						"default" => ""
						]
					]
				];
					$owner->createWindow($player, $data, 4004);
					break;
					
					case 4: //復活
					$price = $weapon["Revival"]["Price"];
						$data = [
						"type" => "custom_form",
						"title" => "アイテム購入",
						"content" => [
						[
						"type" => "label",
						"text" => "§l§6復活アイテム§f1つ §l{$price}§r§b円"
						],
						[
						"type" => "label",
						"text" => "§l§f使い方:\n§r§f牢屋の中でアイテムを持って地面をタッチしよう！"
						],
						[
						"type" => "input",
						"text" => "§l§b買いたい個数を入力してください！",
						"placeholder" => "数字を入力",
						"default" => ""
						]
					]
				];
					$owner->createWindow($player, $data, 4005);
					break;
				
					default:
					$player->sendMessage("§l§aMessage>>§r §c不正なパケットを検知しました");
					break;
				}
				break;
				
				/*case 4001: //スピードダウン確認
					$money = $owner->getMoney($name);
					$sell = $weapon["HunterSpeedDown"]["Price"];
					$id = $weapon["HunterSpeedDown"]["id"];
					$iname = $weapon["HunterSpeedDown"]["Name"];
					
					$price = $sell * $result[2];
					
					$shop[$name] = $result[2];
					var_dump($shop[$name]);
					
					$buttons[] = [ 
					'text' => "はい", 
					'image' => [ 'type' => 'path', 'data' => "" ]  
					]; //0
					$buttons[] = [ 
					'text' => "いいえ", 
					'image' => [ 'type' => 'path', 'data' => "" ]  
					]; //1
					$owner->sendForm($player,"§lSHOP","§a§ｌアイテム名§r:ハンタースピードダウン\n§l§a個数§r:{$result[2]}\n§a§l金額§r:{$price}円\n§a§l所持金§r:{$money}\n購入しますか?",$buttons,40010);
        			$owner->info[$name] = "form";
					break;*/
				
				
				case 4001: //スピードダウン購入
				if($data == 0)
				{
					$money = $owner->getMoney($name);
					$sell = $weapon["HunterSpeedDown"]["Price"];
					$id = $weapon["HunterSpeedDown"]["id"];
					$iname = $weapon["HunterSpeedDown"]["Name"];
					
					$price = $sell * $result[2];
					
					if($money < $price)
					{
						$non = $price - $money;
						$player->sendMessage("§l§aMessage>>§r §cあなたの所持金では§l§r{$non}§r§c円足りません...");
						return true;
					}
					else
					{
						$owner->cutMoney($name, $price);
						$player->sendMessage("§l§aMessage>>§r §eスピードダウンアイテムを§d{$result[2]}個§e購入しました");
						$item = Item::get($id,0,$result[2]);
						$item->setCustomName($iname);
						$player->getInventory()->addItem($item);
						break;
					}
				}
				break;
				
				/*case 4002: //スピードアップ確認
				$money = $owner->getMoney($name);
				$sell = $weapon["SpeedUp"]["Price"];
				$id = $weapon["SpeedUp"]["id"];
				$iname = $weapon["SpeedUp"]["Name"];
				
				$price = $sell * $result[2];
				
				$shop[$name] = $result[2];
				
				$buttons[] = [ 
				'text' => "はい", 
				'image' => [ 'type' => 'path', 'data' => "" ]  
				]; //0
				$buttons[] = [ 
				'text' => "いいえ", 
				'image' => [ 'type' => 'path', 'data' => "" ]  
				]; //1
				$owner->sendForm($player,"§lSHOP","§a§ｌアイテム名§r:スピードアップ\n§l§a個数§r:{$result[2]}\n§a§l金額§r:{$price}円\n§a§l所持金§r:{$money}\n購入しますか?",$buttons,40020);
        		$owner->info[$name] = "form";
				break;*/
				
				case 4002: //スピードあぷう
				if($data ==  0)
				{
					$money = $owner->getMoney($name);
					$sell = $weapon["SpeedUp"]["Price"];
					$id = $weapon["SpeedUp"]["id"];
					$iname = $weapon["SpeedUp"]["Name"];
					
					$price = $sell * $result[2];
					
					if($money < $price)
					{
						$non = $price - $money;
						$player->sendMessage("§l§aMessage>>§r §cあなたの所持金では§l§r{$non}§r§c円足りません...");
						return true;
					}
					else
					{
						$owner->cutMoney($name, $price);
						$player->sendMessage("§l§aMessage>>§r §eスピードアップアイテムを§d{$result[2]}個§e購入しました");
						$item = Item::get($id,0,$result[2]);
						$item->setCustomName($iname);
						$player->getInventory()->addItem($item);
						break;
					}
				}
				
				break;
				
				/*case 4003: //ハイジャンプ確認
				$money = $owner->getMoney($name);
				$sell = $weapon["HighJump"]["Price"];
				$id = $weapon["HighJump"]["id"];
				$iname = $weapon["HighJump"]["Name"];
				
				$price = $sell * $result[2];
				
				$shop[$name] = $result[2];
				
				$buttons[] = [ 
				'text' => "はい", 
				'image' => [ 'type' => 'path', 'data' => "" ]  
				]; //0
				$buttons[] = [ 
				'text' => "いいえ", 
				'image' => [ 'type' => 'path', 'data' => "" ]  
				]; //1
				$owner->sendForm($player,"§lSHOP","§a§ｌアイテム名§r:ハイジャンプ\n§l§a個数§r:{$result[2]}\n§a§l金額§r:{$price}円\n§a§l所持金§r:{$money}\n購入しますか?",$buttons,40010);
        		$owner->info[$name] = "form";
				break;*/
				
				case 4003: //ハイジャンプう
				if($data == 0)
				{
					$money = $owner->getMoney($name);
					$sell = $weapon["HighJump"]["Price"];
					$id = $weapon["HighJump"]["id"];
					$iname = $weapon["HighJump"]["Name"];
					
					$price = $result[2];
					
					if($money < $price)
					{
						$non = $price - $money;
						$player->sendMessage("§l§aMessage>>§r §cあなたの所持金では§l§r{$non}§r§c円足りません...");
						return true;
					}
					else
					{
						$owner->cutMoney($name, $price);
						$player->sendMessage("§l§aMessage>>§r §eハイジャンプアイテムを§d{$result[2]}個§e購入しました");
						$item = Item::get($id,0,$result[2]);
						$item->setCustomName($iname);
						$player->getInventory()->addItem($item);
						break;
					}
				}
				break;
				
				/*case 4004: //透明確認
				$money = $owner->getMoney($name);
				$sell = $weapon["Invisible"]["Price"];
				$id = $weapon["Invisible"]["id"];
				$iname = $weapon["Invisible"]["Name"];
				
				$price = $sell * $result[2];
				
				$shop[$name] = $result[2];
				
				$buttons[] = [ 
				'text' => "はい", 
				'image' => [ 'type' => 'path', 'data' => "" ]  
				]; //0
				$buttons[] = [ 
				'text' => "いいえ", 
				'image' => [ 'type' => 'path', 'data' => "" ]  
				]; //1
				$owner->sendForm($player,"§lSHOP","§a§ｌアイテム名§r:透明\n§l§a個数§r:{$result[2]}\n§a§l金額§r:{$price}円\n§a§l所持金§r:{$money}\n購入しますか?",$buttons,40010);
        		$owner->info[$name] = "form";
				break;*/
				
				case 4004: //透明ぃ
				if($data == 0)
				{
					$money = $owner->getMoney($name);
					$sell = $weapon["Invisible"]["Price"];
					$id = $weapon["Invisible"]["id"];
					$iname = $weapon["Invisible"]["Name"];
					
					$price = $sell * $result[2];
					
					if($money < $price)
					{
						$non = $price - $money;
						$player->sendMessage("§l§aMessage>>§r §cあなたの所持金では§l§r{$non}§r§c円足りません...");
						return true;
					}
					else
					{
						$owner->cutMoney($name, $price);
						$player->sendMessage("§l§aMessage>>§r §e透明アイテムを§d{$result[2]}個§e購入しました");
						$item = Item::get($id,0,$result[2]);
						$item->setCustomName($iname);
						$player->getInventory()->addItem($item);
						break;
					}
				}
				break;
				
				/*case 4005: //復活確認
				$money = $owner->getMoney($name);
				$sell = $weapon["Revival"]["Price"];
				$id = $weapon["Revival"]["id"];
				$iname = $weapon["Revival"]["Name"];
				
				$price = $sell * $result[2];
				
				$shop[$name] = $result[2];
				
				$buttons[] = [ 
				'text' => "はい", 
				'image' => [ 'type' => 'path', 'data' => "" ]  
				]; //0
				$buttons[] = [ 
				'text' => "いいえ", 
				'image' => [ 'type' => 'path', 'data' => "" ]  
				]; //1
				$owner->sendForm($player,"§lSHOP","§a§ｌアイテム名§r:復活\n§l§a個数§r:{$result[2]}\n§a§l金額§r:{$price}円\n§a§l所持金§r:{$money}\n購入しますか?",$buttons,40010);
        		$owner->info[$name] = "form";
				break;*/
				
				case 4005: //復活ぅ
				if($data == 0)
				{
					$money = $owner->getMoney($name);
					$sell = $weapon["Revival"]["Price"];
					$id = $weapon["Revival"]["id"];
					$iname = $weapon["Revival"]["Name"];
					
					$price = $sell * $result[2];
					
					if($money < $price)
					{
						$non = $price - $money;
						$player->sendMessage("§l§aMessage>>§r §cあなたの所持金では§l§r{$non}§r§c円足りません...");
						return true;
					}
					else
					{
						$owner->cutMoney($name, $price);
						$player->sendMessage("§l§aMessage>>§r §e復活アイテムを§d{$result[2]}個§e購入しました");
						$item = Item::get($id,0,$result[2]);
						$item->setCustomName($iname);
						$player->getInventory()->addItem($item);
						break;
					}
						
				}
				break;
     		}	
     	}
    }
  
}
}