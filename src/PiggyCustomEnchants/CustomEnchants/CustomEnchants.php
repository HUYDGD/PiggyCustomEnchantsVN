<?php

namespace PiggyCustomEnchants\CustomEnchants;

use pocketmine\item\enchantment\Enchantment;

class CustomEnchants extends Enchantment
{
    public static function init()
    {
        $vanillaenchants = [];
        foreach (parent::$enchantments as $id => $enchantment) {
            $vanillaenchants[$id] = $enchantment;
        }
        parent::$enchantments = $vanillaenchants;
    }

    public static function getEnchantmentByName(string $name)
    {
        $const = CustomEnchantsIds::class . "::" . strtoupper($name);
        if (defined($const)) {
            return self::getEnchantment(constant($const));
        }
        return null;
    }

    public static function unregisterEnchantment($id)
    {
        if (isset(parent::$enchantments[$id])) {
            unset(parent::$enchantments[$id]);
            return true;
        }
        return false;
    }
}
