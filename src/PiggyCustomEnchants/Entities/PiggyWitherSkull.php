<?php

namespace PiggyCustomEnchants\Entities;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;

class PiggyWitherSkull extends PiggyProjectile
{
    public $width = 0.5;
    public $length = 0.5;
    public $height = 0.5;

    protected $drag = 0.01;
    protected $gravity = 0.05;

    protected $damage = 0;

    const TYPE_ID = 89;

    public function onCollideWithEntity(Entity $entity)
    {
        if ($entity instanceof Living) {
            $effect = new EffectInstance(Effect::getEffect(Effect::WITHER), 800, 1);
            $entity->addEffect($effect);
        }
        parent::onCollideWithEntity($entity);
    }
}
