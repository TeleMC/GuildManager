<?php
namespace GuildManager\System;

use GuildManager\GuildManager;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use pocketmine\Server;

class GuildRank {
    public function __construct(GuildManager $plugin) {
        $this->plugin = $plugin;
    }

    public function GuildRank($player) {
        arsort($this->plugin->rdata);
        $info = "";
        $count = 0;
        foreach ($this->plugin->rdata as $guild => $exp) {
            $count++;
            $info .= "§6§l[ §f{$count}위 §6] §r§6{$guild} §f| §6Lv.{$this->plugin->getGuildLevel($guild)} §f| §6Exp. {$exp}\n\n";
        }
        $form = $this->plugin->ui->SimpleForm(function (Player $player, array $data) {
        });
        $form->setTitle("Tele Guild");
        $form->setContent($info);
        $form->addButton("§l닫기");
        $form->sendToPlayer($player);
    }
}
