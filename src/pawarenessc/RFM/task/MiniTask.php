<?php
namespace pawarenessc\RFM\task;

use pocketmine\scheduler\Task;
use pocketmine\scheduler\TaskScheduler;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;

class MiniTask extends Task
{
	private $player;
  
  private $pel;
  
  public function __construct(PlayerEventListener $pel, Player $player)
	{
		$this->pel = $pel;
    $this->player = $player;
	}
	
	public function onRun(int $ticks)
	{
		$player = $this->player;
		$player->setImmobile(false);
	}
}
