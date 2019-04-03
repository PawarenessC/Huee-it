<?php

namespace pawarenessc\HI\event;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerLoginEvent;
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

use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\level\particle\DustParticle;
use pocketmine\level\particle\DestroyBlockParticle;

use pocketmine\block\Block;

use pocketmine\math\Vector3;

use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;

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
			$this->owner->type[$name] = 4;
			$player->setAllowMovementCheats(true);
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
   				$owner->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}さんが増え鬼を抜けました");
			}
  			
  			if($this->owner->type[$name] == 2)
  			{
  				$this->owner->h--;
  				$owner->getServer()->broadcastMessage("§l§bINFO>>§r §c{$name}さんが増え鬼を抜けました");
  			}
  		}
  	
  		public function onMove(PlayerMoveEvent $event)
  		{
  			$player = $event->getPlayer();
  			$name = $player->getName();
  			
  			if($this->owner->type[$name] == 2 && $this->owner->game) // 鬼だったら
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
		
		public function EntityDamageEvent(EntityDamageEvent $event)
		{
			$data  = $this->owner->xyz->getAll()["MAP1"];
			$data2 = $this->owner->xyz->getAll()["MAP2"];
			
			
			$map = $this->owner->map;
			
			if($event instanceof EntityDamageByEntityEvent)
			{
				$entity = $event->getEntity();
				$player = $event->getDamager();
				
				
				$hunter = $player->getName();
				$runner = $entity->getName();
				
				if($this->owner->type[$hunter] == 2 && $this->owner->type[$runner] == 1 && $this->owner->game)
				{
					if($this->owner->addh !== true)
					{
						$kakuho = $this->owner->config->get("Reward");
						$player->sendMessage("§l§aMessage>>§r §b確保報酬として§6{$kakuho}§b円を手に入れた！");
						$this->owner->addMoney($kakuho ,$hunter);
	  				
						$entity->sendMessage("§l§aMessage>>§r §c{$hunter}§4に捕まった...");
	  					$entity->sendMessage("§l§aMessage>>§r §b鬼になったぞ！");
	  					$entity->addTitle("§c捕まりました...", "");
	  					
	  					//$team = "jaller"; なんか作動しない
						//$this->owner->team($player, $team);
						$this->owner->type[$runner] = 2;
	  					$this->owner->t--;
	  					$this->owner->h++;
	  					$this->owner->getServer()->broadcastMessage("§l§bINFO>>§r §e{$runner}§cが確保された...");
	  					$this->owner->getServer()->broadcastMessage("§l§bINFO>>§r §c鬼→ §f{$hunter}");
	  					
	  					$this->owner->getServer()->broadcastMessage("§l§bINFO>>§r 鬼 §6{$this->owner->h}人");
	  					$this->owner->getServer()->broadcastMessage("§l§bINFO>>§r 残り逃走者 §6{$this->owner->t}人");
	  				}
	  			}
	  			
			}
		}
  
}
