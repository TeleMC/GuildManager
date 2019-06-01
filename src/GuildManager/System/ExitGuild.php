<?php
namespace GuildManager\System;

use GuildManager\GuildManager;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use pocketmine\Server;

class ExitGuild {
    public function __construct(GuildManager $plugin) {
        $this->plugin = $plugin;
    }

    public function ExitGuild($player) {
        $api = Server::getInstance()->getPluginManager()->getPlugin("UiLibrary");
        $form = $api->ModalForm(function (Player $player, array $data) {
            $GuildName = $this->plugin->getGuild($player->getName());
            if ($data[0] == true) {
                if ($this->plugin->mdata[$player->getName()]["직위"] == "길드장") {
                    $player->sendMessage("{$this->plugin->pre} 길드장은 탈퇴할 수 없습니다!");
                    $player->sendMessage("{$this->plugin->pre} 탈퇴하시려면 길드를 해체하여야합니다.");
                    return;
                }
                $this->plugin->ExitGuild($player->getName());
                foreach ($this->plugin->gdata[$GuildName]["전체길드원"] as $members) {
                    if (($member = Server::getInstance()->getPlayer($members)) instanceof Player) {
                        $member->sendMessage("{$this->plugin->pre} {$player->getName()}님이 길드를 탈퇴하였습니다.");
                    }
                }
                $player->sendMessage("{$this->plugin->pre} {$GuildName} 길드를 탈퇴하였습니다.");
                $this->plugin->packet->setTag($player);
                return;
            }
        });
        $form->setTitle("Tele Party");
        $form->setContent("\n정말로 {$this->plugin->getGuild($player->getName())} 길드를 탈퇴하시겠습니까?");
        $form->setButton1("§l§8[예]");
        $form->setButton2("§l§8[아니오]");
        $form->sendToPlayer($player);
    }
}
