<?php

namespace PiggyCustomEnchants\Tasks;

use PiggyCustomEnchants\CustomEnchants\CustomEnchantsIds;
use PiggyCustomEnchants\Main;
use pocketmine\math\Vector3;
use pocketmine\scheduler\PluginTask;

class ParachuteTask extends PluginTask
{
    private $plugin;

    public function __construct(Main $plugin)
    {
        parent::__construct($plugin);
        $this->plugin = $plugin;
    }

    public function onRun(int $currentTick)
    {
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $player) {
            $chestplate = $player->getArmorInventory()->getChestplate();
            $enchantment = $chestplate->getEnchantment(CustomEnchantsIds::PARACHUTE);
            if ($enchantment !== null) {
                $motion = $player->getMotion();
                if ($this->plugin->checkBlocks($player, 0, 3)) {
                    $player->setMotion(new Vector3(0, $motion->y * 0.75, 0));
                    $player->resetFallDistance();
                }
            }
        }
    }
}
