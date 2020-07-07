<?php

namespace PiggyCustomEnchants\Tasks;

use PiggyCustomEnchants\CustomEnchants\CustomEnchantsIds;
use PiggyCustomEnchants\Main;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\TextFormat;

class SizeTask extends PluginTask
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
            $shrinkpoints = 0;
            $growpoints = 0;
            foreach ($player->getArmorInventory()->getContents() as $armor) {
                $enchantment = $armor->getEnchantment(CustomEnchantsIds::SHRINK);
                if ($enchantment !== null) {
                    $shrinkpoints++;
                }
            }
            if (isset($this->plugin->shrunk[$player->getLowerCaseName()]) && ($this->plugin->shrunk[$player->getLowerCaseName()] <= time() || $shrinkpoints < 4)) {
                if ($this->plugin->shrunk[$player->getLowerCaseName()] > time()) {
                    $this->plugin->shrinkremaining[$player->getLowerCaseName()] = $this->plugin->shrunk[$player->getLowerCaseName()] - time();
                    unset($this->plugin->shrinkcd[$player->getLowerCaseName()]);
                }
                unset($this->plugin->shrunk[$player->getLowerCaseName()]);
                $player->setScale(1);
                $player->sendTip(TextFormat::RED . "Bạn đã trở lại kích thước cũ !");
            }
            foreach ($player->getArmorInventory()->getContents() as $armor) {
                $enchantment = $armor->getEnchantment(CustomEnchantsIds::GROW);
                if ($enchantment !== null) {
                    $growpoints++;
                }
            }
            if (isset($this->plugin->grew[$player->getLowerCaseName()]) && ($this->plugin->grew[$player->getLowerCaseName()] <= time() || $growpoints < 4)) {
                if ($this->plugin->grew[$player->getLowerCaseName()] > time()) {
                    $this->plugin->growremaining[$player->getLowerCaseName()] = $this->plugin->grew[$player->getLowerCaseName()] - time();
                    unset($this->plugin->growcd[$player->getLowerCaseName()]);
                }
                unset($this->plugin->grew[$player->getLowerCaseName()]);
                $player->setScale(1);
                $player->sendTip(TextFormat::RED . "Bạn đã trở lại kích thước cũ !");
            }
        }
    }
}
