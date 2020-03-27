<?php

declare(strict_types=1);

namespace goldentouch74\BookSystem;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\item\Item;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as C;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\command\{
    Command, CommandSender
};
use pocketmine\Server;

use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;

use DaPigGuy\PiggyCustomEnchants\CustomEnchants\CustomEnchant;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use DaPigGuy\PiggyCustomEnchants\CustomEnchantManager;
use pocketmine\item\enchantment\EnchantmentInstance;

class Main extends PluginBase implements Listener {

    /* @var Config $config */
    public $config;
    public $overs = 0;

    public function onLoad(){
        @mkdir($this->getDataFolder());
    }

    public function onEnable(): void{
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML, ["Common" => 100, "Uncommon" => 200, "Rare" => 300, "Mythic" => 400]);
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args): bool{
        if ($sender instanceof Player) {
            $ce = $this->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants");
            if ($ce instanceof PiggyCustomEnchants) {
                $form = new SimpleForm(function ($sender, $data){
                    if(!is_null($data)) $this->confirm($sender, $data);
                });
                $form->setTitle("§aCEShop");
                $form->addButton($this->getNameByData(0));
                $form->addButton($this->getNameByData(1));
                $form->addButton($this->getNameByData(2));
                $form->addButton($this->getNameByData(3));
                $sender->sendForm($form);
                return true;
            }
        }
        return false;
    }

    public function getNameByData(int $data, $id = true): string{
        if($id){
            switch($data){
                case 0:
                    return "Common";
                case 1:
                    return "Uncommon";
                case 2:
                    return "Rare";
                case 3:
                    return "Mythic";
            }
        }else{
            switch($data){
                case 0:
                    return "10";
                case 1:
                    return "5";
                case 2:
                    return "2";
                case 3:
                    return "1";
            }
        }
    }

    /**
     * @param int $data
     * @return bool|mixed
     */
    public function getCost(int $data){
        switch($data){
            case 0:
                return $this->config->get("Common");
            case 1:
                return $this->config->get("Uncommon");
            case 2:
                return $this->config->get("Rare");
            case 3:
                return $this->config->get("Mythic");
        }
        return true;
    }

    public function confirm(Player $player, int $dataid): void{
        $ce = $this->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants");
        if ($ce instanceof PiggyCustomEnchants) {
            $form = new CustomForm(function (Player $player, $data) use ($dataid, $ce) {
                if ($data !== null) {
                  //  if ($ce instanceof PiggyCustomEnchants) {
                        if ($player->getCurrentTotalXp() < $this->getCost($dataid)) {
                            $player->sendMessage(C::RED . "You don't have enough Exp!");
                            return;
                        }
                        $item = Item::get(340);
                        $nbt = $item->getNamedTag();
                        $nbt->setString("ceid", (string)$dataid);
                        $item->setCustomName($this->getNameByData($dataid) . C::RESET . C::YELLOW . " Book");
                        $item->setLore([C::GRAY . "Tap ground to get random enchantment"]);
                        $player->getInventory()->addItem($item);
                        $player->addXp(-$this->getCost($dataid));
                    }
                
            });
            $form->setTitle((int)$this->getNameByData($dataid, false) . $this->getNameByData($dataid));
            $form->addLabel("Cost: " . $this->getCost($dataid) . " Exp");
            $player->sendForm($form);
        }
    }
    public function setOvers(int $overs){
        $this->overs = $overs;
    }
    public function getOvers(): int{
        return $this->overs;
    }
    public function onInteract(PlayerInteractEvent $e): void{
        $player = $e->getPlayer();
        $item = $e->getItem();
        $ce = $this->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants");
        if ($ce instanceof PiggyCustomEnchants) {
            if($item->getId() == 340){
                if($item->getNamedTag()->hasTag("ceid", StringTag::class)) {
                    $e->setCancelled();

                    $id = $item->getNamedTag()->getString("ceid");
                    $this->getLogger()->info("Plugin passing cid section.");
                    $this->setOvers(0);

                 //foreach(CustomEnchantManager::getEnchantments() as $eid => $data) {
                //     if(CustomEnchantManager::getEnchantments() instanceof CustomEnchant){
               // $manager = Server::getInstance()->getPluginManager()->getPlugin("PiggyCustomEnchants");
              //  if(!$manager instanceof CustomEnchantManager){
                   // return;
          //      $manager = CustomEnchantManager;
                foreach(CustomEnchantManager::$enchants as $enchantmanager => $enchantment){
                      if ($enchantment->getName() !== $this->getNameByData((int)$id)){
                    
                            switch ($id) {
                                case 0: //Common
                                    $enchs = [128, 142, 136, 131, 143, 122, 125, 139, 121, 146];
                                    break;
                                case 1: //Uncommon
                                    $enchs = [130, 105, 112, 110, 147, 118, 148, 103, 126, 124, 115, 138, 120, 137, 116];
                                    break;
                                case 2: //Rare
                                    $enchs = [101, 114, 100, 134, 107, 119, 123, 149];
                                    break;
                                case 3: //Mythic
                                    $enchs = [127, 102, 104, 144, 129, 140, 113, 133, 106, 108, 109, 135, 150, 111];
                                    break;
                            }
                            $enchanted = false;

                            if ($enchanted == false && $this->getOvers() < 1) {
                                $enchanted = true;
                                $this->setOvers($this->getOvers() + 1);
                                $info["ench"] = $enchs[array_rand($enchs)];
                                   $enchant = is_numeric($info["ench"]) ? CustomEnchantManager::getEnchantment((int)$info["ench"]) : CustomEnchantManager::getEnchantmentByName($info["ench"]);
                    if ($enchant == null) {
                        $player->sendMessage(TextFormat::RED . "Invalid enchantment.");
                        return;
                    }
                              //  $ench = CustomEnchantManager::getEnchantment($info["ench"]);
                              //  if (!$ench instanceof CustomEnchant){
                                  //  $player->sendMessage(TextFormat::colorize("&cEnchant not found."));
                              //  }else{
                                    
                                
                                //$enchName = CustomEnchantManager::getEnchantmentByName($info["ench"]);
                                $info["lvl"] = mt_rand(1, $enchant->getMaxLevel());
                                $book = Item::get(Item::ENCHANTED_BOOK);
                                $hand = $player->getInventory()->getItemInHand();
                                $player->getInventory()->setItemInHand($hand->setCount($hand->getCount() - 1));
                             //   $hand = $player->getInventory()->getItemInHand();
                                 $book->addEnchantment(new EnchantmentInstance($enchant, $info["lvl"]));
                           $player->getInventory()->addItem($book);
                           $player->sendMessage(TextFormat::colorize("&aEnchant success."));
                            }
                        }
                    }
            }
    
        }
    }
}
}
