<?php

namespace pawarenessc\HI\command;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\Server;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

	class TagCommand
	{
    
		public function _construct(string $pg){
			$this->command = $pg;
		}
		
		public function onCommand(CommandSender $sender, Command $command, string $label, array $args, $main) :bool
		{
			switch($label)
				{
  					case "hiset":
 					if($sender->isOp())
 					{
						$main->startMenu($sender);
					}else{
						$sender->sendMessage("§4権限がありません");
					}
	
					return true;
					break;
	
					case "hi":
					$main->tagMenu($sender);
					return true;
					break;
				}
		}
	}