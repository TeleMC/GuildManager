<?php
namespace GuildManager;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\Server;

class EventListener implements Listener {
    private $plugin;

    public function __construct(GuildManager $plugin) {
        $this->plugin = $plugin;
    }

    public function onJoin(PlayerJoinEvent $ev) {
        //$name = $this->plugin->nickname->getNickName($player->getName());
        $server = Server::getInstance();
        $player = $ev->getPlayer();
        $name = $player->getName();
        if (!isset($this->plugin->adata[$name])) {
            $this->plugin->adata[$name] = "허용";
        }
    }
}
