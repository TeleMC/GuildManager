<?php

namespace GuildManager\task;

use GuildManager\GuildManager;
use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\Server;

class DailyCheckTask extends Task {

    public function __construct(GuildManager $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun($currentTick) {
        if ($this->plugin->Time(time()) == "0시 0분 0초") {
            unset($this->plugin->ddata);
            Server::getInstance()->broadcastMessage("{$this->plugin->pre} 하루가 지나 길드 출석체크 초기화 되었습니다!");
            if (empty($this->plugin->gdata)) return;
            if (!empty($this->plugin->gdata)) {
                foreach ($this->plugin->gdata as $guild => $info) {
                    $this->plugin->gdata[$guild]["출석체크"] = 0;
                }
            }
        }
    }
}
