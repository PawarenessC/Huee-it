<?php
namespace pawarenessc\HI\task;

use pocketmine\scheduler\Task;
use pocketmine\scheduler\TaskScheduler;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\Server;

use pawarenessc\HI\Main;
use pawarenessc\Hi\task\GameTask;

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
			$owner->Popup("§f残り時間:§l§f{$minutes}§r§b:§r§f§l{$seconds}§r§e \n§r賞金  §d".$win."§b円§r\n     §l§a逃走者 ".$t." §cvs §b鬼 ".$h."\n\n\n\n");
			
			if($t == 0 or $t < 0)
			{
				$owner->msg("§l§bINFO>>§r §a逃走者が全滅しました！");
				$owner->msg("§l§bINFO>>§r §b鬼の勝利です！");
				$owner->msg("§l§bINFO>>§r §c鬼は§6{$win}§b円の賞金を手にいれた！");
  				$owner->endGame();
			}
			
			if($h == 0 or $h < 0)
			{
				$owner->msg("§l§bINFO>>§r §c鬼が全滅しました");
  				$owner->endGame();
			}
			
			switch($owner->gametime)
			{
				case $truegame:
				if(1 >= $all)
				{
					$owner->msg("§l§bINFO>>§r §c増え鬼開始には2人以上必要です。");
					$owner->msg("§l§bINFO>>§r §cゲームを終了しました");
  					$owner->game = false;
					$owner->endGame();
					break;
				}
				else
				{
					$owner->msg("§l§bINFO>>§r §b増え鬼を開始しました！！鬼は不思議なパーティクルを身に着けているよ！");
					$owner->msg("§l§bINFO>>§r §a鬼は10秒間動けません");
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
							$player->sendMessage("§l§aMessage>> §r§c鬼になりました!");
						}
						else
						{
							$xyz = new Vector3($data["MAP2"]["Hunter"]["x"], $data["MAP2"]["Hunter"]["y"], $data["MAP2"]["Hunter"]["z"], $data["MAP2"]["world"]);
	      					$player->teleport($xyz);
							$player->setImmobile(true);
							$player->addEffect(new EffectInstance(Effect::getEffect(1), 114514, 1, false));
							$player->sendMessage("§l§aMessage>> §r§c鬼になりました!");
						}
					}
				}
				}
				break;
			
				case $huntermove:
			
				foreach ($players as $player)
  				{
  					$player->setImmobile(false);
  				}
  			
  				$owner->msg("§l§bINFO>>§r §c鬼が動けるようになりました");
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
  					}
  				}
  					$owner->msg("§l§bINFO>>§r §b逃げ切った者達にはには§6{$win}§b円の賞金が手に入るぞ");
  					$owner->endGame();
  			}
  		}
  	}
  		
}
