<?php
namespace GuildManager\System;

use GuildManager\GuildManager;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use pocketmine\Server;

class GuildShop {
    public function __construct(GuildManager $plugin) {
        $this->plugin = $plugin;
    }

    public function GuildShop($player) {
        $name = $player->getName();
        $form = $this->plugin->ui->SimpleForm(function (Player $player, array $data) {
            if ($data[0] == 0) {
            }
            if ($data[0] == 1) {
            }
            if ($data[0] == 2) {
            }
            if ($data[0] == 3) {
            }
            if ($data[0] == 4) {
            }
            if ($data[0] == 5) {
            }
            if ($data[0] == 6) {
            }
            if ($data[0] == 7) {
            }
            if ($data[0] == 8) {
            }
        });
        $form->setTitle("Tele Guild");
        $form->setContent($info);
        $form->addButton("§l길드명 변경문서\n§r§8길드명을 변겅합니다.");
        $form->addButton("§l최대 길드원 5명 증가 문서\n§r§8최대 길드원 수를 5명 증가시킵니다.");
        $form->addButton("§l길드원 공격력 3%증가 1시간 주문서\n§r§8해당 주문서를 구매합니다.");
        $form->addButton("§l길드원 공격력 7%증가 1시간 주문서\n§r§8해당 주문서를 구매합니다.");
        $form->addButton("§l출석 시 지급되는 길드 경험치 3%증가 3일 주문서\n§r§8해당 주문서를 구매합니다.");
        $form->addButton("§l출석 시 지급되는 길드 경험치 3%증가 3일 주문서\n§r§8해당 주문서를 구매합니다.");
        $form->addButton("§l길드원 레벨업 시 지급되는 길드 경험치 1%증가 주문서 \n§r§8해당 주문서를 구매합니다.");
        $form->addButton("§l길드원 획득 경험치량 5%증가 주문서\n§r§8해당 주문서를 구매합니다.");
        $form->addButton("§l길드명 색깔 랜덤 변경 주문서\n§r§8해당 주문서를 구매합니다.");
        $form->addButton("§l닫기");
        $form->sendToPlayer($player);
    }
}
