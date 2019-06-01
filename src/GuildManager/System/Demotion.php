<?php
namespace GuildManager\System;

use GuildManager\GuildManager;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use pocketmine\Server;

class Demotion {
    public function __construct(GuildManager $plugin) {
        $this->plugin = $plugin;
    }

    public function Demotion($player) {
        $GuildName = $this->plugin->getGuild($player->getName());
        $api = Server::getInstance()->getPluginManager()->getPlugin("UiLibrary");
        $form = $api->SimpleForm(function (Player $player, array $data) {
            $name = $player->getName();
            if (!isset($this->list[$name][$data[0]])) return;
            $this->target[$player->getName()] = $this->list[$name][$data[0]];
            unset($this->list[$name][$data[0]]);

            $api = Server::getInstance()->getPluginManager()->getPlugin("UiLibrary");
            $form = $api->ModalForm(function (Player $player, array $data) {
                $name = $player->getName();
                $target = $this->target[$player->getName()];
                unset($this->target[$player->getName()]);
                if ($data[0] == true) {
                    $player->sendMessage("{$this->plugin->pre} {$target}님을 강등시켰습니다.");
                    foreach ($this->plugin->gdata[$this->plugin->getGuild($name)]["전체길드원"] as $members) {
                        if (($member = Server::getInstance()->getPlayer($members)) instanceof Player) {
                            $member->sendMessage("{$this->plugin->pre} {$target}님이 부길드장에서 길드원으로 강등되셨습니다.");
                        }
                    }
                    $this->plugin->Demotion($this->plugin->getGuild($player->getName()), $target);
                    return;
                } else {
                    return;
                }
            });
            $form->setTitle("Tele Guild");
            $form->setContent("\n§l{$this->target[$player->getName()]}님을 길드원으로 강등시키겠습니까?");
            $form->setButton1("§l§8[예]");
            $form->setButton2("§l§8[아니오]");
            $form->sendToPlayer($player);
        });
        $form->setTitle("Tele Guild");
        $form->setContent("");
        $count = 0;
        if (count($this->plugin->getGuildLeader2($GuildName)) > 0) {
            foreach ($this->plugin->getGuildLeader2($GuildName) as $list => $list1) {
                $form->addButton((string) $list);
                $this->list[$player->getName()][$count] = $list1;
                $count++;
            }
        }
        $form->addButton("§l닫기");
        $form->sendToPlayer($player);
    }
}
