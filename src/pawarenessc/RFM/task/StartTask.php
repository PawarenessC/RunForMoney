<?php
namespace pawarenessc\RFM\task;

use pocketmine\scheduler\Task;
use pocketmine\scheduler\TaskScheduler;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\Server;

use pawarenessc\RFM\Main;
use pawarenessc\RFM\task\GameTask;

use pocketmine\utils\Config;

class StartTask extends Task
{
	public function __construct($owner)
	{
		$this->owner = $owner;
	}
	
	public function onRun(int $ticks)
	{
		$owner = $this->owner;
		if($owner->game == false && $owner->cogame == true)
		{
			$owner->wt--;
			$data = $owner->xyz->getAll();
			$all = $owner->t + $owner->h;
			$owner->Popup("§l{$this->owner->wt}§r§a秒後に開始 §r§f§l{$all}§r§e人が参加");
			
			if($owner->wt == 10)
			{
				$map_name1 = $data["MAP1"]["Name"];
				$map_name2 = $data["MAP2"]["Name"];
				$map_name3 = $data["MAP3"]["Name"];
				$map_about1 = $data["MAP1"]["About"];
				$map_about2 = $data["MAP2"]["About"];
				$map_about3 = $data["MAP3"]["About"];
				
				if($data["MAP2"]["Ready(ok or no)"] == "ok")
				{
					if($data["MAP3"]["Ready(ok or no)"] !== "ok")
					{
						$owner->map = mt_rand(1,2);
						if($owner->map == 1){
						$owner->msg("§l§bINFO>>§r §a今回はマップは、§e§l{$map_name1}§r§a！");
						$owner->msg("§l§bINFO>>§r {$map_about1}");
						}
						
						if($owner->map == 2){
						$owner->msg("§l§bINFO>>§r §a今回はマップは、§e§l{$map_name2}§r§a！");
						$owner->msg("§l§bINFO>>§r {$map_about1}");	
						}
						
					}
					else
					{
						$owner->map = mt_rand(1,3);
						if($owner->map == 1){
						$owner->msg("§l§bINFO>>§r §a今回はマップは、§e§l{$map_name1}§r§a！");
						$owner->msg("§l§bINFO>>§r {$map_about1}");
						}
						
						if($owner->map == 2){
						$owner->msg("§l§bINFO>>§r §a今回はマップは、§e§l{$map_name2}§r§a！");
						$owner->msg("§l§bINFO>>§r {$map_about1}");	
						}
						
						if($owner->map == 3){
						$owner->msg("§l§bINFO>>§r §a今回はマップは、§e§l{$map_name3}§r§a！");
						$owner->msg("§l§bINFO>>§r {$map_about3}");	
						}
					}
				}else
				{
					$owner->map = 1;
					$owner->msg("§l§bINFO>>§r §a今回はマップは、§e§l{$map_name1}§r§a！");
					$owner->msg("§l§bINFO>>§r {$map_about1}");
				}
			}
			
			if($this->owner->wt == 1)
			{
					$owner->game = true;
			}
		}
	
	}
}
	
