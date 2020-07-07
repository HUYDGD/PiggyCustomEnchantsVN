<?php

namespace PiggyCustomEnchants\Entities;


use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Projectile;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\Player;

class PiggyProjectile extends Projectile
{
    public $placeholder;
    private $ownerOriginalLocation;

    const TYPE_ID = 0;

    public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null, $placeholder = false)
    {
        $this->placeholder = $placeholder;
        $this->ownerOriginalLocation = $shootingEntity->getPosition();
        parent::__construct($level, $nbt, $shootingEntity);
    }

    public function entityBaseTick(int $tickDiff = 1): bool
    {
        if ($this->closed) {
            return false;
        }
        $hasUpdate = parent::entityBaseTick($tickDiff);
        if (!$this->isCollided) {
            if ($this->getOwningEntity() instanceof Player && $this->placeholder) {
                $this->getOwningEntity()->sendPosition($this->add($this->getDirectionVector()->multiply(2)), $this->yaw * 2 <= 360 ? $this->yaw * 2 : $this->yaw / 2, $this->pitch);
            }
        } else {
            if ($this->placeholder) {
                $this->getOwningEntity()->teleport($this->ownerOriginalLocation);
            }
            $this->flagForDespawn();
            $hasUpdate = true;
        }
        return $hasUpdate;
    }

    public function spawnTo(Player $player): void
    {
        parent::spawnTo($player);
        $pk = new AddEntityPacket();
        $pk->entityRuntimeId = $this->getId();
        $pk->type = static::TYPE_ID;
        $pk->position = $this->asVector3();
        $pk->motion = $this->getMotion();
        $pk->yaw = $this->yaw;
        $pk->pitch = $this->pitch;
        $pk->metadata = $this->propertyManager->getAll();
        $player->dataPacket($pk);
    }
}
