<?php
namespace nl\dev7kasper\PMTester\main;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\entity\Living;
use pocketmine\entity\Entity;
use pocketmine\entity\Zombie;
use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\FloatTag;

class PMTester extends PluginBase implements Listener {
	
	public function onEnable() {
		$this->getLogger()->info(TextFormat::YELLOW . "PMTester on");
	}
	
	public function onDisable() {
		$this->getLogger()->info(TextFormat::YELLOW . "PMTester off");
	}
	
    public function onCommand(CommandSender $s, Command $cmd,  $label, array $args) {
        switch (strtolower($cmd->getName())) {
			case "zombie":
			     try {
                    if($s instanceof Player) {
                    	$p = $s;
                    	$s->sendMessage("Getting your zombie, " . TextFormat::LIGHT_PURPLE . "Buhhh!" . TextFormat::RESET);
						$nbt = new CompoundTag("", [
							new ListTag("Pos", [
								new DoubleTag("", $p->x),
								new DoubleTag("", $p->y),
								new DoubleTag("", $p->z)
							]),
							new ListTag("Motion", [
								new DoubleTag("", 0),
								new DoubleTag("", 0),
								new DoubleTag("", 0)
							]),
							new ListTag("Rotation", [
								new FloatTag("", 0),
								new FloatTag("", 0)
							]),
						]);
						$zombie = Entity::createEntity("Zombie", $p->getLevel(), $nbt);
						$zombie->spawnToAll();
                    } else {
                    	$s->sendMessage(TextFormat::DARK_RED . "[Error] Whattcha doing not being a player, where'd you want the pig?");
                    }
                } catch (RuntimeException $e) {
                    throw new RuntimeException($e);
                }
			return true;
            case "leash":
                try {
                    if($s instanceof Player) {
                    	$p = $s;
                    	$s->sendMessage("Leashing the sh*t outta everyone.");
                    	foreach($p->getLevel()->getEntities() as $e) {
                    		if($e instanceof Living) {
                        		$e->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_LEASHED, true);
                        		$e->setDataProperty(Entity::DATA_LEAD_HOLDER_EID, Entity::DATA_TYPE_LONG, $p->getId());
                        		$s->sendMessage("Leashed " . $e->getId() . " to " . $p->getId() . "!");
                    		}
                    	}
                    } else {
                    	$s->sendMessage(TextFormat::DARK_RED . "[Error] Whattcha doing not being a player?");
                    }
                } catch (RuntimeException $e) {
                    throw new RuntimeException($e);
                }
            return true;
            case "unleash": 
                try {
                    if($s instanceof Player) {
                    	$p = $s;
                    	if(count($args) > 0) {
                    		switch(strtolower($args[0])) {
                    			case "toself":
                        			$s->sendMessage("Unleashing the sh*t outta everyone, selfy style.");
                                	foreach($p->getLevel()->getEntities() as $e) {
                                		if($e instanceof Living) {
	                                		$e->setDataProperty(Entity::DATA_LEAD_HOLDER_EID, Entity::DATA_TYPE_LONG, $e->getId());
	                                		$s->sendMessage("(Tried) Unleashing " . $e->getId() . " to " . $e->getId() . "!");
                                		}
                                	}
                    			return true;
                    			case "respawn":
                        			$s->sendMessage("Unleashing the sh*t outta everyone, respawn-style.");
                                	foreach($p->getLevel()->getEntities() as $e) {
                                		if($e instanceof Living) {
                                    		$e->respawnToAll();
                                    		$s->sendMessage("(Tried) Unleashing " . $e->getId() . " by respawning?");
                                		}
                                	}
                				return true;
                    			case "zero":
                    			case "0":
                        			$s->sendMessage("Unleashing the sh*t outta everyone, " . TextFormat::BLACK . "Zorro" . TextFormat::RESET . " style.");
                                	foreach($p->getLevel()->getEntities() as $e) {
                                		if($e instanceof Living) {
	                                		$e->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_LEASHED, false);
	                                		$e->setDataProperty(Entity::DATA_LEAD_HOLDER_EID, Entity::DATA_TYPE_LONG, 0);
	                                		$s->sendMessage("(Tried) Unleashing " . $e->getId() . " by zeroing.");
                                		}
                                	}
                				return true;
                    			case "chuck":
                    				if(count($args) > 1 && strtolower($args[1]) == "norris") {
                            			$s->sendMessage("Unleashing the sh*t outta everyone, " . TextFormat::YELLOW . "Chuck Norris" . TextFormat::RESET . " style!");
                                    	foreach($p->getLevel()->getEntities() as $e) {
                                    		if($e instanceof Living) {
	                                    		$e->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_LEASHED, false);
	                                    		$e->setDataProperty(Entity::DATA_LEAD_HOLDER_EID, Entity::DATA_TYPE_LONG, -1);
	                                    		$e->despawnFromAll();
	                                    		$e->spawnToAll();
												$s->sendMessage("Chucked " . $e->getId() . "!");
                                    		}
                                    	}
                                    	return true;
                    				}
                    				break;
                    			case "-1":
                                	$s->sendMessage("Unleashing the sh*t outta everyone.");
                                	foreach($p->getLevel()->getEntities() as $e) {
                                		if($e instanceof Living) {
	                                		$e->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_LEASHED, false);
	                                		$e->setDataProperty(Entity::DATA_LEAD_HOLDER_EID, Entity::DATA_TYPE_LONG ,-1);
	                                		$s->sendMessage("(Tried) Unleashing " . $e->getId() . " from " . $p->getId() . "!");
                                		}
                                	}
								return true;
                    		}
                    	} else {
							$s->sendMessage("Unleashing the sh*t outta everyone.");
							foreach($p->getLevel()->getEntities() as $e) {
								if($e instanceof Living) {
									$e->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_LEASHED, false);
									$e->setDataProperty(Entity::DATA_LEAD_HOLDER_EID, Entity::DATA_TYPE_LONG, -1);
									$packet = new EntityEventPacket();
									$packet->entityRuntimeId = $e->getId();
									$packet->event = 63; //Baked straight in IDA's delicious oven.
									$packet->data = 0;
									foreach($p->getLevel()->getPlayers() as $playerA) {
										$playerA->dataPacket($packet);
									}
								}
								$s->sendMessage("Unleashed " . $e->getId() . " from " . $p->getId() . "!");
							}
						}
                    } else {
                    	$s->sendMessage(TextFormat::DARK_RED . "[Error] Whattcha doing not being a player?");
                    }
                } catch (RuntimeException $e) {
                    throw new RuntimeException($e);
                }
            return true;
        }
        return false;
    }
}