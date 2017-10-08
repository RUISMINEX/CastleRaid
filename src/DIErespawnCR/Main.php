<?php
namespace DIErespawnCR;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\math\Vector3;

class Main extends PluginBase implements Listener{
 
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getLogger()->info("DIErespawnCR Enabled!");
    }
 
    public function onDisable(){
        $this->getServer()->getLogger()->info("DIErespawnCR Disabled!");
    }
 
    public function onSpawn(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        $player->teleport($player->getLevel()->getSafeSpawn());
    }
}