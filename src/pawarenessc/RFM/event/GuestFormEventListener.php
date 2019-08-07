<?php

namespace pawarenessc\RFM\event;

use pocketmine\event\Listener;

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

use pawarenessc\RFM\task\StartTask;

class GuestFormEventListener implements Listener
{
		
		public function __construct($owner)
		{
				$this->owner = $owner;
		}
    	
    	public function onPrecessing(DataPacketReceiveEvent $event)
      {}
}
