<?php
namespace GuildManager\System;

use GuildManager\GuildManager;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use pocketmine\Server;

class GuildChangeExp {
    public function __construct(GuildManager $plugin) {
        $this->plugin = $plugin;
    }

    //// TODO: 필요시 정리...

    public function GuildChangeExp($player) {
        $api = Server::getInstance()->getPluginManager()->getPlugin("UiLibrary");
        $form = $api->CustomForm(function (Player $player, array $data) {
            $name = $player->getName();
            if (!isset($data[1])) {
                $player->sendMessage("{$this->plugin->pre} 전환할 포인트를 기입하여주세요.");
                return;
            }
            if (!is_numeric($data[1])) {
                $player->sendMessage("{$this->plugin->pre} 전환할 포인트는 숫자여야합니다.");
                return;
            }
            if ($data[1] < 10) {
                $player->sendMessage("{$this->plugin->pre} 전환할 포인트는 10 포인트 이상이여야합니다.");
                return;
            }
            if ($this->plugin->getGuildPoint($this->plugin->getGuild($name)) < $data[1]) {
                $player->sendMessage("{$this->plugin->pre} 전환할 포인트가 부족합니다.");
                return;
            }
            $this->point[$name] = $data[1];
            $this->point1[$name] = floor($data[1] / 10);
            $this->point2[$name] = (($data[1] / 10) - floor($data[1] / 10)) * 10;
            $api = Server::getInstance()->getPluginManager()->getPlugin("UiLibrary");
            $form = $api->ModalForm(function (Player $player, array $data) {
                $name = $player->getName();
                if ($data[0] == true) {
                    $a = $this->point[$name] - $this->point2[$name];
                    $player->sendMessage("{$this->plugin->pre} {$a}포인트를 전환하여 길드경험치가 {$this->point1[$name]} 만큼 지급되었습니다!");
                    $this->plugin->addGuildPoint($this->plugin->getGuild($name), $this->point2[$name]);
                    $this->plugin->addGuildExp($this->plugin->getGuild($name), $this->point1[$name]);
                    $this->plugin->reduceGuildPoint($this->plugin->getGuild($name), $a);
                    unset($this->point[$name]);
                    unset($this->point1[$name]);
                    unset($this->point2[$name]);
                    unset($a);
                    return;
                }
                unset($this->point[$name]);
                unset($this->point1[$name]);
                unset($this->point2[$name]);
                return;
            });
            $form->setTitle("Tele Guild");
            $form->setContent("\n정말로 {$this->point[$name]} 포인트를 길드경험치로 전환하시겠습니까?\n{$this->point2[$name]}포인트가 반환되고, 길드경험치 {$this->point1[$name]} 만큼 지급됩니다.");
            $form->setButton1("§l§8[예]");
            $form->setButton2("§l§8[아니오]");
            $form->sendToPlayer($player);
        });
        $form->setTitle("Tele Guild");
        $form->addLabel("길드포인트를 길드경험치로 전환합니다.");
        $form->addInput("길드포인트", "길드포인트");
        $form->sendToPlayer($player);
    }
}
