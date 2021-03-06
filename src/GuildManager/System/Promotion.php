<?php
namespace GuildManager\System;

use GuildManager\GuildManager;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use pocketmine\Server;

class Promotion {
    public function __construct(GuildManager $plugin) {
        $this->plugin = $plugin;
    }

    public function Promotion($player) {
        $api = Server::getInstance()->getPluginManager()->getPlugin("UiLibrary");
        $form = $api->SimpleForm(function (Player $player, array $data) {
            $name = $player->getName();
            if (!is_numeric($data[0])) return;
            $n = -1;
            foreach ($this->plugin->gdata[$this->plugin->getGuild($player->getName())]["길드원"] as $list => $list1) {
                $n++;
                $this->name[$name][$n] = "{$this->plugin->gdata[$this->plugin->getGuild($player->getName())]["길드원"][$list]}";
            }
            unset($n);
            if (!isset($this->name[$name][$data[0]])) return;
            $this->Lname[$player->getName()] = $this->name[$name][$data[0]];
            $api = Server::getInstance()->getPluginManager()->getPlugin("UiLibrary");
            $form = $api->ModalForm(function (Player $player, array $data) {
                $name = $player->getName();
                if ($data[0] == true) {
                    $player->sendMessage("{$this->plugin->pre} {$this->Lname[$name]}님을 부길드장으로 임명하였습니다!");
                    foreach ($this->plugin->gdata[$this->plugin->getGuild($name)]["전체길드원"] as $members) {
                        if (Server::getInstance()->getPlayer($members) instanceof Player) {
                            Server::getInstance()->getPlayer($members)->sendMessage("{$this->plugin->pre} {$this->Lname[$name]}님이 부길드장으로 임명되셨습니다!");
                        }
                    }
                    $this->plugin->Promotion($this->plugin->getGuild($name), $this->Lname[$name]);
                    unset($this->Lname[$name]);
                    unset($this->name[$name]);
                    return;
                } else {
                    unset($this->Lname[$name]);
                    unset($this->name[$name]);
                    return;
                }
            });
            $form->setTitle("Tele Guild");
            $form->setContent("\n§l{$this->Lname[$player->getName()]}님을 부길드장으로 승급시키겠습니까?");
            $form->setButton1("§l§8[예]");
            $form->setButton2("§l§8[아니오]");
            $form->sendToPlayer($player);
        });
        $form->setTitle("Tele Guild");
        $form->setContent("");
        foreach ($this->plugin->gdata[$this->plugin->getGuild($player->getName())]["길드원"] as $list => $list1) {
            $form->addButton("{$this->plugin->gdata[$this->plugin->getGuild($player->getName())]["길드원"][$list]}");
        }
        $form->addButton("§l닫기");
        $form->sendToPlayer($player);
    }
}
