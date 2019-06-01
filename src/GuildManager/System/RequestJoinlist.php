<?php
namespace GuildManager\System;

use GuildManager\GuildManager;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use pocketmine\Server;

class RequestJoinlist {
    public function __construct(GuildManager $plugin) {
        $this->plugin = $plugin;
    }

    public function RequestJoinlist($player) {
        $api = Server::getInstance()->getPluginManager()->getPlugin("UiLibrary");
        $form = $api->SimpleForm(function (Player $player, array $data) {
            if (!is_numeric($data[0])) return;
            $name = $player->getName();
            $n = -1;
            foreach ($this->plugin->gdata[$this->plugin->getGuild($name)]["가입요청"] as $list1 => $list2) {
                $n++;
                $this->list[$name][$n] = "{$this->plugin->gdata[$this->plugin->getGuild($name)]["가입요청"][$list1]}";
            }
            unset($n);
            if (!isset($this->list[$name][$data[0]])) return;
            $this->Lname[$name] = $this->list[$name][$data[0]];
            $api = Server::getInstance()->getPluginManager()->getPlugin("UiLibrary");
            $form = $api->ModalForm(function (Player $player, array $data) {
                $name = $player->getName();
                if ($data[0] == true) {
                    if (isset($this->plugin->mdata[$this->Lname[$name]])) {
                        $player->sendMessage("{$this->plugin->pre} 해당 유저는 다른 길드에 소속되어있습니다.");
                        unset($this->Lname[$name]);
                        unset($this->list[$name]);
                        return;
                    }
                    if ($this->plugin->getCountMember($name) >= $this->plugin->getMaxMember($this->plugin->getGuild($name))) {
                        $player->sendMessage("{$this->plugin->pre} 길드 정원이 꽉 찼습니다.");
                        unset($this->Lname[$name]);
                        unset($this->list[$name]);
                        return;
                    }
                    $player->sendMessage("{$this->plugin->pre} {$this->Lname[$name]}님의 가입요청을 수락하셨습니다!");
                    if (Server::getInstance()->getPlayer($this->Lname[$name]) instanceof Player) {
                        Server::getInstance()->getPlayer($this->Lname[$name])->sendMessage("{$this->plugin->pre} {$this->plugin->getGuild($name)} 길드의 가입요청이 수락되었습니다!");
                    }
                    $this->plugin->JoinGuild($this->plugin->getGuild($name), $this->Lname[$name]);
                    $this->plugin->packet->setTag(Server::getInstance()->getPlayer($this->Lname[$name]));
                    unset($this->plugin->gdata[$this->plugin->getGuild($name)]["가입요청"][$this->Lname[$name]]);
                    unset($this->Lname[$name]);
                    unset($this->list[$name]);
                    return;
                } else {
                    $player->sendMessage("{$this->plugin->pre} {$this->Lname[$name]}님의 가입요청을 거절하셨습니다");
                    if (Server::getInstance()->getPlayer($this->Lname[$name]) instanceof Player) {
                        Server::getInstance()->getPlayer($this->Lname[$name])->sendMessage("{$this->plugin->pre} {$this->plugin->getGuild($name)} 길드의 가입요청이 거절되었습니다");
                    }
                    unset($this->plugin->gdata[$this->plugin->getGuild($name)]["가입요청"][$this->Lname[$name]]);
                    unset($this->Lname[$name]);
                    unset($this->list[$name]);
                    return;
                }
            });
            $form->setTitle("Tele Guild");
            $form->setContent("\n§l{$this->Lname[$name]}님의 가입요청을 수락하시겠습니까?");
            $form->setButton1("§l§8[예]");
            $form->setButton2("§l§8[아니오]");
            $form->sendToPlayer($player);
        });
        $form->setTitle("Tele Guild");
        $form->setContent("");
        foreach ($this->plugin->gdata[$this->plugin->getGuild($player->getName())]["가입요청"] as $list => $list1) {
            $form->addButton("{$this->plugin->gdata[$this->plugin->getGuild($player->getName())]["가입요청"][$list]}");
        }
        $form->addButton("§l닫기");
        $form->sendToPlayer($player);
    }
}
