<?php
namespace GuildManager\System;

use GuildManager\GuildManager;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use pocketmine\Server;

class RemoveGuild {
    public function __construct(GuildManager $plugin) {
        $this->plugin = $plugin;
    }

    public function RemoveGuild($player) {
        $api = Server::getInstance()->getPluginManager()->getPlugin("UiLibrary");
        $form = $api->ModalForm(function (Player $player, array $data) {
            if ($data[0] == true) {
                foreach ($this->plugin->gdata[$this->plugin->getGuild($player->getName())]["전체길드원"] as $members) {
                    $this->plugin->setChatMode($members, false);
                    if (Server::getInstance()->getPlayer($members) instanceof Player) {
                        Server::getInstance()->getPlayer($members)->sendMessage("{$this->plugin->pre} 길드가 해체되었습니다.");
                        $this->plugin->packet->setTag_2(Server::getInstance()->getPlayer($members));
                    }
                }
                $this->plugin->removeGuild($this->plugin->getGuild($player->getName()));
            }
        });
        $form->setTitle("Tele Guild");
        $form->setContent("\n정말로 {$this->plugin->getGuild($player->getName())} 길드를 해체하시겠습니까?");
        $form->setButton1("§l§8[예]");
        $form->setButton2("§l§8[아니오]");
        $form->sendToPlayer($player);
    }
}
