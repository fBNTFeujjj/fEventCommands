<?php

namespace BNTFeujjj\event;

use BNTFeujjj\event\command\EventCommand;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\world\Position;

class Main extends PluginBase implements Listener
{
    use SingletonTrait;

    public function onLoad(): void
    {
        self::setInstance($this);
    }

    protected function onEnable(): void
    {
        $this->saveDefaultConfig();
        $config = $this->getConfig();

        $this->getLogger()->info("EventCommand by FutoShop & BNT Feujjj bien activé");

        foreach ($config->get("buttons") as $buttonName => $buttonData) {
            if ($buttonData["enabled"]) {
                $this->getServer()->getCommandMap()->register(
                    "",
                    new EventCommand($this, $buttonName, $buttonData)
                );
            }
        }

        if ($config->get("event-command", false)) {
            $this->getServer()->getCommandMap()->register(
                "",
                new EventCommand($this, "event", ["description" => $config->get("event-command-description")])
            );
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        $config = $this->getConfig();
        $commandName = $command->getName();

        if (isset($config->get("buttons")[$commandName])) {
            $buttonData = $config->get("buttons")[$commandName];
            $x = $buttonData["x"];
            $y = $buttonData["y"];
            $z = $buttonData["z"];
            $worldName = $buttonData["world"];
            $world = $this->getServer()->getWorldManager()->getWorldByName($worldName);

            if ($world === null) {
                $this->getServer()->getWorldManager()->loadWorld($worldName);
                $world = $this->getServer()->getWorldManager()->getWorldByName($worldName);
            }

            if ($world !== null) {
                $targetPosition = new Position($x, $y, $z, $world);
                $sender->teleport($targetPosition);
                $sender->sendMessage($buttonData["message"]);
            } else {
                $sender->sendMessage("Le monde $worldName n'a pas pu être chargé.");
            }

            return true;
        }

        return false;
    }
}
