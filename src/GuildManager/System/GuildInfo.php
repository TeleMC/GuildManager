<?php
namespace GuildManager\System;

use GuildManager\GuildManager;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use pocketmine\Server;

class GuildInfo {
    public function __construct(GuildManager $plugin) {
        $this->plugin = $plugin;
    }

    public function GuildInfo($player) {
        $api = Server::getInstance()->getPluginManager()->getPlugin("UiLibrary");
        $form = $api->SimpleForm(function (Player $player, array $data) {
        });
        $form->setTitle("Tele Guild");
        $text = "{$this->plugin->pre} 자신의 길드정보를 확인합니다.\n";
        if (!$this->plugin->isGuild($player->getName()))
            $text .= "소속된 길드가 없습니다.";
        else {
            $GuildName = $this->plugin->getGuild($player->getName());
            $GuildLevel = $this->plugin->getGuildLevel($GuildName);
            $GuildLeader = $this->plugin->getGuildLeader($GuildName);
            $GuildLeader2 = "";
            if (count($this->plugin->getGuildLeader2($GuildName)) <= 0)
                $GuildLeader2 = "공석";
            else {
                $max = count($this->plugin->getGuildLeader2($GuildName));
                $count = 1;
                foreach ($this->plugin->getGuildLeader2($GuildName) as $key => $value) {
                    if ($max == $count)
                        $GuildLeader2 .= $value;
                    else
                        $GuildLeader2 .= $value . ", ";
                }
            }
            $GuildMember = "";
            if (count($this->plugin->getGuildMember($GuildName)) <= 0)
                $GuildMember = "공석";
            else {
                $max = count($this->plugin->getGuildMember($GuildName));
                $count = 1;
                foreach ($this->plugin->getGuildMember($GuildName) as $key => $value) {
                    if ($max == $count)
                        $GuildMember .= $value;
                    else
                        $GuildMember .= $value . ", ";
                }
            }
            $text .= "\n§6===============\n";
            $text .= "§f길드레벨 : {$GuildLevel}\n";
            $text .= "§f길드명 : {$GuildName}\n";
            $text .= "§f길드장 : {$GuildLeader}\n";
            $text .= "§f부길드장 : {$GuildLeader2}\n";
            $text .= "§f길드원 : {$GuildMember}\n";
            $text .= "§f길드원수 : {$this->plugin->getCountMember($GuildName)}/{$this->plugin->getMaxMember($GuildName)}\n";
            $text .= "§f길드포인트 : {$this->plugin->getGuildPoint($GuildName)}\n";
            $text .= "§f길드경험치 : {$this->plugin->getGuildExp($GuildName)}/{$this->plugin->getGuildMaxExp($GuildName)}\n";
            $text .= "§f공개유무 : {$this->plugin->getSearchAccess($GuildName)}\n";
            $text .= "§6===============\n";
        }
        $form->setContent($text);
        $form->addButton("§l닫기");
        $form->sendToPlayer($player);
    }
}
