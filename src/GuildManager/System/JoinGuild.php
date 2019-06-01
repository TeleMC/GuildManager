<?php
namespace GuildManager\System;

use GuildManager\GuildManager;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use pocketmine\Server;

class JoinGuild {
    public function __construct(GuildManager $plugin) {
        $this->plugin = $plugin;
    }

    public function JoinGuild($player) {
        $api = Server::getInstance()->getPluginManager()->getPlugin("UiLibrary");
        $form = $api->SimpleForm(function (Player $player, array $data) {
            $name = $player->getName();
            if (!is_numeric($data[0])) return;
            $n = -1;
            foreach ($this->plugin->gdata as $guild => $gname) {
                if (($this->plugin->getCountMember($this->plugin->gdata[$guild]["길드"]) < $this->plugin->getMaxMember($this->plugin->gdata[$guild]["길드"])) and ($this->plugin->getSearchAccess($this->plugin->gdata[$guild]["길드"]) == "공개")) {
                    $n++;
                    $this->data[$name][$n] = "{$this->plugin->gdata[$guild]["길드"]}";
                }
            }
            unset($n);
            if (!isset($this->data[$name][$data[0]])) return;
            $this->guildname[$name] = $this->data[$name][$data[0]];
            $api = Server::getInstance()->getPluginManager()->getPlugin("UiLibrary");
            $form = $api->ModalForm(function (Player $player, array $data) {
                $name = $player->getName();
                if ($data[0] == true) {
                    if ($this->plugin->getCountMember($this->guildname[$name]) >= $this->plugin->getMaxMember($this->guildname[$name])) {
                        $player->sendMessage("{$this->plugin->pre} 해당 길드 정원이 꽉 찼습니다.");
                        unset($this->data[$name]);
                        unset($this->guildname[$name]);
                        return;
                    }
                    if (($this->plugin->getSearchAccess($this->guildname[$name]) == "공개")) {
                        $player->sendMessage("{$this->plugin->pre} {$this->guildname[$name]} 길드 가입요청을 보냈습니다.");
                        if (Server::getInstance()->getPlayer($this->plugin->getGuildLeader($this->guildname[$name])) instanceof Player) {
                            Server::getInstance()->getPlayer($this->plugin->getGuildLeader($this->guildname[$name]))->sendMessage("{$this->plugin->pre} 새로운 가입요청이 왔습니다!");
                        }
                        /*if(Server::getInstance()->getPlayer($this->plugin->getGuildLeader2($this->guildname[$name])) instanceof Player){
                          Server::getInstance()->getPlayer($this->plugin->getGuildLeader2($this->guildname[$name]))->sendMessage("{$this->plugin->pre} 새로운 가입요청이 왔습니다!");
                        }*/
                        $this->plugin->gdata[$this->guildname[$name]]["가입요청"][$name] = "{$name}";
                        unset($this->data[$name]);
                        unset($this->guildname[$name]);
                        return;
                    }
                    unset($this->data[$name]);
                    unset($this->guildname[$name]);
                    return;
                } else {
                    unset($this->data[$name]);
                    unset($this->guildname[$name]);
                    return;
                }
            });
            $form->setTitle("Tele Guild");
            $form->setContent("\n§l{$this->guildname[$name]} 길드에 가입하시겠습니까?");
            $form->setButton1("§l§8[예]");
            $form->setButton2("§l§8[아니오]");
            $form->sendToPlayer($player);
        });
        $form->setTitle("Tele Guild");
        $form->setContent("");
        foreach ($this->plugin->gdata as $guild => $gname) {
            if (($this->plugin->getCountMember($this->plugin->gdata[$guild]["길드"]) < $this->plugin->getMaxMember($this->plugin->gdata[$guild]["길드"])) and ($this->plugin->getSearchAccess($this->plugin->gdata[$guild]["길드"]) == "공개")) {
                $form->addButton("§l{$this->plugin->gdata[$guild]["길드"]}\n§r§8길드장 : {$this->plugin->getGuildLeader($this->plugin->gdata[$guild]["길드"])} 인원 : {$this->plugin->getCountMember($this->plugin->gdata[$guild]["길드"])}/{$this->plugin->getMaxMember($this->plugin->gdata[$guild]["길드"])} 공개유무 : {$this->plugin->getSearchAccess($this->plugin->gdata[$guild]["길드"])}");
            }
        }
        $form->addButton("§l닫기");
        $form->sendToPlayer($player);
    }
}
