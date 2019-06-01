<?php
namespace GuildManager\System;

use GuildManager\GuildManager;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use pocketmine\Server;

class GuildList {
    public function __construct(GuildManager $plugin) {
        $this->plugin = $plugin;
    }

    public function GuildList($player) {
        $api = Server::getInstance()->getPluginManager()->getPlugin("UiLibrary");
        $form = $api->SimpleForm(function (Player $player, array $data) {
        });
        $form->setTitle("Tele Guild");
        $a = "\n";
        foreach ($this->plugin->gdata as $guild => $gname) {
            $a .= "{$this->plugin->pre} §r{$this->plugin->gdata[$guild]["길드"]}\n\n";
        }
        $form->setContent("{$a}");
        $form->addButton("§l닫기");
        $form->sendToPlayer($player);
    }
}
