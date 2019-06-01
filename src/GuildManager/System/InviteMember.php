<?php
namespace GuildManager\System;

use GuildManager\GuildManager;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use pocketmine\Server;

class InviteMember {
    public function __construct(GuildManager $plugin) {
        $this->plugin = $plugin;
    }

    public function InviteMember($player) {
        $api = Server::getInstance()->getPluginManager()->getPlugin("UiLibrary");
        $form = $api->CustomForm(function (Player $player, array $data) {
            $name = $player->getName();
            if (!isset($data[1])) {
                $player->sendMessage("{$this->plugin->pre} 유저명을 기입해주세요.");
                return;
            }
            if ($this->plugin->getCountMember($name) >= $this->plugin->getMaxMember($this->plugin->getGuild($name))) {
                $player->sendMessage("{$this->plugin->pre} 길드 정원이 꽉 찼습니다.");
                return;
            }
            if (!Server::getInstance()->getPlayer($data[1]) instanceof Player) {
                $player->sendMessage("{$this->plugin->pre} 해당 유저는 접속중이 아닙니다.");
                return;
            }
            if (isset($this->plugin->mdata[Server::getInstance()->getPlayer($data[1])->getName()])) {
                $player->sendMessage("{$this->plugin->pre} 이미 길드에 소속되어있습니다.");
                return;
            }
            if ($this->plugin->getAccessInvite(Server::getInstance()->getPlayer($data[1])->getName()) == "거부") {
                $player->sendMessage("{$this->plugin->pre} 해당 유저는 길드 초대수신을 차단한 상태입니다.");
                return;
            }
            $this->invite[$name] = Server::getInstance()->getPlayer($data[1])->getName();
            $api = Server::getInstance()->getPluginManager()->getPlugin("UiLibrary");
            $form = $api->ModalForm(function (Player $player, array $data) {
                $name = $player->getName();
                if ($data[0] == true) {
                    if ($this->plugin->InviteGuild($this->invite[$name], $this->plugin->getGuild($name)) === false)
                        $player->sendMessage("{$this->plugin->pre} 해당 유저가 다른 창을 열고있어 보낼 수 없습니다. 나중에 다시 시도하세요.");
                    else
                        $player->sendMessage("{$this->plugin->pre} {$this->invite[$name]}님에게 초대수신을 보냈습니다.");
                    unset($this->invite[$name]);
                    return;
                }
                unset($this->invite[$name]);
                return;
            });
            $form->setTitle("Tele Guild");
            $form->setContent("\n{$this->invite[$name]}님을 길드에 초대하시겠습니까?");
            $form->setButton1("§l§8[예]");
            $form->setButton2("§l§8[아니오]");
            $form->sendToPlayer($player);
        });
        $form->setTitle("Tele Guild");
        $form->addLabel("길드에 유저를 초대합니다");
        $form->addInput("유저명", "유저명");
        $form->sendToPlayer($player);
    }
}
