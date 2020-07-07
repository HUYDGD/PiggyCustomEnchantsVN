<?php

namespace PiggyCustomEnchants\Entities;

use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Arrow;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

class VolleyArrow extends Arrow
{
    private $volley;
    public $placeholder;
    private $ownerOriginalLocation;

    public function __construct(Level $level, CompoundTag $nbt, Entity $shootingEntity = null, bool $critical = false, bool $placeholder = false, bool $volley = false)
    {
        $this->volley = $volley;
        $this->placeholder = $placeholder;
        $this->ownerOriginalLocation = $shootingEntity->getLocation();
        parent::__construct($level, $nbt, $shootingEntity, $critical);
    }

    public function entityBaseTick(int $tickDiff = 1): bool
    {
        if ($this->closed) {
            return false;
        }
        $hasUpdate = parent::entityBaseTick($tickDiff);
        if (!$this->isFlaggedForDespawn()) {
            if (!$this->isCollided) {
                if ($this->getOwningEntity() instanceof Player && $this->placeholder) {
                    $this->getOwningEntity()->sendPosition($this->add($this->getDirectionVector()->multiply(-2)), $this->yaw, $this->pitch);
                }
            } else {
                if ($this->isVolley()) {
                    $this->flagForDespawn();
                    $hasUpdate = true;
                }
                if ($this->placeholder) {
                    $this->placeholder = false;
                    $this->getOwningEntity()->teleport($this->ownerOriginalLocation);
                }
            }
        }
        return $hasUpdate;
    }

    public function isVolley()
    {
        return $this->volley;
    }
}
