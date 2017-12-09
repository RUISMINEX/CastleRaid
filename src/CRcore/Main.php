<?php
declare(strict_types=1);

namespace CRcore;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\Item;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\tile\Sign;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener{
	/** @var int $maxcaps */
	private $maxcaps;
	/** @var int $interval */
	public $interval = 10;
	/** @var array $yml */
	private $yml = [];

	public function onEnable(){
		$this->saveDefaultConfig();
		$this->interval = $this->getConfig()->get("interval");
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getNetwork()->setName($this->getConfig()->get("Server-Name", "unset name"));
		$this->getServer()->loadLevel("PVP");
		$yml = new Config($this->getDataFolder() . "config.yml", Config::YAML);
		$this->yml = $yml->getAll();
		$this->getLogger()->info("Starting KingdomCraft Core"); //not needed
		$this->getLogger()->info("Done!"); //not needed aswell
		$this->saveResource("config.yml");
		//Dev Mode will have more added later
		if($this->getConfig()->get("Dev_Mode") == "true"){
			$this->getLogger()->info("§cDev Mode is starting up...");
			$this->getServer()->getNetwork()->setName($this->getConfig()->get("Server-Name-Dev", "unset name"));
			$this->getServer()->loadLevel("hub");
			$this->getServer()->loadLevel("PVP");
			$this->getServer()->loadLevel("SkyGate");
			$this->getServer()->loadLevel("SW3");
			$this->getServer()->loadLevel("WoodLand");
			$this->getServer()->loadLevel("BreezeIsland");
			$this->getLogger()->info("§cDev Mode Loaded!");
		}
		new EventListener($this);
	}

	public function loadConfig(){
		$this->saveDefaultConfig();
		$this->maxcaps = intval($this->getConfig()->get("max-caps"));
	}

	public function onDisable(){
		$this->getLogger()->info("Shutting down KingdomCraft Core"); //not needed
		$this->saveConfig();
		$this->getLogger()->info("Done!"); //not needed
	}

	public function onRespawn(PlayerRespawnEvent $event){
		$player = $event->getPlayer();
		$event->getPlayer()->teleport(Server::getInstance()->getLevelByName("hub")->getSafeSpawn());
		$player->setMaxHealth(20);
		$player->setHealth(20);
		$player->setFood(20);
		$player->getInventory()->clearAll();
		$player->getInventory()->setItem(1, Item::get(388, 0, 1));
		$player->getInventory()->setItem(2, Item::get(264, 0, 1));
		$player->getInventory()->setItem(3, Item::get(265, 0, 1));
		$player->getInventory()->setItem(4, Item::get(406, 0, 1));
		$player->getInventory()->equipItem(1);
		$player->getInventory()->equipItem(2);
		$player->getInventory()->equipItem(3);
		$player->getInventory()->equipItem(4);
	}

	public function onJoin(PlayerJoinEvent $event){
		$player = $event->getPlayer();
		$player->sendMessage("Welcome,§b " . $player->getName() . " §fto §l§o§bKingdom§9Craft");
		$player->setMaxHealth(20);
		$player->setHealth(20);
		$player->setFood(20);
		$player->getInventory()->clearAll();
		$player->getInventory()->setItem(1, Item::get(388, 0, 1));
		$player->getInventory()->setItem(2, Item::get(264, 0, 1));
		$player->getInventory()->setItem(3, Item::get(265, 0, 1));
		$player->getInventory()->setItem(4, Item::get(406, 0, 1));
		$player->getInventory()->equipItem(1);
		$player->getInventory()->equipItem(2);
		$player->getInventory()->equipItem(3);
		$player->getInventory()->equipItem(4);
		$event->getPlayer()->teleport(Server::getInstance()->getLevelByName("hub")->getSafeSpawn());
		$rank = "Basic";
		$level = $this->getServer()->getDefaultLevel();
		if($event->getPlayer()->isOp()){
			$rank = "Admin";
		}
		$level->addParticle(new FloatingTextParticle(new Vector3(129, 69.8, 124), "", "§7------------------------------------"));
		$level->addParticle(new FloatingTextParticle(new Vector3(129, 69.4, 124), "", "§fWelcome, §b{$event->getPlayer()->getName()}§fto §bKingdom§9Craft"), [$event->getPlayer()]);
		$level->addParticle(new FloatingTextParticle(new Vector3(129, 69.1, 124), "", "§fYou are Playing on: §bplay§7.§bkcmcpe§b.§bnet"), [$event->getPlayer()]);
		$level->addParticle(new FloatingTextParticle(new Vector3(129, 68.7, 124), "", "§fYour rank§8: §b{$rank}"), [$event->getPlayer()]);
		$level->addParticle(new FloatingTextParticle(new Vector3(129, 68.1, 124), "", "§fWe are in beta §7- §bv1§7.§b2"), [$event->getPlayer()]);
		$level->addParticle(new FloatingTextParticle(new Vector3(129, 67.5, 124), "", "§7------------------------------------"));
	}

	//Removes need for iProtector
	public function onBlockBreakHub(BlockBreakEvent $event){
		$player = $event->getPlayer();
		if($player->getLevel()->getName() == "hub" and !$player->isOp()){
			$event->setCancelled(true);
		}elseif($player->getLevel()->getName() == "PVP" and !$player->isOp()){
			$event->setCancelled(true);
		}
	}

	public function onBlockPlaceHub(BlockPlaceEvent $event){
		$player = $event->getPlayer();
		if($player->getLevel()->getName() == "hub" and !$player->isOp()){
			$event->setCancelled(true);
		}elseif($player->getLevel()->getName() == "PVP" and !$player->isOp()){
			$event->setCancelled(true);
		}
	}

	public function GodMode(EntityDamageEvent $event){
		$player = $event->getEntity();
		if($player->getLevel()->getName() == "hub"){
			$event->setCancelled(true);
		}
	}

	public function onHunger(PlayerExhaustEvent $event){
		$player = $event->getPlayer();
		if($player->getLevel()->getName() == "hub"){
			$event->setCancelled(true);
		}
	}

	public function onDrop(PlayerDropItemEvent $event){
		$player = $event->getPlayer();
		$player->sendTip("§cYou Cannot Drop Items");
		$event->setCancelled(true);
	}

	public function onHeld(PlayerItemHeldEvent $event){
		$cfg = $this->getConfig();
		$player = $event->getPlayer();
		$item = $event->getItem()->getId();
		if($item === $cfg->get("item1") and $player->getLevel()->getName() == "hub"){
			$player->sendPopup("KitPvP");
		}elseif($item === $cfg->get("item2") and $player->getLevel()->getName() == "hub"){
			$player->sendPopup("Help");
		}elseif($item === $cfg->get("item3") and $player->getLevel()->getName() == "hub"){
			$player->sendPopup("SkyWars");
		}elseif($item === $cfg->get("item4") and $player->getLevel()->getName() == "hub"){
			$player->sendPopup("Hub");
		}
	}

	public function onPacketReceived(DataPacketReceiveEvent $event){
		$pk = $event->getPacket();
		$player = $event->getPlayer();
		if($pk instanceof InventoryTransactionPacket and ($pk->trData->actionType === InventoryTransactionPacket::USE_ITEM_ACTION_CLICK_AIR or $pk->trData->actionType === InventoryTransactionPacket::USE_ITEM_ACTION_CLICK_BLOCK)){
			$item = $player->getInventory()->getItemInHand();
			if($item->getId() == $this->yml["item1"] and $player->getLevel()->getName() == "hub"){
				$player->teleport(Server::getInstance()->getLevelByName("PVP")->getSafeSpawn());
				$player->getInventory()->clearAll();
			}elseif($item->getId() == $this->yml["item2"] and $player->getLevel()->getName() == "hub"){
				$player->sendMessage("§o§l§b-- Help Page 1 of 1 --§r\n§b/hub - §fTeleport player to hub\n§b/help - §f{Page}lists all Commands\n§b/tell - §f{player}Sends a private message to the given player\n§b/mymoney - §fChecks How much money you have\n§b/pay - §f{player}Allows you to give toher players money\n§b/flyon - §fAdmins only\n§b/flyoff - §fAdmins only");
			}elseif($item->getId() == $this->yml["item3"] and $player->getLevel()->getName() == "hub"){
				$player->sendMessage("§o§l§f-- §cJoining §bSkywars§f --§r");
				$player->setHealth(20);
				$player->setFood(20);
				$player->teleport(new Vector3(134, 77, 81));
				$player->getInventory()->clearAll();
			}elseif($item->getId() == $this->yml["item4"] and $player->getLevel()->getName() == "hub"){
				$event->getPlayer()->teleport(Server::getInstance()->getLevelByName("hub")->getSafeSpawn());
				$player->sendMessage($this->getConfig()->get("Hub-Command"));
				$player->setMaxHealth(20);
				$player->setHealth(20);
				$player->getInventory()->clearAll();
				$player->getInventory()->setItem(1, Item::get(388, 0, 1));
				$player->getInventory()->setItem(2, Item::get(264, 0, 1));
				$player->getInventory()->setItem(3, Item::get(265, 0, 1));
				$player->getInventory()->setItem(4, Item::get(406, 0, 1));
				$player->getInventory()->equipItem(1);
				$player->getInventory()->equipItem(2);
				$player->getInventory()->equipItem(3);
				$player->getInventory()->equipItem(4);
			}
		}
	}

	public function onToManyCaps(PlayerChatEvent $event){
		$this->maxcaps = intval($this->getConfig()->get("max-caps"));
		$player = $event->getPlayer();
		$message = $event->getMessage();
		$strlen = strlen($message);
		$asciiA = ord("A");
		$asciiZ = ord("Z");
		$count = 0;
		for($i = 0; $i < $strlen; $i++){
			$char = $message[$i];
			$ascii = ord($char);
			if($asciiA <= $ascii and $ascii <= $asciiZ){
				$count++;
			}
		}
		if($count > $this->getMaxCaps()){
			$event->setCancelled(true);
			$player->sendMessage("§7[§bKingdom§9Chat§7] §cYou used too much caps!");
		}
	}

	public function onDeath(PlayerDeathEvent $event){
		$cause = $event->getEntity()->getLastDamageCause();
		if($cause instanceof EntityDamageByEntityEvent){
			$player = $event->getEntity();
			$killer = $cause->getDamager();
			if($killer instanceof Player){
				$event->setDeathMessage("");
				$player->sendMessage("§bYou were Killed by " . $killer->getName());
				$killer->sendMessage("§bYou Killed " . $player->getName());
				$player->setMaxHealth(20);
				$player->getInventory()->clearAll();
			}
		}
	}

	public function FlyonCommand(PlayerCommandPreprocessEvent $event){
		$cmd = explode(" ", strtolower($event->getMessage()));
		if($cmd[0] === "/flyon"){
			$player = $event->getPlayer();
			if($player->isOp()){
				$player->setAllowFlight(true);
				$player->sendMessage("§7[§l§o§bKingdom§9Craft§r§7] §3Flight on");
				$event->setCancelled();
			}
		}
	}

	public function FlyoffCommand(PlayerCommandPreprocessEvent $event){
		$cmd = explode(" ", strtolower($event->getMessage()));
		if($cmd[0] === "/flyoff"){
			$player = $event->getPlayer();
			if($player->isOp()){
				$player->setAllowFlight(false);
				$player->sendMessage("§7[§l§o§bKingdom§9Craft§r§7] §cFlight off");
				$event->setCancelled();
			}
		}
	}

	public function Hub(PlayerCommandPreprocessEvent $event){
		$cmd3 = explode(" ", strtolower($event->getMessage()));
		$player = $event->getPlayer();
		if($cmd3[0] === "/hub"){
			$event->getPlayer()->teleport(Server::getInstance()->getLevelByName("hub")->getSafeSpawn());
			$player->sendMessage($this->getConfig()->get("Hub-Command"));
			$player->setMaxHealth(20);
			$player->setHealth(20);
			$player->getInventory()->clearAll();
			$player->getInventory()->setItem(1, Item::get(388, 0, 1));
			$player->getInventory()->setItem(2, Item::get(264, 0, 1));
			$player->getInventory()->setItem(3, Item::get(265, 0, 1));
			$player->getInventory()->setItem(4, Item::get(406, 0, 1));
			$player->getInventory()->equipItem(1);
			$player->getInventory()->equipItem(2);
			$player->getInventory()->equipItem(3);
			$player->getInventory()->equipItem(4);
			$event->setCancelled();
		}
	}

	public function setGroupBlockForAdminsAndAnyoneWhoIdontTrust(PlayerCommandPreprocessEvent $event){//cancer
		$cmd3 = explode(" ", strtolower($event->getMessage()));
		$player = $event->getPlayer();
		if($cmd3[0] === "/setgroup"){
			$player->sendMessage($this->getConfig()->get("Unknown-Command"));
			$event->setCancelled();
		}
	}

	public function PermsInfoBlock(PlayerCommandPreprocessEvent $event){
		$cmd3 = explode(" ", strtolower($event->getMessage()));
		$player = $event->getPlayer();
		if($cmd3[0] === "/ppinfo"){
			$player->sendMessage($this->getConfig()->get("Unknown-Command"));
			$event->setCancelled();
		}
	}

	public function Plugins(PlayerCommandPreprocessEvent $event){
		$cmd4 = explode(" ", strtolower($event->getMessage()));
		$player = $event->getPlayer();
		if($cmd4[0] === "/plugins"){
			$player->sendMessage($this->getConfig()->get("Unknown-Command"));
			$event->setCancelled();
		}
	}

	public function Heal(PlayerCommandPreprocessEvent $event){
		$cmd4 = explode(" ", strtolower($event->getMessage()));
		if($cmd4[0] === "/heal"){
			$player = $event->getPlayer();
			if($player->isOp()){
				$player->setMaxHealth(80);
				$player->setHealth(80);
				$event->setCancelled();
			}
		}
	}

	public function Help(PlayerCommandPreprocessEvent $event){
		$cmdHelp = explode(" ", strtolower($event->getMessage()));
		$player = $event->getPlayer();
		if($cmdHelp[0] === "/help"){
			$player->sendMessage("§o§l§b-- Help Page 1 of 1 --§r\n§b/hub - §fTeleport player to hub\n§b/help - §f{Page}lists all Commands\n§b/tell - §f{player}Sends a private message to the given player\n§b/mymoney - §fChecks How much money you have\n§b/pay - §f{player}Allows you to give toher players money\n§b/flyon - §fAdmins only\n§b/flyoff - §fAdmins only");
			$event->setCancelled();
		}
	}

	public function Help2(PlayerCommandPreprocessEvent $event){
		$cmd3 = explode(" ", strtolower($event->getMessage()));
		$player = $event->getPlayer();
		if($cmd3[0] === "/?"){
			$player->sendMessage($this->getConfig()->get("Unknown-Command"));
			$event->setCancelled();
		}
	}

	public function KitSignSetup(SignChangeEvent $event){
		$player = $event->getPlayer();
		if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
			$sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
			if(!($sign instanceof Sign)){
				return;
			}
			$sign = $event->getLines();
			if($sign[0] == 'PvP'){
				$player->sendMessage("§o§l§b-- PvP Setup --");
				$event->setLine(0, "§l§c[§bKitPvP§c]");
				$event->setLine(1, "§l§eBiomePvP");
				$event->setLine(3, "§fTap to Join");
			}
		}
	}

	public function SkyWarsSign(SignChangeEvent $event){
		$player = $event->getPlayer();
		if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
			$sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
			if(!($sign instanceof Sign)){
				return;
			}
			$sign = $event->getLines();
			if($sign[0] == 'Sky'){
				$player->sendMessage("§o§l§b-- Skywars Setup --");
				$event->setLine(0, "§l§c[§bSkywars§c]");
				$event->setLine(1, "§l§eSkywars Lobby");
				$event->setLine(3, "§fTap to Join");
			}
		}
	}

	public function playerPvP(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
			$sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
			if(!($sign instanceof Sign)){
				return;
			}
			$sign = $sign->getText();
			if($sign[0] == '§l§c[§bKitPvP§c]'){
				$player->sendMessage("§o§l§f-- §cJoining §bPvP§f --§r");
				$player->setHealth(20);
				$player->setFood(20);
				$event->getPlayer()->teleport(Server::getInstance()->getLevelByName("PVP")->getSafeSpawn());
				$player->getInventory()->clearAll();
			}
		}
	}

	public function playerSkywars(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
			$sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
			if(!($sign instanceof Sign)){
				return;
			}
			$sign = $sign->getText();
			if($sign[0] == '§l§c[§bSkywars§c]'){
				$player->sendMessage("§o§l§f-- §cJoining §bSkywars§f --§r");
				$player->setHealth(20);
				$player->setFood(20);
				$player->teleport(new Vector3(134, 77, 81));
				$player->getInventory()->clearAll();
			}
		}
	}

	public function playerKit(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
			$sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
			if(!($sign instanceof Sign)){
				return;
			}
			$sign = $sign->getText();
			if($sign[0] == '§eKnight'){
				if($player->hasPermission("game.kit")){
					$player->sendTip("§o§l§b-- §cPvP Kit §bKnight§c Given§b --");
					$player->setMaxHealth(80);
					$player->setHealth(80);
					$player->setFood(20);
					$player->getInventory()->clearAll();
					$player->getInventory()->setItem(0, Item::get(276, 0, 1));
					$player->getInventory()->setItem(1, Item::get(322, 0, 64));
					$player->getInventory()->setItem(2, Item::get(373, 14, 1));
					$player->getInventory()->setItem(3, Item::get(373, 28, 1));
					$player->getInventory()->setHelmet(Item::get(302, 0, 1));
					$player->getInventory()->setChestplate(Item::get(307, 0, 1));
					$player->getInventory()->setLeggings(Item::get(308, 0, 1));
					$player->getInventory()->setBoots(Item::get(305, 0, 1));
					$player->getInventory()->sendArmorContents($player);
					$player->teleport(new Vector3(1719, 9, -1027));
					$player->getInventory()->equipItem(0);
					$player->getInventory()->equipItem(1);
					$player->getInventory()->equipItem(2);
					$player->getInventory()->equipItem(3);
					$player->getInventory()->equipItem(4);
				}
			}
		}
	}

	public function playerKit2(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
			$sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
			if(!($sign instanceof Sign)){
				return;
			}
			$sign = $sign->getText();
			if($sign[0] == '§eArcher'){
				if($player->hasPermission("game.kit")){
					$player->sendMessage("§o§l§b-- §cPvP Kit §bArcher§c Given§b --");
					$player->sendTip("§o§l§b-- §cPvP Kit §bArcher§c Given --");
					$player->setMaxHealth(80);
					$player->setHealth(80);
					$player->setFood(20);
					$player->getInventory()->clearAll();
					$player->getInventory()->setItem(0, Item::get(279, 0, 1));
					$player->getInventory()->setItem(1, Item::get(261, 0, 1));
					$player->getInventory()->setItem(2, Item::get(322, 0, 64));
					$player->getInventory()->setItem(3, Item::get(373, 14, 1));
					$player->getInventory()->setItem(4, Item::get(373, 28, 1));
					$player->getInventory()->setItem(14, Item::get(262, 27, 255));
					$player->getInventory()->setHelmet(Item::get(302, 0, 1));
					$player->getInventory()->setChestplate(Item::get(307, 0, 1));
					$player->getInventory()->setLeggings(Item::get(308, 0, 1));
					$player->getInventory()->setBoots(Item::get(305, 0, 1));
					$player->getInventory()->sendArmorContents($player);
					$player->teleport(new Vector3(1719, 9, -1027));
					$player->getInventory()->equipItem(0);
					$player->getInventory()->equipItem(1);
					$player->getInventory()->equipItem(2);
					$player->getInventory()->equipItem(3);
					$player->getInventory()->equipItem(4);
				}
			}
		}
	}

	public function playerMobcrushKit(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
			$sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
			if(!($sign instanceof Sign)){
				return;
			}
			$sign = $sign->getText();
			if($sign[0] == '§eSuper'){
				if($player->hasPermission("game.kit.super")){
					$player->sendTip("§o§l§b-- §cPvP Kit §bSuper§c Given§b --");
					$player->setMaxHealth(80);
					$player->setHealth(80);
					$player->setFood(20);
					$player->getInventory()->clearAll();
					$player->getInventory()->setItem(0, Item::get(279, 0, 1));
					$player->getInventory()->setItem(1, Item::get(466, 0, 3));
					$player->getInventory()->setItem(2, Item::get(373, 14, 1));
					$player->getInventory()->setItem(3, Item::get(373, 31, 1));
					$player->getInventory()->setHelmet(Item::get(302, 0, 1));
					$player->getInventory()->setChestplate(Item::get(311, 0, 1));
					$player->getInventory()->setLeggings(Item::get(308, 0, 1));
					$player->getInventory()->setBoots(Item::get(313, 0, 1));
					$player->getInventory()->sendArmorContents($player);
					$player->teleport(new Vector3(1719, 9, -1027));
					$player->getInventory()->equipItem(0);
					$player->getInventory()->equipItem(1);
					$player->getInventory()->equipItem(2);
					$player->getInventory()->equipItem(3);
					$player->getInventory()->equipItem(4);
				}
			}
		}
	}

	public function playerVIPKit(PlayerInteractEvent $event){
		$player = $event->getPlayer();
		if($event->getBlock()->getID() == 323 || $event->getBlock()->getID() == 63 || $event->getBlock()->getID() == 68){
			$sign = $event->getPlayer()->getLevel()->getTile($event->getBlock());
			if(!($sign instanceof Sign)){
				return;
			}
			$sign = $sign->getText();
			if($sign[0] == '§eSuper+'){
				if($player->hasPermission("game.kit.super+")){
					$player->sendTip("§o§l§b-- §cPvP Kit §bSuper+§c Given§b --");
					$player->setMaxHealth(80);
					$player->setHealth(80);
					$player->setFood(20);
					$player->getInventory()->clearAll();
					$player->getInventory()->setItem(0, Item::get(279, 0, 1));
					$player->getInventory()->setItem(1, Item::get(466, 0, 3));
					$player->getInventory()->setItem(2, Item::get(373, 14, 1));
					$player->getInventory()->setItem(3, Item::get(373, 31, 1));
					$player->getInventory()->setHelmet(Item::get(302, 0, 1));
					$player->getInventory()->setChestplate(Item::get(311, 0, 1));
					$player->getInventory()->setLeggings(Item::get(308, 0, 1));
					$player->getInventory()->setBoots(Item::get(313, 0, 1));
					$player->getInventory()->sendArmorContents($player);
					$player->teleport(new Vector3(1719, 9, -1027));
					$player->getInventory()->equipItem(0);
					$player->getInventory()->equipItem(1);
					$player->getInventory()->equipItem(2);
					$player->getInventory()->equipItem(3);
					$player->getInventory()->equipItem(4);
				}
			}
		}
	}

	public function getMaxCaps(){
		return $this->maxcaps;
	}

	public function saveConfig(){
		$this->getConfig()->set("max-caps", $this->getMaxCaps());
		$this->getConfig()->save();
	}
}
