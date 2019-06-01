<?php
namespace GuildManager\System;

use GuildManager\GuildManager;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use pocketmine\Server;

class CreateGuild {
    public function __construct(GuildManager $plugin) {
        $this->plugin = $plugin;
    }

    public function CreateGuild($player) {
        $api = Server::getInstance()->getPluginManager()->getPlugin("UiLibrary");
        $form = $api->CustomForm(function (Player $player, array $data) {
            $name = $player->getName();
            if (!isset($data[1])) {
                $player->sendMessage("{$this->plugin->pre} 길드명을 기입해주세요.");
                return;
            }
            if ($this->plugin->isGuild($player->getName())) {
                $player->sendMessage("{$this->plugin->pre} 이미 다른 길드에 소속되어있습니다.");
                return;
            }
            if (isset($this->plugin->gdata[$data[1]])) {
                $player->sendMessage("{$this->plugin->pre} 해당 이름의 길드가 이미 존재합니다.");
                return;
            }
            if ($this->plugin->money->getMoney($name) < 200000) {
                $needmoney = 200000 - ($this->plugin->money->getMoney($name));
                $player->sendMessage("{$this->plugin->pre} 길드를 생성할 비용이 부족합니다! 필요한 테나 : {$needmoney}");
                return;
            }
            if ($data[2] !== 1) $this->guildopen[$name] = false;
            if ($data[2] == 1) $this->guildopen[$name] = true;
            $this->guildname[$name] = $data[1];
            $api = Server::getInstance()->getPluginManager()->getPlugin("UiLibrary");
            $form = $api->ModalForm(function (Player $player, array $data) {
                $name = $player->getName();
                $GuildName = $this->guildname[$name];
                $Open = $this->guildopen[$name];
                unset($this->guildname[$name]);
                unset($this->guildopen[$name]);
                if ($data[0] == true) {
                    $player->sendMessage("{$this->plugin->pre} {$GuildName} 길드를 생성하였습니다!");
                    $this->plugin->addGuild($GuildName, $name, $Open);
                    $this->plugin->packet->setTag($player);
                    return;
                }
                return;
            });
            $form->setTitle("Tele Guild");
            $form->setContent("\n정말로 20만테나로 {$this->guildname[$name]} 길드를 생성하시겠습니까?");
            $form->setButton1("§l§8[예]");
            $form->setButton2("§l§8[아니오]");
            $form->sendToPlayer($player);
        });
        $form->setTitle("Tele Guild");
        $form->addLabel("20만테나로 길드를 생성합니다.");
        $form->addInput("길드명", "길드명");
        $form->addToggle("공개유무");
        $form->sendToPlayer($player);
    }
}
