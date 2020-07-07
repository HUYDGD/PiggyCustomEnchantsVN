<?php

namespace PiggyCustomEnchants\Tasks;

use PiggyCustomEnchants\Main;
use pocketmine\entity\Entity;
use pocketmine\scheduler\PluginTask;

class MoltenTask extends PluginTask
{
    private $plugin;
    private $entity;
    private $level;

    public function __construct(Main $plugin, Entity $entity, $level)
    {
        parent::__construct($plugin);
        $this->plugin = $plugin;
        $this->entity = $entity;
        $this->level = $level;
    }

    public function onRun(int $currentTick)
    {
        $this->entity->setOnFire(3 * $this->level);
    }
}
