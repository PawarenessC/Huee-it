<?php

namespace pawarenessc\HI\event;

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

use pawarenessc\HI\task\StartTask;

class FormEventListener implements Listener
{
		
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
  	
  			if($pk->getName() == "ModalFormResponsePacket")
  			{
  				$data = $pk->formData;
  				$result = json_decode($data);
  		
  				if($data !== "null\n")
  				{
  			 		switch($pk->formId)
  			 		{
  			 			case 2:
  			 			switch($data)
  			 			{
  			 				case 0:
  			 				if(!$owner->game && !$owner->cogame)
  			 				{
  			 					$owner->getScheduler()->scheduleRepeatingTask(new StartTask($owner, $owner), 20);
  			 					$owner->game = false;
  			 					$owner->cogame = true;
       							$owner->getServer()->broadcastMessage("§l§bINFO>>§r §b増え鬼を開催します！ /hiで参加しましょう！");
       							break;
       						}
       						else
       						{
       							$player->sendMessage("§l§aMessage>>§r §c既に開催されています");
       						}
       						break;
       						
       						case 1:
       						$owner->endGame();
       						$owner->getServer()->broadcastMessage("§l§bINFO>>§r §c権限者によって増え鬼が終了しました");
       						break;
       						
       						case 2:
       						$buttons[] = [ 
       						'text' => "はい", 
       						]; //0
       						$buttons[] = [ 
       						'text' => "いいえ", 
       						];
       						$owner->sendForm($player,"DisablePlugin","本当にプラグインを停止しますか？\n\n",$buttons,19198101);
							break;
						}
							
							case 3:
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
							
							case 4:
							$buttons[] = [ 
							'text' => "§l§3マップ1の座標指定", 
							'image' => [ 'type' => 'path', 'data' => "" ]  
							]; //0
							$buttons[] = [ 
							'text' => "§l§1マップ2の座標指定", 
							'image' => [ 'type' => 'path', 'data' => "" ]  
							]; //1
							$owner->sendForm($player,"増え鬼座標の設定","指定したい設定ボタンを押すと現在の座標が登録されます。\n",$buttons,467389);
        					$owner->info[$name] = "form";
							break;
							
							case 5:
							if(!$owner->game){
							$owner->ReloadGame();
							$player->sendMessage("§l§aMessage>>§r 設定を更新しました");
							break;
						}else{
							$player->sendMessage("§l§aMessage>>§r §cゲーム中です、ゲームが終わってから更新ボタンを押してください");
							break;
							}
							
							case 6: //Debug
							$buttons[] = [ 
        					'text' => "逃走者", 
        					'image' => [ 'type' => 'path', 'data' => "" ] 
        					]; //0
        					$buttons[] = [ 
        					'text' => "鬼", 
        					'image' => [ 'type' => 'path', 'data' => "" ] 
        					]; //1
        					$buttons[] = [ 
        					'text' => "牢屋", 
        					'image' => [ 'type' => 'path', 'data' => "" ] 
        					]; //2
        					$owner->sendForm($player,"Debug Menu","どうやってこの画面を見たんだ。\n",$buttons, 188711);
        					$owner->info[$name] = "form";
							break;
				
					case 19198101:
					if($data == 0)
					{//無効化 はい
						$owner->getServer()->getPluginManager()->disablePlugin($owner);
						$player->sendMessage("§l§aMessage>>§r §a増え鬼プラグインを無効化しました、再起動、またはリロードをすれば再読み込みすることができます。");
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
							$player->sendMessage("§a増え鬼の単価を§d".$tanka."§aに更新しました");
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
								$player->sendMessage("§l§aMessage>>§r §a増え鬼の待機時間を§b".$wtime."§a秒に更新しました");
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
								$player->sendMessage("§l§aMessage>>§r §a増え鬼のゲーム時間を§e".$gtime."§a秒に更新しました");
								break;
							}
						}
						else
						{
							$player->sendMessage("§l§bINFO>>§r §cゲーム中なので変更はできません (ゲーム時間) (待機時間)");
							break;
						}
				
					//疲れた、↓からはインデントの意識0。また今度するから許して
					case 467389:
					if($data == 0)
					{
						$buttons[] = [ 
						'text' => "§l§1逃走者のテレポ地点", 
						'image' => [ 'type' => 'path', 'data' => "" ]  
						]; //0
						$buttons[] = [ 
						'text' => "§l§2鬼のテレポ地点", 
						'image' => [ 'type' => 'path', 'data' => "" ]  
						]; //1
						$owner->sendForm($player,"増え鬼座標の設定","マップ1の指定したい設定ボタンを押すと現在の座標が登録されます。\n",$buttons,4091);
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
						'text' => "§l§2鬼のテレポ地点", 
						'image' => [ 'type' => 'path', 'data' => "" ]  
						]; //1
						$buttons[] = [ 
						'text' => "§l§6準備完了ボタン", 
						'image' => [ 'type' => 'path', 'data' => "" ]  
						]; //2
						$owner->sendForm($player,"増え鬼座標の設定","マップ2の指定したい設定ボタンを押すと現在の座標が登録されます。\n",$buttons,4092);
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
					$player->sendMessage("§l§aMessage>>§r マップ1の逃走者の座標を更新しました。\nX:{$x}\nY:{$y}\nZ:{$z}");
					
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
				elseif($data == 1) // 鬼
				{
					$player->sendMessage("§l§aMessage>>§r マップ1の鬼の座標を更新しました。\nX:{$x}\nY:{$y}\nZ:{$z}");
					
					$data = $owner->xyz->get("MAP1");
					$data["Hunter"]["x"] = $x;
					$data["Hunter"]["y"] = $y;
					$data["Hunter"]["z"] = $z;
					
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
					$player->sendMessage("§l§aMessage>>§r マップ2の逃走者の座標を更新しました。\nX:{$x}\nY:{$y}\nZ:{$z}");
					
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
				elseif($data == 1) // 鬼
				{
					$player->sendMessage("§l§aMessage>>§r マップ2の鬼の座標を更新しました。\nX:{$x}\nY:{$y}\nZ:{$z}");
					
					$data = $owner->xyz->get("MAP2");
					$data["Hunter"]["x"] = $x;
					$data["Hunter"]["y"] = $y;
					$data["Hunter"]["z"] = $z;
					
					$owner->xyz->set("MAP2", $data);
					$owner->xyz->save();
					break;
				}
				elseif($data == 2) //準備
				{
					$buttons[] = [ 
        			'text' => "§lできた！", 
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
					$player->sendMessage("§l§aMessage>>§r §a設定お疲れ様です！、次回の増え鬼から適用されます！");
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
				
				case 188711: //デバッグ
				switch($data)
				{
					case 0: //逃走者
					$owner->type[$name] = 1;
					$player->sendMessage("逃走者 ok");
					break;
					
					case 1: //鬼
					$owner->type[$name] = 2;
					$player->sendMessage("鬼 ok");
					break;
					
					case 2: //牢屋
					$owner->type[$name] = 3;
					$player->sendMessage("牢屋 ok");
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
  						if(!$owner->game)
  						{
  	 						if(!$owner->cogame)
  	 						{
  	  							if($all == 0)
  	  							{
  									$owner->game = false;
  									$owner->cogame = true;
									$owner->getServer()->broadcastMessage("§l§bINFO>>§r §b増え鬼を開催します！ /hiで参加しましょう！");
									
									$team = "runner";
									$owner->team($player, $team);
      								
      								$owner->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}さんが増え鬼に参加しました");
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
										$owner->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}さんが増え鬼に参加しました");
										
										}
										elseif($H < $T)
										{
											$team = "hunter";
											$owner->team($player, $team);
											$owner->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}さんが増え鬼に参加しました");
											
										}
										elseif($H === $T)
										{
											$team = 'runner';
											$owner->team($player, $team);
											$owner->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}さんが増え鬼に参加しました");
										}
								}
								else
								{
									$team = 'runner';
									$owner->team($player, $team);
									$owner->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}さんが増え鬼に参加しました");
								}
							}
						}
					}
  	
  					if($owner->game){ $player->sendMessage("§l§aMessage>>§r §r§b現在ゲーム中です"); } break; //ゲーム中だったら
   
    				}
    				elseif($data == 1)
    				{
    					$name = $player->getName();
 	       				$level = $owner->getServer()->getDefaultLevel();
       					$player->setGamemode(0);
     					
     					switch($owner->type[$name])
     					{
     						case 1:
     						$owner->type[$name] = 4;
							$player->sendMessage("§l§aMessage>>§r §c増え鬼を抜けました");
     						$owner->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}さんが増え鬼を抜けました");
	 						$owner->t--;
	 						$player->teleport($level->getSafeSpawn());
	 						break;
	 						
	 						case 2:
	 						$owner->type[$name] = 4;
							$player->sendMessage("§l§aMessage>>§r §c増え鬼を抜けました");
     						$owner->getServer()->broadcastMessage("§l§bINFO>>§r §c{$name}さんが増え鬼を抜けました");
     						$owner->h--;
	  						$player->teleport($level->getSafeSpawn());
     						break;
     						
     						case 3:
     						$owner->type[$name] = 4;
							$player->sendMessage("§l§aMessage>>§r §c増え鬼を抜けました");
     						$owner->getServer()->broadcastMessage("§l§bINFO>>§r §e{$name}さんが増え鬼を抜けました");
     						$player->teleport($level->getSafeSpawn()); 
     						break;
     						
     						case 4:
     						$player->sendMessage("§l§aMessage>>§r §c参加していないようです");
     						break;
     						
     						default:
     						$player->sendMessage("§l§aMessage>>§r §c参加していないようです");
     					}
     						
     						break;
     				
     				}
					
     		}
     	}
    }
  
}
}
