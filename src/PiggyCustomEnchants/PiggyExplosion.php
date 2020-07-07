<?php

namespace PiggyCustomEnchants;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\level\Explosion;
use pocketmine\level\Position;
use pocketmine\Player;

class PiggyExplosion extends Explosion
{
    protected $player;
    private $plugin;

    public function __construct(Position $center, $size, Player $player = null, Main $plugin)
    {
        $this->plugin = $plugin;
        $this->player = $player;
        parent::__construct($center, $size, $player);
    }

    public function explodeB(): bool
    {
        $result = parent::explodeB();
        foreach ($this->affectedBlocks as $index => $block) {
            $ev = new BlockBreakEvent($this->player, $block, $this->player->getInventory()->getItemInHand());
            $this->plugin->getServer()->getPluginManager()->callEvent($ev);
            if ($ev->isCancelled()) {
                unset($this->affectedBlocks[$index]);
            } else {
                foreach ($ev->getDrops() as $drop) {
                    $this->level->dropItem($block->add(0.5, 0.5, 0.5), $drop);
                }
            }
        }
        return $result;
    }
}
