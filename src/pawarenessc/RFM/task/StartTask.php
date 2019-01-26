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
				if($data["MAP2"]["Ready(ok or no)"] == "ok")
				{
					$owner->map = mt_rand(1,2);
					$owner->msg("§l§bINFO>>§r §a今回はマップ§c§l{$this->owner->map}§r§a!");
				}
				else
				{
					$owner->map = 1;
					$owner->msg("§l§bINFO>>§r §a今回はマップ§c§l1§r§a!");
				}
			}
			
			if($this->owner->wt == 1)
			{
					$owner->game = true;
			}
		}
	
	}
}
	
