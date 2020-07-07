<?php

namespace PiggyCustomEnchants\Entities;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

class PigProjectile extends PiggyProjectile
{
    private $porklevel = 1;
    private $zombie = false;

    public $width = 0.9;
    public $height = 0.9;

    protected $drag = 0.01;
    protected $gravity = 0.05;

    protected $damage = 1.5;

    const TYPE_ID = 12;

    const PORKLEVELS = [
        1 => [1, false, false, Item::AIR, ""],
        2 => [2, false, false, Item::RAW_PORKCHOP, "Mysterious Raw Pork"],
        3 => [2, false, false, Item::COOKED_PORKCHOP, "Mysterious Cooked Pork"],
        4 => [3, true, false, Item::COOKED_PORKCHOP, "Mysterious Cooked Pork"],
        5 => [5, false, true, Item::ROTTEN_FLESH, "Mysterious Rotten Pork"],
        6 => [6, true, true, Item::ROTTEN_FLESH, "Mysterious Rotten Pork"]
    ];

    public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null, bool $placeholder = false, int $porklevel = 1)
    {
        if ($porklevel < 1) {
            $porklevel = 1;
        }
        if ($porklevel > 6) {
            $porklevel = 6;
        }
        $values = self::PORKLEVELS[$porklevel];
        $this->damage = $values[0];
        if ($values[1]) {
            $this->setNameTag("Dinnerbone");
        }
        $this->zombie = $values[2];
        $this->porklevel = $porklevel;
        parent::__construct($level, $nbt, $shootingEntity, $placeholder);
    }

    public function entityBaseTick(int $tickDiff = 1): bool
    {
        if ($this->closed) {
            return false;
        }
        $hasUpdate = parent::entityBaseTick($tickDiff);
        if (!$this->isCollided) {
            if ($this->getPorkLevel() > 1) {
                foreach ($this->getDrops() as $drop) {
                    $droppeditem = $this->getLevel()->dropItem($this, $drop);
                    $droppeditem->age = 5700; //300 ticks (15 seconds) til despawns}
                }
            }
        } else {
            $this->flagForDespawn();
            $hasUpdate = true;
        }
        return $hasUpdate;
    }

    protected function sendSpawnPacket(Player $player): void
    {
        $pk = new AddEntityPacket();
        $pk->type = $this->isZombie() ? Entity::ZOMBIE_PIGMAN : static::TYPE_ID;
        $pk->entityRuntimeId = $this->getId();
        $pk->position = $this->asVector3();
        $pk->motion = $this->getMotion();
        $pk->metadata = $this->propertyManager->getAll();
        $player->dataPacket($pk);
    }

    public function getDrops(): array
    {
        $values = self::PORKLEVELS[$this->getPorkLevel()];
        return [
            Item::get($values[3], 0, 1)->setCustomName($values[4])
        ];
    }

    public function getPorkLevel()
    {
        return $this->porklevel;
    }

    public function isZombie()
    {
        return $this->zombie;
    }
}
