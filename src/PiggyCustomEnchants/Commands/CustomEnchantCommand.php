<?php

namespace PiggyCustomEnchants\Commands;

use PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use PiggyCustomEnchants\Main;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class CustomEnchantCommand extends PluginCommand
{

    public function __construct($name, Main $plugin)
    {
        parent::__construct($name, $plugin);
        $this->setDescription("Enchant với Custom Enchants");
        $this->setUsage("/customenchant <about|enchant|help|info|list>");
        $this->setAliases(["ce", "customenchants", "customenchantments", "customenchant", "phuphep", "soicon", "gamersoicon"]);
        $this->setPermission("piggycustomenchants.command.ce");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            $forms = $sender instanceof Player && $plugin->formsEnabled && ($formsapi = $plugin->getServer()->getPluginManager()->getPlugin("FormAPI")) instanceof \jojoe77777\FormAPI\FormAPI && $formsapi->isEnabled();
            if (count($args) < 1) {
                if ($forms) {
                    $this->formMenu($sender);
                    return true;
                }
                $sender->sendMessage(TextFormat::RED . "Sử dụng: /customenchant <about|enchant|help|info|list>");
                return false;
            }
            switch ($args[0]) {
                case "about":
                    if (!$sender->hasPermission("piggycustomenchants.command.ce.about")) {
                        $this->error($sender, TextFormat::RED . "Bạn không có quyền để làm điều này !");
                        return false;
                    }
                    if ($forms) {
                        $this->aboutForm($sender);
                        return true;
                    }
                    $sender->sendMessage(TextFormat::GREEN . "PiggyCustomEnchants v" . $this->getPlugin()->getDescription()->getVersion() . " là một plugin Custom Enchants được làm bởi DaPigGuy (IGN: MCPEPIG) & Aericio & Translate by GamerSoiCon.\n" . TextFormat::GREEN . "Bạn có thể tìm thấy plugin tại https://github.com/DaPigGuy/PiggyCustomEnchants.");
                    break;
                case "enchant":
                    if (!$sender->hasPermission("piggycustomenchants.command.ce.enchant")) {
                        $this->error($sender, TextFormat::RED . "Bạn không có quyền để làm điều này !");
                        return false;
                    }
                    if (count($args) < 2) {
                        if ($forms) {
                            $this->enchantForm($sender);
                            return true;
                        }
                        $sender->sendMessage(TextFormat::RED . "Sử dụng: /customenchant enchant <enchant> [cấp độ] [người chơi]");
                        return false;
                    }
                    if ($forms) {
                        $this->checkEnchantForm($sender, [$args[1], isset($args[2]) ? $args[2] : 1, isset($args[3]) ? $args[3] : $sender->getName()]);
                        return true;
                    }
                    $this->enchant($sender, $args[1], isset($args[2]) ? $args[2] : 1, isset($args[3]) ? $args[3] : $sender->getName());
                    break;
                case "help":
                    if (!$sender->hasPermission("piggycustomenchants.command.ce.help")) {
                        $this->error($sender, TextFormat::RED . "Bạn không có quyền để làm điều này !");
                        return false;
                    }
                    if ($forms) {
                        $this->helpForm($sender);
                        return true;
                    }
                    $sender->sendMessage(TextFormat::GREEN . "---PiggyCE Trợ Giúp---\n" . TextFormat::RESET . "/ce about: Hiển thị thông tin về plugin\n/ce enchant: Phù phép vật phẩm\n/ce help: Hiển thị trợ giúp\n/ce info: Xem mô tả của một Enchant bất kì\n/ce list: Hiển thị danh sách enchant");
                    break;
                case "info":
                    if (!$sender->hasPermission("piggycustomenchants.command.ce.info")) {
                        $this->error($sender, TextFormat::RED . "Bạn không có quyền để làm điều này !");
                        return false;
                    }
                    if (count($args) < 2) {
                        if ($forms) {
                            $this->infoForm($sender);
                            return true;
                        }
                        $sender->sendMessage(TextFormat::RED . "Sử dụng: /customenchant info <enchant>");
                        return false;
                    }
                    if ($forms) {
                        $this->sendInfo($sender, $args[1]);
                        return true;
                    }
                    if ((is_numeric($args[1]) && ($enchant = CustomEnchants::getEnchantment($args[1])) !== null) || ($enchant = CustomEnchants::getEnchantmentByName($args[1])) !== null) {
                        $sender->sendMessage(TextFormat::GREEN . $enchant->getName() . "\n" . TextFormat::RESET . "▶ ID: " . $enchant->getId() . "\n▶ MÔ TẢ: " . $plugin->getEnchantDescription($enchant) . "\n▶ LOẠI: " . $plugin->getEnchantType($enchant) . "\n▶ ĐỘ HIẾM: " . $plugin->getEnchantRarity($enchant) . "\n▶ LEVEL TỐI ĐA: " . $plugin->getEnchantMaxLevel($enchant));
                    } else {
                        $sender->sendMessage(TextFormat::RED . "Enchant không hợp lệ.");
                    }
                    break;
                case "list":
                    if (!$sender->hasPermission("piggycustomenchants.command.ce.list")) {
                        $this->error($sender, TextFormat::RED . "Bạn không có quyền để làm điều này !");
                        return false;
                    }
                    if ($forms) {
                        $this->listForm($sender);
                        return true;
                    }
                    $sender->sendMessage($this->list());
                    break;
                default:
                    if ($forms) {
                        $this->formMenu($sender);
                        return true;
                    }
                    $sender->sendMessage(TextFormat::RED . "Sử dụng: /customenchant <about|enchant|help|info|list>");
                    break;
            }
            return true;
        }
        return false;
    }

    public function formMenu(Player $player)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            if ($plugin->formsEnabled && ($formsapi = $plugin->getServer()->getPluginManager()->getPlugin("FormAPI")) instanceof \jojoe77777\FormAPI\FormAPI && $formsapi->isEnabled()) {
                $form = $formsapi->createSimpleForm(function (Player $player, $data) {
                    if (isset($data[0])) {
                        switch ($data[0]) {
                            case 0:
                                if (!$player->hasPermission("piggycustomenchants.command.ce.about")) {
                                    $this->errorForm($player, TextFormat::RED . "Bạn không có quyền để làm điều này !");
                                    return false;
                                }
                                $this->aboutForm($player);
                                break;
                            case 1:
                                if (!$player->hasPermission("piggycustomenchants.command.ce.enchant")) {
                                    $this->errorForm($player, TextFormat::RED . "Bạn không có quyền để làm điều này !");
                                    return false;
                                }
                                $this->enchantForm($player);
                                break;
                            case 2:
                                if (!$player->hasPermission("piggycustomenchants.command.ce.help")) {
                                    $this->errorForm($player, TextFormat::RED . "Bạn không có quyền để làm điều này !");
                                    return false;
                                }
                                $this->helpForm($player);
                                break;
                            case 3:
                                if (!$player->hasPermission("piggycustomenchants.command.ce.info")) {
                                    $this->errorForm($player, TextFormat::RED . "Bạn không có quyền để làm điều này !");
                                    return false;
                                }
                                $this->infoForm($player);
                                break;
                            case 4:
                                if (!$player->hasPermission("piggycustomenchants.command.ce.list")) {
                                    $this->errorForm($player, TextFormat::RED . "Bạn không có quyền để làm điều này !");
                                    return false;
                                }
                                $this->listForm($player);
                                break;
                        }
                        return true;
                    }
                    return false;
                });
                $form->setTitle(TextFormat::GREEN . "── Custom Enchants Menu ──");
                $form->addButton("▶ Thông Tin Plugin");
                $form->addButton("▶ Phù Phép Item");
                $form->addButton("▶ Trợ Giúp");
                $form->addButton("▶ Thông Tin Enchants");
                $form->addButton("▶ Danh Sách Enchants");
                $form->sendToPlayer($player);
            }
        }
    }

    public function error(CommandSender $sender, $error)
    {
        if ($sender instanceof Player) {
            if ($this->errorForm($sender, $error)) {
                return true;
            }
        }
        $sender->sendMessage($error);
        return true;
    }

    public function errorForm(Player $player, $error)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            if ($plugin->formsEnabled && ($formsapi = $plugin->getServer()->getPluginManager()->getPlugin("FormAPI")) instanceof \jojoe77777\FormAPI\FormAPI && $formsapi->isEnabled()) {
                $form = $formsapi->createSimpleForm(function (Player $player, $data) {
                    if (isset($data[0])) {
                        $this->formMenu($player);
                        return true;
                    }
                    return false;
                });
                $form->setTitle(TextFormat::RED . "── Lỗi ──");
                $form->setContent($error);
                $form->addButton(TextFormat::BOLD . "Trở về");
                $form->sendToPlayer($player);
                return true;
            }
            return false;
        }
        return false;
    }

    public function aboutForm(Player $player)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            if ($plugin->formsEnabled && ($formsapi = $plugin->getServer()->getPluginManager()->getPlugin("FormAPI")) instanceof \jojoe77777\FormAPI\FormAPI && $formsapi->isEnabled()) {
                $form = $formsapi->createSimpleForm(function (Player $player, $data) {
                    if (isset($data[0])) {
                        $this->formMenu($player);
                        return true;
                    }
                    return false;
                });
                $form->setTitle(TextFormat::GREEN . "── Thông Tin Plugin ──");
                $form->setContent(TextFormat::GREEN . "▶ PiggyCustomEnchants v" . $this->getPlugin()->getDescription()->getVersion() . " là một plugin Custom Enchants được tạo bởi DaPigGuy (IGN: MCPEPIG) & Aericio & Translate by GamerSoiCon. Bạn có thể tìm thấy plugin tại https://github.com/DaPigGuy/PiggyCustomEnchants.");
                $form->addButton(TextFormat::BOLD . "Trở về");
                $form->sendToPlayer($player);
            }
        }
    }

    public function enchantForm(Player $player)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            if ($plugin->formsEnabled && ($formsapi = $plugin->getServer()->getPluginManager()->getPlugin("FormAPI")) instanceof \jojoe77777\FormAPI\FormAPI && $formsapi->isEnabled()) {
                $form = $formsapi->createCustomForm(function (Player $player, $data) {
                    if (isset($data[0]) && isset($data[1]) && isset($data[2])) {
                        $this->checkEnchantForm($player, $data);
                        return true;
                    }
                    return false;
                });
                $form->setTitle(TextFormat::GREEN . "── Enchant ──");
                $form->addInput(TextFormat::GREEN . "▶ Loại Enchant");
                $form->addInput(TextFormat::GREEN . "▶ Cấp Độ", "", 1);
                $form->addInput(TextFormat::GREEN . "▶ Người Chơi", "", $player->getName());
                $form->sendToPlayer($player);
                return true;
            }
            return false;
        }
        return false;
    }

    public function checkEnchantForm(Player $player, $data)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            if ($plugin->formsEnabled && ($formsapi = $plugin->getServer()->getPluginManager()->getPlugin("FormAPI")) instanceof \jojoe77777\FormAPI\FormAPI && $formsapi->isEnabled()) {
                $enchant = null;
                if (is_numeric($data[0])) {
                    $enchant = CustomEnchants::getEnchantment((int)$data[0]);
                } else {
                    $enchant = CustomEnchants::getEnchantmentByName($data[0]);
                }
                if ($enchant == null) {
                    $this->errorForm($player, TextFormat::RED . "Enchant không hợp lệ !");
                    return false;
                }
                $target = $this->getPlugin()->getServer()->getPlayer($data[2]);
                if (!$target instanceof Player) {
                    $this->errorForm($player, TextFormat::RED . "Không tìm thấy người chơi !");
                    return false;
                }
                if (!$player->hasPermission("piggycustomenchants.overridecheck")) {
                    $result = $plugin->canBeEnchanted($target->getInventory()->getItemInHand(), $enchant, $data[1]);
                    if ($result !== true) {
                        switch ($result) {
                            case Main::NOT_COMPATIBLE:
                                $this->errorForm($player, TextFormat::RED . "Item này không thể kết hợp với enchant !");
                                break;
                            case Main::NOT_COMPATIBLE_WITH_OTHER_ENCHANT:
                                $this->errorForm($player, TextFormat::RED . "Enchant này không thể kết hợp với enchant khác !");
                                break;
                            case Main::MAX_LEVEL:
                                $this->errorForm($player, TextFormat::RED . "Cấp độ tối đa là " . $plugin->getEnchantMaxLevel($enchant) . ".");
                                break;
                            case Main::MORE_THAN_ONE:
                                $this->errorForm($player, TextFormat::RED . "Bạn chỉ có thể enchant item với một khoảng thời gian nhất định !");
                                break;
                        }
                        return false;
                    }
                }
                $this->enchant($player, $data[0], $data[1], $data[2]);
                return true;
            }
            return false;
        }
        return false;
    }

    public function helpForm(Player $player)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            if ($plugin->formsEnabled && ($formsapi = $plugin->getServer()->getPluginManager()->getPlugin("FormAPI")) instanceof \jojoe77777\FormAPI\FormAPI && $formsapi->isEnabled()) {
                $form = $formsapi->createSimpleForm(function (Player $player, $data) {
                    if (isset($data[0])) {
                        $this->formMenu($player);
                        return true;
                    }
                    return false;
                });
                $form->setTitle(TextFormat::GREEN . "── Trợ Giúp ──");
                $form->setContent(TextFormat::GREEN . "▶ Nói cho tôi biết bạn đang nói đùa đi... Tại sao bạn thậm chí cần một trình đơn trợ giúp khi bạn có giao diện người dùng? Chờ đã ... tại sao điều này lại ở đây?");
                $form->addButton(TextFormat::BOLD . "Trở về");
                $form->sendToPlayer($player);
            }
        }
    }

    public function infoForm(Player $player)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            if ($plugin->formsEnabled && ($formsapi = $plugin->getServer()->getPluginManager()->getPlugin("FormAPI")) instanceof \jojoe77777\FormAPI\FormAPI && $formsapi->isEnabled()) {
                $form = $formsapi->createCustomForm(function (Player $player, $data) {
                    if (isset($data[0])) {
                        $this->sendInfo($player, $data[0]);
                    }
                });
                $form->setTitle(TextFormat::GREEN . "── Thông Tin Enchants ──");
                $form->addInput(TextFormat::GREEN . "▶ Enchantment");
                $form->sendToPlayer($player);
                return true;
            }
            return false;
        }
        return false;
    }

    public function sendInfo(Player $player, $enchant)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            if ($plugin->formsEnabled && ($formsapi = $plugin->getServer()->getPluginManager()->getPlugin("FormAPI")) instanceof \jojoe77777\FormAPI\FormAPI && $formsapi->isEnabled()) {
                $form = $formsapi->createSimpleForm(function (Player $player, $data) {
                    if (isset($data[0])) {
                        $this->formMenu($player);
                        return true;
                    }
                    return false;
                });
                if ((is_numeric($enchant) && ($enchant = CustomEnchants::getEnchantment($enchant)) !== null) || ($enchant = CustomEnchants::getEnchantmentByName($enchant)) !== null) {
                    $form->setTitle(TextFormat::GREEN . "── Thông Tin Enchants ──");
                    $form->setContent(TextFormat::GREEN . $enchant->getName() . "\n" . TextFormat::RESET . "▶ ID: " . $enchant->getId() . "\n▶ MÔ TẢ: " . $plugin->getEnchantDescription($enchant) . "\n▶ LOẠI: " . $plugin->getEnchantType($enchant) . "\n▶ ĐỘ HIẾM: " . $plugin->getEnchantRarity($enchant) . "\n▶ CẤP ĐỘ TỐI ĐA: " . $plugin->getEnchantMaxLevel($enchant));
                } else {
                    $form->setTitle(TextFormat::RED . "Lỗi");
                    $form->setContent(TextFormat::RED . "Enchant không hợp lệ !");
                }
                $form->addButton(TextFormat::BOLD . "Trở về");
                $form->sendToPlayer($player);
                return true;
            }
            return false;
        }
        return false;
    }

    public function listForm(Player $player)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            if ($plugin->formsEnabled && ($formsapi = $plugin->getServer()->getPluginManager()->getPlugin("FormAPI")) instanceof \jojoe77777\FormAPI\FormAPI && $formsapi->isEnabled()) {
                $form = $formsapi->createSimpleForm(function (Player $player, $data) {
                    if (isset($data[0])) {
                        $sorted = $this->getPlugin()->sortEnchants();
                        foreach ($sorted as $type => $enchants) {
                            if (array_search($type, array_keys($sorted)) == $data[0]) {
                                $this->sendList($player, $type);
                                return true;
                            }
                        }
                        $this->formMenu($player);
                    }
                    return false;
                });
                $form->setTitle(TextFormat::GREEN . "── Danh Sách Enchants ──");
                $sorted = $plugin->sortEnchants();
                foreach ($sorted as $type => $enchants) {
                    $form->addButton($type);
                }
                $form->addButton(TextFormat::BOLD . "Trở về");
                $form->sendToPlayer($player);
                return true;
            }
            return false;
        }
        return false;
    }

    public function sendList(Player $player, $type)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            if ($plugin->formsEnabled && ($formsapi = $plugin->getServer()->getPluginManager()->getPlugin("FormAPI")) instanceof \jojoe77777\FormAPI\FormAPI && $formsapi->isEnabled()) {
                $form = $formsapi->createSimpleForm(function (Player $player, $data) {
                    if (isset($data[0])) {
                        $this->formMenu($player);
                        return true;
                    }
                    return false;
                });
                $form->setContent(TextFormat::GREEN . TextFormat::BOLD . $type . "\n" . TextFormat::RESET . implode(", ", $this->getPlugin()->sortEnchants()[$type]));
                $form->addButton(TextFormat::BOLD . "Trở về");
                $form->sendToPlayer($player);
            }
        }
    }

    public function enchant(CommandSender $sender, $enchantment, $level, $target)
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            if (!is_numeric($level)) {
                $level = 1;
                $sender->sendMessage(TextFormat::RED . "Cấp độ phải là số !");
            }
            $target == null ? $target = $sender : $target = $this->getPlugin()->getServer()->getPlayer($target);
            if (!$target instanceof Player) {
                if ($target instanceof ConsoleCommandSender) {
                    $sender->sendMessage(TextFormat::RED . "Hãy điền tên người chơi !");
                    return false;
                }
                $sender->sendMessage(TextFormat::RED . "Người chơi không hợp lệ !");
                return false;
            }
            $target->getInventory()->setItemInHand($plugin->addEnchantment($target->getInventory()->getItemInHand(), $enchantment, $level, $sender->hasPermission("piggycustomenchants.overridecheck") ? false : true, $sender));
            return true;
        }
        return false;
    }


    public function list()
    {
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            $sorted = $plugin->sortEnchants();
            $list = "";
            foreach ($sorted as $type => $enchants) {
                $list .= "\n" . TextFormat::GREEN . TextFormat::BOLD . $type . "\n" . TextFormat::RESET;
                $list .= implode(", ", $enchants);
            }
            return $list;
        }
        return "";
    }
}
