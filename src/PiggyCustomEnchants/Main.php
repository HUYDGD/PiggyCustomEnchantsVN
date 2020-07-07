<?php

namespace PiggyCustomEnchants;

use PiggyCustomEnchants\Blocks\PiggyObsidian;
use PiggyCustomEnchants\Commands\CustomEnchantCommand;
use PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use PiggyCustomEnchants\CustomEnchants\CustomEnchantsIds;
use PiggyCustomEnchants\Entities\PiggyFireball;
use PiggyCustomEnchants\Entities\PiggyLightning;
use PiggyCustomEnchants\Entities\PiggyWitherSkull;
use PiggyCustomEnchants\Entities\PigProjectile;
use PiggyCustomEnchants\Entities\VolleyArrow;
use PiggyCustomEnchants\Tasks\AutoAimTask;
use PiggyCustomEnchants\Tasks\CactusTask;
use PiggyCustomEnchants\Tasks\ChickenTask;
use PiggyCustomEnchants\Tasks\EffectTask;
use PiggyCustomEnchants\Tasks\ForcefieldTask;
use PiggyCustomEnchants\Tasks\JetpackTask;
use PiggyCustomEnchants\Tasks\MeditationTask;
use PiggyCustomEnchants\Tasks\ParachuteTask;
use PiggyCustomEnchants\Tasks\PoisonousGasTask;
use PiggyCustomEnchants\Tasks\ProwlTask;
use PiggyCustomEnchants\Tasks\RadarTask;
use PiggyCustomEnchants\Tasks\SizeTask;
use PiggyCustomEnchants\Tasks\SpiderTask;
use PiggyCustomEnchants\Tasks\VacuumTask;

use pocketmine\block\BlockFactory;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\item\Armor;
use pocketmine\item\Axe;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Hoe;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\Pickaxe;
use pocketmine\item\Shears;
use pocketmine\item\Shovel;
use pocketmine\item\Sword;
use pocketmine\level\Position;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Main extends PluginBase
{
    const MAX_LEVEL = 0;
    const NOT_COMPATIBLE = 1;
    const NOT_COMPATIBLE_WITH_OTHER_ENCHANT = 2;
    const MORE_THAN_ONE = 3;

    const ROMAN_CONVERSION_TABLE = [
        'M' => 1000,
        'CM' => 900,
        'D' => 500,
        'CD' => 400,
        'C' => 100,
        'XC' => 90,
        'L' => 50,
        'XL' => 40,
        'X' => 10,
        'IX' => 9,
        'V' => 5,
        'IV' => 4,
        'I' => 1
    ];

    const COLOR_CONVERSION_TABLE = [
        "BLACK" => TextFormat::BLACK,
        "DARK_BLUE" => TextFormat::DARK_BLUE,
        "DARK_GREEN" => TextFormat::DARK_GREEN,
        "DARK_AQUA" => TextFormat::DARK_AQUA,
        "DARK_RED" => TextFormat::DARK_RED,
        "DARK_PURPLE" => TextFormat::DARK_PURPLE,
        "GOLD" => TextFormat::GOLD,
        "GRAY" => TextFormat::GRAY,
        "DARK_GRAY" => TextFormat::DARK_GRAY,
        "BLUE" => TextFormat::BLUE,
        "GREEN" => TextFormat::GREEN,
        "AQUA" => TextFormat::AQUA,
        "RED" => TextFormat::RED,
        "LIGHT_PURPLE" => TextFormat::LIGHT_PURPLE,
        "YELLOW" => TextFormat::YELLOW,
        "WHITE" => TextFormat::WHITE
    ];

    const PIGGY_ENTITIES = [
        PiggyFireball::class,
        PiggyLightning::class,
        PigProjectile::class,
        VolleyArrow::class,
        PiggyWitherSkull::class
    ];

    public $berserkercd;
    public $bountyhuntercd;
    public $cloakingcd;
    public $endershiftcd;
    public $growcd;
    public $implantscd;
    public $jetpackcd;
    public $shrinkcd;
    public $vampirecd;

    public $growremaining;
    public $jetpackDisabled;
    public $shrinkremaining;
    public $flyremaining;

    public $chickenTick;
    public $forcefieldParticleTick;
    public $gasParticleTick;
    public $jetpackChargeTick;
    public $meditationTick;

    public $blockface;

    public $glowing;
    public $grew;
    public $flying;
    public $hallucination;
    public $implants;
    public $mined;
    public $moved;
    public $nofall;
    public $overload;
    public $prowl;
    public $using;
    public $shrunk;

    public $formsEnabled = false;

    public static $lightningFlames = false;
    public static $blazeFlames = false;

    public $enchants = [
        CustomEnchantsIds::ANTIKNOCKBACK => ["Anti Knockback", "Armor", "Damage", "Rare", 1, "Giảm 25% knockback cho bộ giáp khi Enchant."],
        CustomEnchantsIds::ANTITOXIN => ["Antitoxin", "Helmets", "Effect", "Mythic", 1, "Miễn nhiễm với chất độc !"],
        CustomEnchantsIds::AERIAL => ["Aerial", "Weapons", "Damage", "Common", 5, "Tăng sát thương khi đánh trên trời."],
        CustomEnchantsIds::ARMORED => ["Armored", "Armor", "Damage", "Rare", 5, "Giảm sát thương của kiếm xuống 20l% khi bị đánh !"],
        CustomEnchantsIds::AUTOAIM => ["Auto Aim", "Bow", "Held", "Mythic", 1, "Xác định mục tiêu đang ở gần."],
        CustomEnchantsIds::AUTOREPAIR => ["Autorepair", "Damageable", "Move", "Uncommon", 5, "Tự động sửa chữa vật phẩm khi di chuyển."],
        CustomEnchantsIds::BACKSTAB => ["Backstab", "Weapons", "Damage", "Uncommon", 5, "Khi đánh người chơi từ phía sau (Móc lốt), bạn sẽ nhận được nhiều sát thương hơn !"],
        CustomEnchantsIds::BERSERKER => ["Berserker", "Armor", "Damaged", "Rare", 5, "Tăng sức đánh khi máu còn ít."],
        CustomEnchantsIds::BLESSED => ["Blessed", "Weapons", "Damage", "Uncommon", 3, "15l% (l = cấp CE) khả năng loại bỏ hiệu ứng xấu."],
        CustomEnchantsIds::BLAZE => ["Blaze", "Bow", "Shoot", "Rare", 1, "Bắn ra quả cầu lửa !!!"],
        CustomEnchantsIds::BLIND => ["Blind", "Weapons", "Damage", "Common", 5, "Làm cho đối thủ bị mù !"],
        CustomEnchantsIds::BOUNTYHUNTER => ["Bounty Hunter", "Bow", "Damage", "Uncommon", 5, "Nhận được vật phẩm khi bắn người chơi."],
        CustomEnchantsIds::CACTUS => ["Cactus", "Armor", "Equip", "Rare", 1, "Poke people around you", "Poke people around you"],
        CustomEnchantsIds::CHARGE => ["Charge", "Weapons", "Damage", "Uncommon", 5, "Tăng sát thương khi chạy nước rút."],
        CustomEnchantsIds::CHICKEN => ["Chicken", "Chestplate", "Equip", "Uncommon", 5, "Đẻ ra trứng mỗi 5 phút !, 5l% (l = cấp CE) cơ hội rơi ra đồ hiếm."],
        CustomEnchantsIds::CLOAKING => ["Cloaking", "Armor", "Damaged", "Uncommon", 5, "Trở thành người tàng hình khi bị đánh..."],
        CustomEnchantsIds::CRIPPLINGSTRIKE => ["Cripple", "Weapons", "Damage", "Common", 5, "Gây hiệu ứng buồn nôn và đi chậm cho kẻ thù !"],
        CustomEnchantsIds::CRIPPLE => ["Cripple", "Weapons", "Damage", "Common", 5, "Gây hiệu ứng buồn nôn và đi chậm cho kẻ thù !"],
        CustomEnchantsIds::CURSED => ["Cursed", "Armor", "Damaged", "Uncommon", 5, "Làm cho đối thủ bị hiệu ứng Khô khi đánh."],
        CustomEnchantsIds::DEATHBRINGER => ["Deathbringer", "Weapons", "Damage", "Rare", 5, "Tăng sát thương"],
        CustomEnchantsIds::DISARMING => ["Disarming", "Weapons", "Damage", "Uncommon", 5, "10l% cơ hội làm mất đồ vật trên tay đối thủ !"],
        CustomEnchantsIds::DISARMOR => ["Disarmor", "Weapons", "Damage", "Uncommon", 5, "10l% cơ hội làm mất áo giáp của đối thủ !"],
        CustomEnchantsIds::DRILLER => ["Driller", "Tools", "Break", "Uncommon", 5, "Phá hủy 3x3 blocks cho mỗi cấp CE !"],
        CustomEnchantsIds::DRUNK => ["Drunk", "Armor", "Damaged", "Rare", 5, "Gây hiệu ứng chậm, đào chậm và buồn nôn đến đối thủ khi đánh !"],
        CustomEnchantsIds::ENDERSHIFT => ["Endershift", "Armor", "Damaged", "Rare", 5, "Tăng tốc độ và máu khi người chơi sắp chết."],
        CustomEnchantsIds::ENERGIZING => ["Energizing", "Tools", "Break", "Uncommon", 5, "Tăng tốc độ đào khi đập block."],
        CustomEnchantsIds::ENLIGHTED => ["Enlighted", "Armor", "Damaged", "Uncommon", 5, "Cung cấp hiệu ứng hồi máu khi đánh."],
        CustomEnchantsIds::ENRAGED => ["Enraged", "Chestplate", "Equip", "Rare", 5, "Tăng sức mạnh cho mỗi cấp CE"],
        CustomEnchantsIds::EXPLOSIVE => ["Explosive", "Tools", "Break", "Rare", 5, "Gây ra vụ nổ lớn khi đập một block."],
        CustomEnchantsIds::FARMER => ["Farmer", "Hoe", "Break", "Uncommon", 1, "Tự động trồng lại cây khi thu hoạch."],
        CustomEnchantsIds::FERTILIZER => ["Fertilizer", "Hoe", "Interact", "Uncommon", 3, "Tạo ra một khu nông nghiệp trong một bán kính xung quanh khối."],
        CustomEnchantsIds::FOCUSED => ["Focused", "Helmets", "Effect", "Uncommon", 5, "Giảm ảnh hưởng của effect Buồn Nôn."],
        CustomEnchantsIds::FORCEFIELD => ["Forcefield", "Armor", "Equip", "Mythic", 5, "Làm lệch các mũi tên và các thực thể sống trong 0.75x (x = # áo giáp)"],
        CustomEnchantsIds::FROZEN => ["Frozen", "Armor", "Damaged", "Rare", 5, "Tăng tốc độ khi đánh !"],
        CustomEnchantsIds::GEARS => ["Gears", "Boots", "Equip", "Uncommon", 5, "Cung cấp effect Speed!"],
        CustomEnchantsIds::GLOWING => ["Glowing", "Helmets", "Equip", "Common", 1, "Cung cấp khả năng nhìn trong bóng tối."],
        CustomEnchantsIds::GOOEY => ["Gooey", "Weapons", "Damage", "Uncommon", 5, "Ném kẻ thù vào không khí."],
        CustomEnchantsIds::GRAPPLING => ["Grappling", "Bow", "Projectile_Hit", "Rare", 1, "Kéo bạn đến vị trí của mũi tên khi bắn. Nếu kẻ thù bị bắn, hắn sẽ bị kéo đến bạn !"],
        CustomEnchantsIds::GROW => ["Grow", "Armor", "Sneak", "Uncommon", 5, "Tăng kích thước cơ thể khi ngồi (Phải mặc đủ bộ giáp Enchant Grow)"],
        CustomEnchantsIds::HALLUCINATION => ["Hallucination", "Weapons", "Damage", "Mythic", 5, "5l% (l = cấp CE) cơ hội nhốt kẻ thù trong nhà tù giả !"],
        CustomEnchantsIds::HARDENED => ["Hardened", "Armor", "Damaged", "Uncommon", 5, "Gây hiệu ứng yếu đến kẻ thù khi đánh !"],
        CustomEnchantsIds::HASTE => ["Haste", "Tools", "Held", "Uncommon", 5, "Cung cấp effect Đào Nhanh khi cầm."],
        CustomEnchantsIds::HARVEST => ["Harvest", "Hoe", "Break", "Uncommon", 3, "Thu hoạch cây trồng trong vòng một bán kính."],
        CustomEnchantsIds::HEADHUNTER => ["Headhunter", "Bow", "Damage", "Uncommon", 5, "Tăng sát thương khi bắn vào đầu."],
        CustomEnchantsIds::HEALING => ["Healing", "Bow", "Damage", "Rare", 5, "Hồi máu khi bắn trúng mục tiêu."],
        CustomEnchantsIds::HEAVY => ["Heavy", "Armor", "Damage", "Rare", 5, "Giảm sát thương từ rìu 20l%"],
        CustomEnchantsIds::IMPLANTS => ["Implants", "Helmets", "Move", "Rare", 5, "Bổ sung thanh đói và không khí."],
        CustomEnchantsIds::JETPACK => ["Jetpack", "Boots", "Sneak", "Rare", 3, "Cho phép bay (Bay ở bất kì nơi nào bạn nhìn đến) khi bạn ngồi."],
        CustomEnchantsIds::JACKPOT => ["Jackpot", "Tools", "Break", "Mythic", 10, "10l% cơ hội tăng khả năng có khoáng sản."],
        CustomEnchantsIds::LIFESTEAL => ["Lifesteal", "Weapons", "Damage", "Common", 5, "Hồi máu khi đánh đối thủ."],
        CustomEnchantsIds::LIGHTNING => ["Lightning", "Weapons", "Damage", "Rare", 5, "10l% cơ hội để giật điện đối thủ !"],
        CustomEnchantsIds::LUMBERJACK => ["Lumberjack", "Axe", "Break", "Rare", 1, "Khai thác tất cả các bản ghi khi nhật kí bị hỏng !"],
        CustomEnchantsIds::MAGMAWALKER => ["Magma Walker", "Boots", "Move", "Uncommon", 2, "Biến dung nham thành Hắc Diện Thạch xung quanh bạn."],
        CustomEnchantsIds::MEDITATION => ["Meditation", "Helmets", "Equip", "Uncommon", 5, "Tăng cường sức khỏe và đói mỗi 20 giây (một nửa thanh đói / tim mỗi cấp CE)"],
        CustomEnchantsIds::MISSILE => ["Missile", "Bow", "Projectile_Hit", "Rare", 5, "Tạo ra TNT khi đánh !!!"],
        CustomEnchantsIds::MOLOTOV => ["Molotov", "Bow", "Projectile_Hit", "Uncommon", 5, "Bắt lửa xung quanh mục tiêu."],
        CustomEnchantsIds::MOLTEN => ["Molten", "Armor", "Damaged", "Rare", 5, "Đặt đối thủ lên lửa khi đánh."],
        CustomEnchantsIds::OBSIDIANSHIELD => ["Obsidian Shield", "Armor", "Equip", "Common", 5, "Cung cấp khả năng chống cháy !"],
        CustomEnchantsIds::OVERLOAD => ["Overload", "Armor", "Equip", "Mythic", 3, "Cung cấp thêm 1 trái tim cho mỗi cấp độ cho mỗi áo giáp"],
        CustomEnchantsIds::OXYGENATE => ["Oxygenate", "Tools", "Break", "Uncommon", 1, "Có thể thở dưới nước khi cầm."],
        CustomEnchantsIds::PARACHUTE => ["Parachute", "Chestplate", "Equip", "Uncommon", 1, "Làm chậm khi rơi xuống (trên 3 blocks)"],
        CustomEnchantsIds::PARALYZE => ["Paralyze", "Bow", "Damage", "Rare", 5, "Gây hiệu ứng chậm, mù, và yếu..."],
        CustomEnchantsIds::PIERCING => ["Piercing", "Bow", "Damage", "Rare", 5, "Bắn xuyên áo giáp của đối thủ."],
        CustomEnchantsIds::POISON => ["Poison", "Weapons", "Damage", "Uncommon", 5, "Gây độc cho người chơi"],
        CustomEnchantsIds::POISONOUSCLOUD => ["Poisonous Cloud", "Armor", "Equip", "Rare", 3, ""],
        CustomEnchantsIds::POISONED => ["Poisoned", "Armor", "Damaged", "Uncommon", 5, "Gây độc cho đối thủ khi đánh."],
        CustomEnchantsIds::PORKIFIED => ["Porkified", "Bow", "Shoot", "Mythic", 3, "Bắn lợn !!"],
        CustomEnchantsIds::PROWL => ["Prowl", "Chestplate", "Equip", "Rare", 1, "Tàng hình khi ngồi."],
        CustomEnchantsIds::QUICKENING => ["Quickening", "Tools", "Break", "Uncommon", 5, "Tăng tốc độ chạy khi đập block."],
        CustomEnchantsIds::RADAR => ["Radar", "Compass", "Inventory", "Rare", 5, "Xác định người chơi ở gần trong 50l (l = cấp CE) blocks."],
        CustomEnchantsIds::REVIVE => ["Revive", "Armor", "Death", "Rare", 5, "Có thể hồi sinh khi bạn chết (có thể làm yếu/xóa bỏ CE)"],
        CustomEnchantsIds::REVULSION => ["Revulsion", "Armor", "Damaged", "Uncommon", 5, "Gây buồn nôn cho đối thủ khi đánh."],
        CustomEnchantsIds::SELFDESTRUCT => ["Self Destruct", "Armor", "Damaged", "Rare", 5, "Tạo ra TNT khi bạn chết !!!"],
        CustomEnchantsIds::SHIELDED => ["Shielded", "Armor", "Equip", "Rare", 3, "Tăng tốc độ hồi máu khi mặc !"],
        CustomEnchantsIds::SHRINK => ["Shrink", "Armor", "Sneak", "Uncommon", 2, "Thu nhỏ cơ thể khi ngồi ! (Phải mặc đủ bộ giáp Enchant Shrink)"],
        CustomEnchantsIds::SHUFFLE => ["Shuffle", "Bow", "Damage", "Rare", 1, "Dịch chuyển đến mục tiêu."],
        CustomEnchantsIds::SMELTING => ["Smelting", "Tools", "Break", "Uncommon", 1, "Tự động làm hỏng block khi đập."],
        CustomEnchantsIds::SOULBOUND => ["Soulbound", "Global", "Death", "Mythic", 5, "Giữ đồ vật khi bạn chết ! (có thể sẽ xóa bỏ Enchant)"],
        CustomEnchantsIds::SPIDER => ["Spider", "Chestplate", "Equip", "Rare", 1, "Leo tường..."],
        CustomEnchantsIds::SPRINGS => ["Springs", "Boots", "Equip", "Uncommon", 5, "Cho hiệu ứng nhảy cao."],
        CustomEnchantsIds::STOMP => ["Stomp", "Boots", "Fall_Damage", "Uncommon", 5, "Chuyển sát thương sang cho đối thủ khi bạn rơi."],
        CustomEnchantsIds::TANK => ["Tank", "Armor", "Damage", "Uncommon", 5, "Tạo ra TNT khi bạn chết !!!"],
        CustomEnchantsIds::TELEPATHY => ["Telepathy", "Tools", "Break", "Rare", 1, "Tự động lụm item xung quanh bạn."],
        CustomEnchantsIds::VACUUM => ["Vacuum", "Chestplate", "Equip", "Rare", 3, "Nhét các item trong bán kính 3l."],
        CustomEnchantsIds::VAMPIRE => ["Vampire", "Weapons", "Damage", "Uncommon", 1, "Hút máu đối thủ..."],
        CustomEnchantsIds::VOLLEY => ["Volley", "Bow", "Shoot", "Uncommon", 5, "Bắn nhiều mũi tên trong hình Cái Nón."],
        CustomEnchantsIds::WITHER => ["Wither", "Weapons", "Damage", "Uncommon", 5, "Gây cho đối thủ effect Khô Héo."],
        CustomEnchantsIds::WITHERSKULL => ["Wither Skull", "Bow", "Shoot", "Mythic", 1, "Bắn ra cái đầu của Wither !"],
        CustomEnchantsIds::PLACEHOLDER => ["Placeholder", "Bow", "Shoot", "Rare", 1, ""]
    ];

    public $incompatibilities = [
        CustomEnchantsIds::GROW => [CustomEnchantsIds::SHRINK],
        CustomEnchantsIds::PORKIFIED => [CustomEnchantsIds::BLAZE, CustomEnchantsIds::WITHERSKULL],
        CustomEnchantsIds::VOLLEY => [CustomEnchantsIds::GRAPPLING]
    ];

    public function onEnable()
    {
        if (!$this->isSpoon()) {
            $this->initCustomEnchants();
            $this->saveDefaultConfig();
            if ($this->getConfig()->getNested("forms.enabled")) {
                if ($this->getServer()->getPluginManager()->getPlugin("FormAPI") !== null) {
                    $this->formsEnabled = true;
                } else {
                    $this->getLogger()->error("Form hiện đã bật nhưng hệ thống không tìm thấy plugin FormAPI ! Vui lòng cài tại: http://123link.pw/1v62p");
                }
            }
            if ($this->getConfig()->getNested("blaze.flames")) {
                self::$blazeFlames = true;
            }
            if ($this->getConfig()->getNested("lightning.flames")) {
                self::$lightningFlames = true;
            }
            $this->jetpackDisabled = $this->getConfig()->getNested("jetpack.disabled") ?? [];
            if (count($this->jetpackDisabled) > 0) {
                $this->getLogger()->info(TextFormat::RED . "Jetpack hiện đang bị vô hiệu hóa ở thế giới: " . implode(", ", $this->jetpackDisabled) . ".");
            }
            BlockFactory::registerBlock(new PiggyObsidian(), true);
            foreach (self::PIGGY_ENTITIES as $piggyEntity) {
                Entity::registerEntity($piggyEntity, true);
            }

            if (!ItemFactory::isRegistered(Item::ENCHANTED_BOOK)) {
                ItemFactory::registerItem(new Item(Item::ENCHANTED_BOOK, 0, "Sách CustomEnchants"));
            }
            $this->getServer()->getCommandMap()->register("customenchant", new CustomEnchantCommand("customenchant", $this));
            $this->getServer()->getScheduler()->scheduleRepeatingTask(new AutoAimTask($this), 1);
            $this->getServer()->getScheduler()->scheduleRepeatingTask(new CactusTask($this), 10);
            $this->getServer()->getScheduler()->scheduleRepeatingTask(new ChickenTask($this), 20);
            $this->getServer()->getScheduler()->scheduleRepeatingTask(new ForcefieldTask($this), 1);
            $this->getServer()->getScheduler()->scheduleRepeatingTask(new EffectTask($this), 5);
            $this->getServer()->getScheduler()->scheduleRepeatingTask(new JetpackTask($this), 1);
            $this->getServer()->getScheduler()->scheduleRepeatingTask(new MeditationTask($this), 20);
            $this->getServer()->getScheduler()->scheduleRepeatingTask(new ParachuteTask($this), 2);
            $this->getServer()->getScheduler()->scheduleRepeatingTask(new ProwlTask($this), 1);
            $this->getServer()->getScheduler()->scheduleRepeatingTask(new RadarTask($this), 1);
            $this->getServer()->getScheduler()->scheduleRepeatingTask(new SizeTask($this), 20);
            $this->getServer()->getScheduler()->scheduleRepeatingTask(new SpiderTask($this), 1);
            $this->getServer()->getScheduler()->scheduleRepeatingTask(new PoisonousGasTask($this), 1);
            $this->getServer()->getScheduler()->scheduleRepeatingTask(new VacuumTask($this), 1);
            $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);

            $this->getLogger()->info(TextFormat::GREEN . "ĐÃ HOẠT ĐỘNG !");
        }
    }

    public function isSpoon()
    {
        if ($this->getServer()->getName() !== "PocketMine-MP") {
            $this->getLogger()->error("Heo không hề thích spoons !. Do đó plugin sẽ không hoạt động cho đến khi bạn sử dụng PocketMine-MP !");
            return true;
        }
        if ($this->getDescription()->getAuthors() !== ["DaPigGuy"] || $this->getDescription()->getName() !== "PiggyCustomEnchants") {
            $this->getLogger()->error("Bạn hiện không sử dụng bản gốc của plugin này !. Hãy sử dụng (PiggyCustomEnchants) bởi DaPigGuy/MCPEPIG.");
            return true;
        }
        return false;
    }

    public function initCustomEnchants()
    {
        CustomEnchants::init();
        foreach ($this->enchants as $id => $data) {
            $ce = $this->translateDataToCE($id, $data);
            CustomEnchants::registerEnchantment($ce);
        }
    }

    public function registerEnchantment($id, $name, $type, $trigger, $rarity, $maxlevel, $description = "")
    {
        $data = [$name, $type, $trigger, $rarity, $maxlevel, $description];
        $this->enchants[$id] = $data;
        $ce = $this->translateDataToCE($id, $data);
        CustomEnchants::registerEnchantment($ce);
    }

    public function unregisterEnchantment($id)
    {
        if (isset($this->enchants[$id]) && CustomEnchants::getEnchantment($id) !== null) {
            unset($this->enchants[$id]);
            CustomEnchants::unregisterEnchantment($id);
            return true;
        }
        return false;
    }

    public function addIncompatibility(int $id, array $incompatibilities)
    {
        if (!isset($this->incompatibilities[$id])) {
            $this->incompatibilities[$id] = $incompatibilities;
            return true;
        }
        return false;
    }

    public function translateDataToCE($id, $data)
    {
        $slot = CustomEnchants::SLOT_NONE;
        switch ($data[1]) {
            case "Global":
                $slot = CustomEnchants::SLOT_ALL;
                break;
            case "Weapons":
                $slot = CustomEnchants::SLOT_SWORD;
                break;
            case "Bow":
                $slot = CustomEnchants::SLOT_BOW;
                break;
            case "Tools":
                $slot = CustomEnchants::SLOT_TOOL;
                break;
            case "Pickaxe":
                $slot = CustomEnchants::SLOT_PICKAXE;
                break;
            case "Axe":
                $slot = CustomEnchants::SLOT_AXE;
                break;
            case "Shovel":
                $slot = CustomEnchants::SLOT_SHOVEL;
                break;
            case "Hoe":
                $slot = CustomEnchants::SLOT_HOE;
                break;
            case "Armor":
                $slot = CustomEnchants::SLOT_ARMOR;
                break;
            case "Helmets":
                $slot = CustomEnchants::SLOT_HEAD;
                break;
            case "Chestplate":
                $slot = CustomEnchants::SLOT_TORSO;
                break;
            case "Leggings":
                $slot = CustomEnchants::SLOT_LEGS;
                break;
            case "Boots":
                $slot = CustomEnchants::SLOT_FEET;
                break;
            case "Compass":
                $slot = 0b10000000000000;
                break;
        }
        $rarity = CustomEnchants::RARITY_COMMON;
        switch ($data[3]) {
            case "Common":
                $rarity = CustomEnchants::RARITY_COMMON;
                break;
            case "Uncommon":
                $rarity = CustomEnchants::RARITY_UNCOMMON;
                break;
            case "Rare":
                $rarity = CustomEnchants::RARITY_RARE;
                break;
            case "Mythic":
                $rarity = CustomEnchants::RARITY_MYTHIC;
                break;
        }
        $ce = new CustomEnchants($id, $data[0], $rarity, $slot, CustomEnchants::SLOT_NONE, $data[4]);
        return $ce;
    }

    public function addEnchantment(Item $item, $enchants, $levels, $check = true, CommandSender $sender = null)
    {
        if (!is_array($enchants)) {
            $enchants = [$enchants];
        }
        if (!is_array($levels)) {
            $levels = [$levels];
        }
        if (count($enchants) > count($levels)) {
            for ($i = 0; $i <= count($enchants) - count($levels); $i++) {
                $levels[] = 1;
            }
        }
        $combined = array_combine($enchants, $levels);
        foreach ($enchants as $enchant) {
            $level = $combined[$enchant];
            if (!$enchant instanceof CustomEnchants) {
                if (is_numeric($enchant)) {
                    $enchant = CustomEnchants::getEnchantment((int)$enchant);
                } else {
                    $enchant = CustomEnchants::getEnchantmentByName($enchant);
                }
            }
            if ($enchant == null) {
                if ($sender !== null) {
                    $sender->sendMessage(TextFormat::RED . "Enchant không hợp lệ !");
                }
                continue;
            }
            $result = $this->canBeEnchanted($item, $enchant, $level);
            if ($result === true || $check !== true) {
                if ($item->getId() == Item::BOOK) {
                    $item = Item::get(Item::ENCHANTED_BOOK, $level);
                }
                $ench = $item->getNamedTagEntry(Item::TAG_ENCH);
                $found = false;
                if (!($ench instanceof ListTag)) {
                    $ench = new ListTag(Item::TAG_ENCH, [], NBT::TAG_Compound);
                } else {
                    foreach ($ench as $k => $entry) {
                        if ($entry->getShort("id") === $enchant->getId()) {
                            $ench->set($k, new CompoundTag("", [
                                new ShortTag("id", $enchant->getId()),
                                new ShortTag("lvl", $level)
                            ]));
                            $item->setCustomName(str_replace($this->getRarityColor($enchant->getRarity()) . $enchant->getName() . " " . $this->getRomanNumber($entry["lvl"]), $this->getRarityColor($enchant->getRarity()) . $enchant->getName() . " " . $this->getRomanNumber($level), $item->getName()));
                            $found = true;
                            break;
                        }
                    }
                }
                if (!$found) {
                    $ench->push(new CompoundTag("", [
                        new ShortTag("id", $enchant->getId()),
                        new ShortTag("lvl", $level)
                    ]));
                    $item->setCustomName($item->getName() . "\n" . $this->getRarityColor($enchant->getRarity()) . $enchant->getName() . " " . $this->getRomanNumber($level));
                }
                $item->setNamedTagEntry($ench);
                if ($sender !== null) {
                    $sender->sendMessage(TextFormat::GREEN . "Enchant thành công !");
                }
                continue;
            }
            if ($sender !== null) {
                switch ($result) {
                    case self::NOT_COMPATIBLE:
                        $sender->sendMessage(TextFormat::RED . "Item này không thể kết hợp với enchant !");
                        break;
                    case self::NOT_COMPATIBLE_WITH_OTHER_ENCHANT:
                        $sender->sendMessage(TextFormat::RED . "Enchant này không thể kết hợp với các loại enchant khác !");
                        break;
                    case self::MAX_LEVEL:
                        $sender->sendMessage(TextFormat::RED . "Cấp độ tối đa là " . $this->getEnchantMaxLevel($enchant) . ".");
                        break;

                    case self::MORE_THAN_ONE:
                        $sender->sendMessage(TextFormat::RED . "Bạn chỉ có thể enchant item trong một khoảng thời gian nhất định !");
                        break;
                }
            }
            continue;
        }
        return $item;
    }

    public function removeEnchantment(Item $item, $enchant, $level = -1)
    {
        if (!$item->hasEnchantments()) {
            return false;
        }
        if ($enchant instanceof EnchantmentInstance) {
            $enchant = $enchant->getType();
        }
        $ench = $item->getNamedTagEntry(Item::TAG_ENCH);
        if (!($ench instanceof ListTag)) {
            return false;
        }
        foreach ($ench as $k => $entry) {
            if ($entry->getShort("id") === $enchant->getId() and ($level === -1 or $entry->getShort("lvl") === $level)) {
                $ench->remove($k);
                $item->setCustomName(str_replace("\n" . $this->getRarityColor($enchant->getRarity()) . $enchant->getName() . " " . $this->getRomanNumber($entry->getShort("lvl")), "", $item->getCustomName()));
                break;
            }
        }
        $item->setNamedTagEntry($ench);
        return $item;
    }

    public function getEnchantType(CustomEnchants $enchant)
    {
        foreach ($this->enchants as $id => $data) {
            if ($enchant->getId() == $id) {
                return $data[1];
            }
        }
        return "Unknown";
    }

    public function getEnchantRarity(CustomEnchants $enchant)
    {
        foreach ($this->enchants as $id => $data) {
            if ($enchant->getId() == $id) {
                return $data[3];
            }
        }
        return "Common";
    }

    public function getEnchantMaxLevel(CustomEnchants $enchant)
    {
        foreach ($this->enchants as $id => $data) {
            if ($enchant->getId() == $id) {
                return $data[4];
            }
        }
        return 5;
    }

    public function getEnchantDescription(CustomEnchants $enchant)
    {
        foreach ($this->enchants as $id => $data) {
            if ($enchant->getId() == $id) {
                return $data[5];
            }
        }
        return "Unknown";
    }

    public function sortEnchants()
    {
        $sorted = [];
        foreach ($this->enchants as $id => $data) {
            $type = $data[1];
            if (!isset($sorted[$type])) {
                $sorted[$type] = [$data[0]];
            } else {
                array_push($sorted[$type], $data[0]);
            }
        }
        return $sorted;
    }

    public function getRomanNumber($integer)
    {
        $romanString = "";
        while ($integer > 0) {
            foreach (self::ROMAN_CONVERSION_TABLE as $rom => $arb) {
                if ($integer >= $arb) {
                    $integer -= $arb;
                    $romanString .= $rom;
                    break;
                }
            }
        }
        return $romanString;
    }

    public function getRarityColor($rarity)
    {
        switch ($rarity) {
            case CustomEnchants::RARITY_COMMON:
                $color = strtoupper($this->getConfig()->getNested("color.common"));
                return $this->translateColorNameToTextFormat($color) == false ? TextFormat::YELLOW : $this->translateColorNameToTextFormat($color);
            case CustomEnchants::RARITY_UNCOMMON:
                $color = strtoupper($this->getConfig()->getNested("color.uncommon"));
                return $this->translateColorNameToTextFormat($color) == false ? TextFormat::BLUE : $this->translateColorNameToTextFormat($color);
            case CustomEnchants::RARITY_RARE:
                $color = strtoupper($this->getConfig()->getNested("color.rare"));
                return $this->translateColorNameToTextFormat($color) == false ? TextFormat::GOLD : $this->translateColorNameToTextFormat($color);
            case CustomEnchants::RARITY_MYTHIC:
                $color = strtoupper($this->getConfig()->getNested("color.mythic"));
                return $this->translateColorNameToTextFormat($color) == false ? TextFormat::LIGHT_PURPLE : $this->translateColorNameToTextFormat($color);
            default:
                return TextFormat::GRAY;
        }
    }

    public function translateColorNameToTextFormat($color)
    {
        foreach (self::COLOR_CONVERSION_TABLE as $name => $textformat) {
            if ($color == $name) {
                return $textformat;
            }
        }
        return false;
    }

    public function canBeEnchanted(Item $item, $enchant, $level)
    {
        if ($enchant instanceof EnchantmentInstance) {
            $enchant = $enchant->getType();
        } elseif ($enchant instanceof CustomEnchants !== true) {
            $this->getLogger()->error("Enchant '$enchant' phải là một cá thể EnchantmentInstance hoặc CustomEnchants.");
            return false;
        }
        $type = $this->getEnchantType($enchant);
        if ($this->getEnchantMaxLevel($enchant) < $level) {
            return self::MAX_LEVEL;
        }
        foreach ($this->incompatibilities as $enchantment => $incompatibilities) {
            if ($item->getEnchantment($enchantment) !== null) {
                if (in_array($enchant->getId(), $incompatibilities)) {
                    return self::NOT_COMPATIBLE_WITH_OTHER_ENCHANT;
                }
            } else {
                foreach ($incompatibilities as $incompatibility) {
                    if ($item->getEnchantment($incompatibility) !== null) {
                        if ($enchantment == $enchant->getId() || in_array($enchant->getId(), $incompatibilities)) {
                            return self::NOT_COMPATIBLE_WITH_OTHER_ENCHANT;
                        }
                    }
                }
            }
        }
        if ($item->getCount() > 1) {
            return self::MORE_THAN_ONE;
        }
        if ($item->getId() == Item::BOOK) {
            return true;
        }
        switch ($type) {
            case "Global":
                return true;
            case "Damageable":
                if ($item instanceof Durable) {
                    return true;
                }
                break;
            case "Weapons":
                if ($item instanceof Sword || $item instanceof Axe || $item->getId() == Item::BOW) {
                    return true;
                }
                break;
            case "Bow":
                if ($item->getId() == Item::BOW) {
                    return true;
                }
                break;
            case "Tools":
                if ($item instanceof Pickaxe || $item instanceof Axe || $item instanceof Shovel || $item instanceof Hoe || $item instanceof Shears) {
                    return true;
                }
                break;
            case "Pickaxe":
                if ($item instanceof Pickaxe) {
                    return true;
                }
                break;
            case "Axe":
                if ($item instanceof Axe) {
                    return true;
                }
                break;
            case "Shovel":
                if ($item instanceof Shovel) {
                    return true;
                }
                break;
            case "Hoe":
                if ($item instanceof Hoe) {
                    return true;
                }
                break;
            case "Armor":
                if ($item instanceof Armor) {
                    return true;
                }
                break;
            case "Helmets":
                switch ($item->getId()) {
                    case Item::LEATHER_CAP:
                    case Item::CHAIN_HELMET:
                    case Item::IRON_HELMET:
                    case Item::GOLD_HELMET:
                    case Item::DIAMOND_HELMET:
                        return true;
                }
                break;
            case "Chestplate":
                switch ($item->getId()) {
                    case Item::LEATHER_TUNIC:
                    case Item::CHAIN_CHESTPLATE;
                    case Item::IRON_CHESTPLATE:
                    case Item::GOLD_CHESTPLATE:
                    case Item::DIAMOND_CHESTPLATE:
                    case Item::ELYTRA:
                        return true;
                }
                break;
            case "Leggings":
                switch ($item->getId()) {
                    case Item::LEATHER_PANTS:
                    case Item::CHAIN_LEGGINGS:
                    case Item::IRON_LEGGINGS:
                    case Item::GOLD_LEGGINGS:
                    case Item::DIAMOND_LEGGINGS:
                        return true;
                }
                break;
            case "Boots":
                switch ($item->getId()) {
                    case Item::LEATHER_BOOTS:
                    case Item::CHAIN_BOOTS:
                    case Item::IRON_BOOTS:
                    case Item::GOLD_BOOTS:
                    case Item::DIAMOND_BOOTS:
                        return true;
                }
                break;
            case "Compass":
                if ($item->getId() == Item::COMPASS) {
                    return true;
                }
                break;
        }
        return self::NOT_COMPATIBLE;
    }

    public function checkBlocks(Position $pos, $ids, $deep = 0)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        if ($deep == 0) {
            $block = $pos->getLevel()->getBlock($pos);
            if (!in_array($block->getId(), $ids)) {
                return false;
            }
        } else {
            for ($i = 0; $deep < 0 ? $i >= $deep : $i <= $deep; $deep < 0 ? $i-- : $i++) {
                $block = $pos->getLevel()->getBlock($pos->subtract(0, $i));
                if (!in_array($block->getId(), $ids)) {
                    return false;
                }
            }
        }
        return true;
    }

    public function findNearestEntity(Position $position, int $range = 50, string $type = Player::class, Player $player = null)
    {
        assert(is_a($type, Entity::class, true));
        $nearestEntity = null;
        $nearestEntityDistance = $range;
        foreach ($position->getLevel()->getEntities() as $entity) {
            $distance = $position->distance($entity);
            if ($distance <= $range && $distance < $nearestEntityDistance && $entity instanceof $type && $player !== $entity && $entity->isAlive() && $entity->isClosed() !== true && $entity->isFlaggedForDespawn() !== true) {
                $nearestEntity = $entity;
                $nearestEntityDistance = $distance;
            }
        }
        return $nearestEntity;
    }
}
