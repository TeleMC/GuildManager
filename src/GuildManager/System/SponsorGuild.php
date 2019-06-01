<?php
namespace GuildManager\System;

use GuildManager\GuildManager;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use pocketmine\Server;

class SponsorGuild {
    public function __construct(GuildManager $plugin) {
        $this->plugin = $plugin;
    }

    public function SponsorGuild($player) {
        $api = Server::getInstance()->getPluginManager()->getPlugin("UiLibrary");
        $form = $api->CustomForm(function (Player $player, array $data) {
            $name = $player->getName();
            if (!isset($data[1])) {
                $player->sendMessage("{$this->plugin->pre} 후원할 테나를 기입하여주세요.");
                return;
            }
            if (!is_numeric($data[1])) {
                $player->sendMessage("{$this->plugin->pre} 후원할 테나는 숫자여야합니다.");
                return;
            }
            if ($data[1] < 10) {
                $player->sendMessage("{$this->plugin->pre} 후원할 테나는 10테나 이상이여야합니다.");
                return;
            }
            if ($this->plugin->money->getMoney($name) < $data[1]) {
                $player->sendMessage("{$this->plugin->pre} 후원할 테나가 부족합니다.");
                return;
            }
            $this->sponsor[$name] = $data[1];
            $this->sponsor1[$name] = floor($data[1] / 10);
            $this->sponsor2[$name] = (($data[1] / 10) - floor($data[1] / 10)) * 10;
            $api = Server::getInstance()->getPluginManager()->getPlugin("UiLibrary");
            $form = $api->ModalForm(function (Player $player, array $data) {
                $name = $player->getName();
                if ($data[0] == true) {
                    $a = $this->sponsor[$name] - $this->sponsor2[$name];
                    $player->sendMessage("{$this->plugin->pre} {$a}테나를 길드에 후원하여 {$this->sponsor1[$name]} 포인트가 지급되었습니다!");
                    $this->plugin->money->addMoney($name, $this->sponsor2[$name]);
                    $this->plugin->money->reduceMoney($name, $a);
                    $this->plugin->addGuildPoint($this->plugin->getGuild($name), $this->sponsor1[$name]);
                    unset($this->sponsor[$name]);
                    unset($this->sponsor1[$name]);
                    unset($this->sponsor2[$name]);
                    unset($a);
                    return;
                }
                unset($this->sponsor[$name]);
                unset($this->sponsor1[$name]);
                unset($this->sponsor2[$name]);
                return;
            });
            $form->setTitle("Tele Guild");
            $form->setContent("\n정말로 {$this->sponsor[$name]}테나를 길드에 후원하시겠습니까?\n{$this->sponsor2[$name]}테나가 반환되고, {$this->sponsor1[$name]} 포인트로 변환됩니다.");
            $form->setButton1("§l§8[예]");
            $form->setButton2("§l§8[아니오]");
            $form->sendToPlayer($player);
        });
        $form->setTitle("Tele Guild");
        $form->addLabel("자신의 테나를 길드에 후원합니다.");
        $form->addInput("액수", "액수");
        $form->sendToPlayer($player);
    }
}
