<?php

namespace BNTFeujjj\event\command;

use EasyUI\element\Button;
use EasyUI\variant\SimpleForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\world\Position;

class EventCommand extends Command
{
    private $plugin;
    private $buttonName;
    private $buttonData;

    public function __construct(PluginBase $plugin, string $buttonName, array $buttonData = [])
    {
        $this->plugin = $plugin;
        $this->buttonName = $buttonName;
        $this->buttonData = $buttonData;

        $commandName = $buttonData["command"] ?? ltrim($buttonName, "/");
        $description = $buttonData["description"] ?? "§cDescription manquante";

        parent::__construct(
            $commandName,
            $description,
            null,
            [$buttonName]
        );

        $this->setPermission(DefaultPermissions::ROOT_USER);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): void
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage("§cCette commande ne peut être utilisée qu'en jeu.");
            return;
        }

        $config = $this->plugin->getConfig();

        if ($this->buttonName === "event") {
            $form = new SimpleForm($config->get("title"));

            foreach ($config->get("buttons") as $buttonName => $buttonData) {
                if ($buttonData["enabled"]) {
                    $label = $buttonData["label"] ?? $buttonName;
                    $command = $buttonData["command"] ?? "";
                    $message = $buttonData["message"] ?? "§aVous avez été téléporté !";

                    $form->setHeaderText($config->get("content"));
                    $form->addButton(new Button($label, null, function (Player $player) use ($command, $message) {
                        $player->chat($command);
                    }));
                }
            }

            $sender->sendForm($form);
        } else {
            if (!isset($this->buttonData["x"], $this->buttonData["y"], $this->buttonData["z"], $this->buttonData["world"])) {
                $sender->sendMessage("§cDonnées du bouton manquantes.");
                return;
            }

            $x = $this->buttonData["x"];
            $y = $this->buttonData["y"];
            $z = $this->buttonData["z"];
            $worldName = $this->buttonData["world"];
            $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($worldName);

            if ($world === null) {
                $this->plugin->getServer()->getWorldManager()->loadWorld($worldName);
                $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($worldName);
            }

            if ($world !== null) {
                $targetPosition = new Position($x, $y, $z, $world);
                $sender->teleport($targetPosition);
                $message = $this->buttonData["message"] ?? "§aVous avez été téléporté !";
                $sender->sendMessage($message);
            } else {
                $sender->sendMessage("§cLe monde $worldName n'a pas pu être chargé.");
            }
        }
    }
}
