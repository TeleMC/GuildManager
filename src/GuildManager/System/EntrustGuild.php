<?php
namespace GuildManager\System;

use GuildManager\GuildManager;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use pocketmine\Server;

class EntrustGuild {
    public function __construct(GuildManager $plugin) {
        $this->plugin = $plugin;
    }

    public function EntrustGuild($player) {
        $api = Server::getInstance()->getPluginManager()->getPlugin("UiLibrary");
        $form = $api->SimpleForm(function (Player $player, array $data) {
            $name = $player->getName();
            if (!is_numeric($data[0])) return;
            if (!isset($this->list[$name][$data[0]])) return;
            $this->target[$player->getName()] = $this->list[$name][$data[0]];
            unset($this->list[$name][$data[0]]);
            $api = Server::getInstance()->getPluginManager()->getPlugin("UiLibrary");
            $form = $api->ModalForm(function (Player $player, array $data) {
                $name = $player->getName();
                $target = $this->target[$player->getName()];
                unset($this->target[$player->getName()]);
                if ($data[0] == true) {
                    if ($name == $target) {
                        $player->sendMessage("{$this->plugin->pre} 자기자신에게 위임할 수 없습니다!");
                        return;
                    }
                    if (!Server::getInstance()->getPlayer($target) instanceof Player) {
                        $player->sendMessage("{$this->plugin->pre} 해당 유저는 접속중이 아닙니다.");
                        return;
                    }
                    $player->sendMessage("{$this->plugin->pre} {$target}님께 길드를 위임하였습니다!");
                    Server::getInstance()->getPlayer($target)->sendMessage("{$this->plugin->pre} {$name}님으로부터 길드를 위임 받았습니다!");
                    $this->plugin->EntrustGuild($this->plugin->getGuild($name), $target);
                    return;
                } else {
                    return;
                }
            });
            $form->setTitle("Tele Guild");
            $form->setContent("\n§l{$this->target[$player->getName()]}님에게 길드를 위임하시겠습니까?");
            $form->setButton1("§l§8[예]");
            $form->setButton2("§l§8[아니오]");
            $form->sendToPlayer($player);
        });
        $form->setTitle("Tele Guild");
        $form->setContent("");
        $count = 0;
        foreach ($this->plugin->gdata[$this->plugin->getGuild($player->getName())]["전체길드원"] as $list => $list1) {
            $form->addButton((string) $list);
            $this->list[$player->getName()][$count] = $list;
            $count++;
        }
        $form->addButton("§l닫기");
        $form->sendToPlayer($player);
    }
}
