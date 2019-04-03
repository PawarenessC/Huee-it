<?php

namespace pawarenessc\HI;

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

use pawarenessc\HI\event\PlayerEventListener;
use pawarenessc\HI\event\FormEventListener;

use pawarenessc\HI\task\StartTask;
use pawarenessc\HI\task\GameTask;
use pawarenessc\HI\command\TagCommand;

use MixCoinSystem\MixCoinSystem;
use metowa1227\moneysystem\api\core\API;

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
    	public $gametime;
    	public $wt;
	
		public function onEnable()
    	{
    		$this->getLogger()->info("=========================");
 			$this->getLogger()->info("Huee-itを読み込みました");
 			$this->getLogger()->info("制作者: PawarenessC");
 			$this->getLogger()->info("ライセンス: NYSL Version 0.9982");
 			$this->getLogger()->info("http://www.kmonos.net/nysl/");
 			$this->getLogger()->info($this->getDescription()->getVersion());
 			$this->getLogger()->info("");
 			$this->getLogger()->info("最終更新:2019/04/04");
 			$this->getLogger()->info("=========================");
    		
    			$this->Event();
    			$this->Config();
			$this->system = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
    			$this->saveDefaultConfig();
        		$this->reloadConfig();
        		
			$this->h = 0; // 鬼
    			$this->t = 0; // 逃走者
    			$this->all = $this->h + $this->t; // 鬼、逃走者合わせて
    		
    			$this->game = false; //ゲームの状態
    			$this->win = 0; // 賞金
    			$this->cogame = false;
    		
    		
    			$this->map = 1;
    		
    			$this->gametime = $this->config->get("GameTime");
 			$this->wt = $this->config->get("WaitTime");
 			
 			$this->getScheduler()->scheduleRepeatingTask(new GameTask($this, $this), 20);
  			$this->getScheduler()->scheduleRepeatingTask(new StartTask($this, $this), 20);
			
			if($this->getServer()->getPluginManager()->getPlugin("RunForMoney") !== NULL){
          		$this->getLogger()->error("RunForMoneyを同時に使うことはできません。");
			$this->getLogger()->error("プラグインを無効化しています...");
          		$this->getServer()->getPluginManager()->disablePlugin($this);
        		}
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
		$p = $this->getServer()->getPlayer($name);
 		
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
 			API::getInstance()->increase($p, $money, "win", "RunForMoney");
 		}
 		
 		if($plugin == "MoneyPlugin")
 		{
 			$this->getServer()->getPluginManager()->getPlugin("MoneyPlugin")->addmoney($name,$money);
 		}
 	}
 	
 	public function getMoney($name)
 	{
 		$plugin = $this->config->get("Plugin");
		$p = $this->getServer()->getPlayer($name);
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
 			return API::getInstance()->get($p);
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
        	
         $this->xyz = new Config($this->getDataFolder() . "xyz.yml", Config::YAML);
         $this->config = new Config($this->getDataFolder()."Setup.yml", Config::YAML);
    }
 	public function startMenu($player) 
  	{
		$tanka = $this->config->get("UnitPrice");
		$wti   = $this->config->get("WaitTime");
		$gti   = $this->config->get("GameTime");
        $name = $player->getName();
        $buttons[] = [ 
        'text' => "§l§6増え鬼を始める", 
        'image' => [ 'type' => 'path', 'data' => "" ] 
        ]; //0
        $buttons[] = [ 
        'text' => "§l§4増え鬼を終了する", 
        'image' => [ 'type' => 'path', 'data' => "" ] 
        ]; //1
        $buttons[] = [ 
        'text' => "§l§cプラグインを止める", 
        'image' => [ 'type' => 'path', 'data' => "" ] 
        ]; //2
        $buttons[] = [ 
        'text' => "§l§3増え鬼の設定", 
        'image' => [ 'type' => 'path', 'data' => "" ] 
        ]; //3
        $buttons[] = [
        "text" => "§l§2座標の設定",
        "image" => [ "type" => "path", "data" => "" ] 
        ]; //4
        $buttons[] = [ 
        'text' => "§l設定の更新", 
        'image' => [ 'type' => 'path', 'data' => "" ] 
        ]; //5
        /*$buttons[] = [ 
        'text' => "§l§7Debug", 
        'image' => [ 'type' => 'path', 'data' => "" ] 
        ]; //9*/
        $this->sendForm($player,"SETUP","増え鬼のセットアップのFormです\n\n\n§6--現在の設定--\n単価:§b{$tanka}円\n§f待機時間:§e{$wti}秒\n§fゲーム時間:§d{$gti}秒\n\n\n",$buttons,2);
        $this->info[$name] = "form";
  }
  
  public function tagMenu($player)
  {
        $name = $player->getName();
        $buttons[] = [ 
        'text' => "§l§b増え鬼に参加する", 
        'image' => [ 'type' => 'path', 'data' => "" ] 
        ]; //0
        $buttons[] = [ 
        'text' => "§l§c増え鬼から抜ける", 
        'image' => [ 'type' => 'path', 'data' => "" ] 
        ]; //1
        $buttons[] = [ 
        'text' => "§l§eステータスを確認する", 
        'image' => [ 'type' => 'path', 'data' => "" ] 
        ]; //2
        $this->sendForm($player,"§l§7増   え   鬼","§a選択してください",$buttons,1145145);
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


