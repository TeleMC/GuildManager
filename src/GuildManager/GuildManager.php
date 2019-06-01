<?php
namespace GuildManager;

use Core\Core;
use Core\util\Util;
use GuildManager\System\CreateGuild;
use GuildManager\System\Demotion;
use GuildManager\System\EntrustGuild;
use GuildManager\System\ExitGuild;
use GuildManager\System\GuildChangeExp;
use GuildManager\System\GuildInfo;
use GuildManager\System\GuildList;
use GuildManager\System\GuildRank;
use GuildManager\System\GuildShop;
use GuildManager\System\InviteMember;
use GuildManager\System\JoinGuild;
use GuildManager\System\KickMember;
use GuildManager\System\Promotion;
use GuildManager\System\RemoveGuild;
use GuildManager\System\RequestJoinlist;
use GuildManager\System\SponsorGuild;
use PacketEventManager\PacketEventManager;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use TeleMoney\TeleMoney;
use UiLibrary\UiLibrary;

//use GuildManager\System\DailyCheck;

class GuildManager extends PluginBase {

    private static $instance = null;
    //public $pre = "§l§6[ §f길드 §6]§r§6";
    public $pre = "§e•";

    public static function getInstance() {
        return self::$instance;
    }

    public function onLoad() {
        self::$instance = $this;
    }

    public function onEnable() {
        @mkdir($this->getDataFolder());
        $this->saveResource("MaxExp.yml");
        $this->guild = new Config($this->getDataFolder() . "Guild.yml", Config::YAML);
        $this->gdata = $this->guild->getAll();
        $this->member = new Config($this->getDataFolder() . "Member.yml", Config::YAML);
        $this->mdata = $this->member->getAll();
        $this->rank = new Config($this->getDataFolder() . "Rank.yml", Config::YAML);
        $this->rdata = $this->rank->getAll();
        $this->accessinvite = new Config($this->getDataFolder() . "AccessInvite.yml", Config::YAML);
        $this->adata = $this->accessinvite->getAll();
        $this->dailycheck = new Config($this->getDataFolder() . "DailyCheck.yml", Config::YAML);
        $this->ddata = $this->dailycheck->getAll();
        $this->maxexp = (new Config($this->getDataFolder() . "MaxExp.yml", Config::YAML))->getAll();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        //$this->getScheduler()->scheduleRepeatingTask(new DailyCheckTask($this), 20);
        $this->money = TeleMoney::getInstance();
        $this->core = Core::getInstance();
        $this->util = new Util($this->core);
        $this->packet = PacketEventManager::getInstance();
        $this->ui = UiLibrary::getInstance();

        $this->CreateGuild = new CreateGuild($this);
        $this->RemoveGuild = new RemoveGuild($this);
        $this->JoinGuild = new JoinGuild($this);
        $this->ExitGuild = new ExitGuild($this);
        $this->RequestJoinlist = new RequestJoinlist($this);
        $this->GuildList = new GuildList($this);
        $this->EntrustGuild = new EntrustGuild($this);
        $this->Promotion = new Promotion($this);
        $this->Demotion = new Demotion($this);
        $this->InviteMember = new InviteMember($this);
        $this->KickMember = new KickMember($this);
        $this->GuildInfo = new GuildInfo($this);
        $this->SponsorGuild = new SponsorGuild($this);
        $this->GuildChangeExp = new GuildChangeExp($this);
        $this->GuildRank = new GuildRank($this);
        $this->GuildShop = new GuildShop($this);
        //$this->DailyCheck = new DailyCheck($this);
    }

    public function onDisable() {
        $this->save();
    }

    public function save() {
        $this->guild->setAll($this->gdata);
        $this->guild->save();
        $this->member->setAll($this->mdata);
        $this->member->save();
        $this->accessinvite->setAll($this->adata);
        $this->accessinvite->save();
        $this->rank->setAll($this->rdata);
        $this->rank->save();
        $this->dailycheck->setAll($this->ddata);
        $this->dailycheck->save();
    }

    public function isGuild(string $name) {
        return isset($this->mdata[$name]);
    }

    public function getAccessInvite(string $name) {
        return $this->adata[$name];
    }

    public function getChatMode(string $name) {
        if (!isset($this->mdata[$name]))
            return false;
        return $this->mdata[$name]["길드채팅"];
    }

    public function addGuild(string $GuildName, string $name, bool $search = true) {
        if (isset($this->mdata[$name])) return;
        if (isset($this->gdata[$GuildName])) return;
        if ($this->money->getMoney($name) < 200000) return;
        $this->money->reduceMoney($name, 200000);
        $this->gdata[$GuildName] = [];
        $this->gdata[$GuildName]["길드"] = $GuildName;
        $this->gdata[$GuildName]["길드장"] = $name;
        $this->gdata[$GuildName]["부길드장"] = [];
        $this->gdata[$GuildName]["길드원"] = [];
        $this->gdata[$GuildName]["전체길드원"] = [];
        $this->gdata[$GuildName]["전체길드원"][$name] = $name;
        $this->gdata[$GuildName]["공개유무"] = $search;
        $this->gdata[$GuildName]["레벨"] = 1;
        $this->gdata[$GuildName]["포인트"] = 0;
        $this->gdata[$GuildName]["경험치"] = 0;
        $this->gdata[$GuildName]["누적경험치"] = 0;
        $this->gdata[$GuildName]["최대경험치"] = 100000;
        $this->gdata[$GuildName]["최대멤버수"] = 15;
        $this->gdata[$GuildName]["가입요청"] = [];
        $this->gdata[$GuildName]["출석체크"] = 0;

        $this->rdata[$GuildName] = $this->gdata[$GuildName]["누적경험치"];

        $this->mdata[$name] = [];
        $this->mdata[$name]["길드"] = $GuildName;
        $this->mdata[$name]["직위"] = "길드장";
        $this->mdata[$name]["길드채팅"] = "off";
    }

    public function getGuildLeader2(string $GuildName) {
        if (!isset($this->gdata[$GuildName]))
            return null;
        else {
            $leader2 = [];
            if (count($this->gdata[$GuildName]["부길드장"]) <= 0)
                return $leader2;
            foreach ($this->gdata[$GuildName]["부길드장"] as $key => $value) {
                $leader2[] = $value;
            }
            return $leader2;
        }
    }

    public function getSearchAccess(string $GuildName) {
        if (!isset($this->gdata[$GuildName]))
            return null;
        return $this->gdata[$GuildName]["공개유무"];
    }

    public function getGuildMember(string $GuildName) {
        if (!isset($this->gdata[$GuildName]))
            return null;
        else {
            $member = [];
            if (count($this->gdata[$GuildName]["길드원"]) <= 0)
                return $member;
            foreach ($this->gdata[$GuildName]["길드원"] as $key => $value) {
                $member[] = $value;
            }
            return $member;
        }
    }

    public function addGuildPoint(string $GuildName, int $amount) {
        if (!isset($this->gdata[$GuildName]))
            return false;
        if ($amount < 0)
            return false;
        $this->gdata[$GuildName]["포인트"] += $amount;
        return true;
    }

    public function reduceGuildPoint(string $GuildName, int $amount) {
        if (!isset($this->gdata[$GuildName]))
            return false;
        if ($amount < 0)
            return false;
        if ($amount > $this->getGuildPoint($GuildName))
            return false;
        $this->gdata[$GuildName]["포인트"] -= $amount;
        return true;
    }

    public function getGuildPoint(string $GuildName) {
        if (!isset($this->gdata[$GuildName]))
            return null;
        return $this->gdata[$GuildName]["포인트"];
    }

    public function ExitGuild(string $name) {
        if (!isset($this->mdata[$name]))
            return false;
        $GuildName = $this->getGuild($name);
        if ($this->mdata[$name]["직위"] == "길드장")
            return false;
        $this->setChatMode($name, false);
        if ($this->getPosition($name) == "부길드장")
            unset($this->gdata[$GuildName]["부길드장"][$name]);
        else
            unset($this->gdata[$GuildName]["길드원"][$name]);
        unset($this->gdata[$GuildName]["전체길드원"][$name]);
        unset($this->mdata[$name]);
        return true;
    }

    public function getGuild(string $name) {
        if (!isset($this->mdata[$name]))
            return null;
        return $this->mdata[$name]["길드"];
    }

    public function setChatMode(string $name, bool $type = false) {
        if (!isset($this->mdata[$name]))
            return false;
        $this->mdata[$name]["길드채팅"] = $type;
        return true;
    }

    public function getPosition(string $name) {
        if (!isset($this->mdata[$name])) return;
        return $this->mdata[$name]["직위"];
    }

    public function InviteGuild(string $name, string $GuildName) {
        if ($this->getServer()->getPlayer($name) instanceof Player) {
            $this->guildn[$this->getServer()->getPlayer($name)->getName()] = $GuildName;
            $form = $this->ui->ModalForm(function (Player $player, array $data) {
                if ($data[0] == true) {
                    $player->sendMessage("{$this->pre} {$this->guildn[$player->getName()]} 길드의 초대를 수락하여 가입되었습니다!");
                    $this->JoinGuild($this->guildn[$player->getName()], $player->getName());
                    $this->packet->setTag($player);
                    if (isset($this->gdata[$this->guildn[$player->getName()]]["가입요청"][$player->getName()])) {
                        unset($this->gdata[$this->guildn[$player->getName()]]["가입요청"][$player->getName()]);
                    }
                    unset($this->guildn[$player->getName()]);
                    return;
                } else {
                    $player->sendMessage("{$this->pre} {$this->guildn[$player->getName()]} 길드의 초대를 거절하였습니다.");
                    if ($this->getServer()->getPlayer($this->getGuildLeader($this->guildn[$player->getName()]))) {
                        $this->getServer()->getPlayer($this->getGuildLeader($this->guildn[$player->getName()]))->sendMessage("{$this->pre} {$player->getName()}님이 초대를 거절하셨습니다.");
                    }
                    /*if($this->getServer()->getPlayer($this->getGuildLeader2($this->guildn[$player->getName()]))){
                      $this->getServer()->getPlayer($this->getGuildLeader2($this->guildn[$player->getName()]))->sendMessage("{$this->pre} {$player->getName()}님이 초대를 거절하셨습니다.");
                    }*/
                    if (isset($this->gdata[$this->guildn[$player->getName()]]["가입요청"][$player->getName()])) {
                        unset($this->gdata[$this->guildn[$player->getName()]]["가입요청"][$player->getName()]);
                    }
                    unset($this->guildn[$player->getName()]);
                    return;
                }
            });
            $form->setTitle("Tele Guild");
            $form->setContent("\n{$GuildName} 길드에서 초대장이 왔습니다! ( 길드장 : {$this->getGuildLeader($GuildName)})\n수락하시겠습니까?");
            $form->setButton1("§l§7[수락]");
            $form->setButton2("§l§7[거절]");
            $send = $form->sendToPlayer($this->getServer()->getPlayer($name));
            return $send;
        }
    }

    public function JoinGuild(string $GuildName, string $name) {
        if (!isset($this->gdata[$GuildName]))
            return false;
        if (isset($this->mdata[$name]))
            return false;
        if ($this->getCountMember($GuildName) >= $this->getMaxMember($GuildName))
            return false;
        foreach ($this->gdata[$GuildName]["전체길드원"] as $members) {
            if (($player = $this->getServer()->getPlayer($members)) instanceof Player) {
                $player->sendMessage("{$this->pre} {$name}님이 길드에 새로 가입하셨습니다!");
            }
        }
        $this->gdata[$GuildName]["길드원"][$name] = $name;
        $this->gdata[$GuildName]["전체길드원"][$name] = $name;
        $this->mdata[$name] = [];
        $this->mdata[$name]["길드"] = $GuildName;
        $this->mdata[$name]["직위"] = "길드원";
        $this->mdata[$name]["길드채팅"] = "off";
    }

    public function getCountMember(string $GuildName) {
        if (!isset($this->gdata[$GuildName])) return;
        return count($this->gdata[$GuildName]["전체길드원"]);
    }

    public function getMaxMember(string $GuildName) {
        if (!isset($this->gdata[$GuildName]))
            return null;
        return $this->gdata[$GuildName]["최대멤버수"];
    }

    public function getGuildLeader(string $GuildName) {
        if (!isset($this->gdata[$GuildName]))
            return null;
        return $this->gdata[$GuildName]["길드장"];
    }

    public function KickMember(string $name) {
        if (!isset($this->mdata[$name]))
            return false;
        $GuildName = $this->getGuild($name);
        $this->setChatMode($name, false);
        if ($this->mdata[$name]["직위"] == "부길드장") {
            unset($this->gdata[$GuildName]["부길드장"][$name]);
            unset($this->gdata[$GuildName]["전체길드원"][$name]);
            unset($this->mdata[$name]);
            return true;
        }
        unset($this->gdata[$GuildName]["길드원"][$name]);
        unset($this->gdata[$GuildName]["전체길드원"][$name]);
        unset($this->mdata[$name]);
        return true;
    }

    public function removeGuild(string $GuildName) {
        if (!isset($this->gdata[$GuildName]))
            return false;
        foreach ($this->gdata[$GuildName]["전체길드원"] as $members) {
            unset($this->mdata[$members]);
        }
        unset($this->gdata[$GuildName]);
        return true;
    }

    public function EntrustGuild(string $GuildName, string $name) { // 위임
        if (!isset($this->gdata[$GuildName]))
            return false;
        if (!isset($this->mdata[$name]))
            return false;
        if ($this->mdata[$name]["길드"] !== $GuildName)
            return false;
        if ($this->mdata[$name]["직위"] == "길드장")
            return false;
        if ($this->mdata[$name]["직위"] == "길드원") {
            $this->mdata[$name]["직위"] = "길드장";
            $this->mdata[$this->gdata[$GuildName]["길드장"]]["직위"] = "길드원";
            $this->gdata[$GuildName]["길드원"][$this->gdata[$GuildName]["길드장"]] = $this->gdata[$GuildName]["길드장"];
            $this->gdata[$GuildName]["길드장"] = $name;
            unset($this->gdata[$GuildName]["길드원"][$name]);
            return true;
        } elseif ($this->mdata[$name]["직위"] == "부길드장") {
            $this->mdata[$name]["직위"] = "길드장";
            $this->mdata[$this->gdata[$GuildName]["길드장"]]["직위"] = "길드원";
            $this->gdata[$GuildName]["길드원"][$this->gdata[$GuildName]["길드장"]] = $this->gdata[$GuildName]["길드장"];
            $this->gdata[$GuildName]["길드장"] = $name;
            unset($this->gdata[$GuildName]["부길드장"][$name]);
            return true;
        }
    }

    public function Promotion(string $GuildName, string $name) { // 승진?
        if (!isset($this->gdata[$GuildName]))
            return false;
        if (!isset($this->mdata[$name]))
            return false;
        if ($this->mdata[$name]["길드"] !== $GuildName)
            return false;
        if ($this->mdata[$name]["직위"] == "길드장" || $this->mdata[$name]["직위"] == "부길드장")
            return false;
        $this->gdata[$GuildName]["부길드장"][$name] = $name;
        $this->mdata[$name]["직위"] = "부길드장";
        unset($this->gdata[$GuildName]["길드원"][$name]);
        return true;
    }

    public function Demotion(string $GuildName, string $name) { // 강등
        if (!isset($this->gdata[$GuildName]))
            return false;
        if (!isset($this->mdata[$name]))
            return false;
        if ($this->mdata[$name]["길드"] !== $GuildName)
            return false;
        if ($this->mdata[$name]["직위"] == "길드장" || $this->mdata[$name]["직위"] == "길드원")
            return false;
        $this->gdata[$GuildName]["길드원"][$name] = $name;
        $this->mdata[$name]["직위"] = "길드원";
        unset($this->gdata[$GuildName]["부길드장"][$name]);
        return true;
    }

    public function DailyCheck(Player $player) {
        $name = $player->getName();
        if (!isset($this->mdata[$name]))
            return false;
        if (isset($this->ddata[$name])) {
            $player->sendMessage("{$this->pre} 당일의 출석체크는 이미 완료하였습니다.");
            return false;
        }
        if (!isset($this->ddata[$name])) {
            $player->sendMessage("{$this->pre} 출석체크를 완료하였습니다!");
            $this->ddata[$name] = "완료";
            $this->gdata[$this->getGuild($name)]["출석체크"] = +1;
            $this->addGuildExp($this->getGuild($name), 100);
            return true;
        }
    }

    public function addGuildExp(string $GuildName, int $amount) {
        if (!isset($this->gdata[$GuildName])) return;
        if ($amount < 0) return;
        if (($this->getGuildExp($GuildName) + $amount) < $this->getGuildMaxExp($GuildName)) {
            foreach ($this->gdata[$GuildName]["전체길드원"] as $members) {
                if (!isset($this->exp[$GuildName])) {
                    if (($player = $this->getServer()->getPlayer($members)) instanceof Player) {
                        $player->sendMessage("{$this->pre} 길드 경험치 {$amount}를 획득하였습니다!");
                    }
                }
            }
            $this->gdata[$GuildName]["경험치"] += $amount;
            $this->FixAllExp($GuildName);
            arsort($this->rdata);
            return;
        }
        if (($this->getGuildExp($GuildName) + $amount) >= $this->getGuildMaxExp($GuildName)) {
            foreach ($this->gdata[$GuildName]["전체길드원"] as $members) {
                if (!isset($this->exp[$GuildName])) {
                    if (($player = $this->getServer()->getPlayer($members)) instanceof Player) {
                        $player->sendMessage("{$this->pre} 길드 경험치 {$amount}를 획득하였습니다!");
                    }
                }
            }
            $this->exp[$GuildName] = 0;
            $a = ($this->getGuildExp($GuildName) + $amount);
            $b = $a - $this->getGuildMaxExp($GuildName);
            $this->Guildlevelup($GuildName);
            if ($b == 0) {
                unset($this->exp[$GuildName]);
                return;
            }
            $this->addGuildExp($GuildName, $b);
        }
    }

    public function getGuildExp(string $GuildName) {
        if (!isset($this->gdata[$GuildName]))
            return null;
        return $this->gdata[$GuildName]["경험치"];
    }

    public function getGuildMaxExp(string $GuildName) {
        if (!isset($this->gdata[$GuildName]))
            return null;
        return $this->gdata[$GuildName]["최대경험치"];
    }

    public function FixAllExp(string $GuildName) {
        $GuildLevel = $this->getGuildLevel($GuildName);
        $minExp = 0;
        foreach ($this->maxexp as $level => $value) {
            if ($GuildLevel > $level)
                $minExp += $value;
            else
                break;
        }
        $minExp += $this->getGuildExp($GuildName);
        $this->gdata[$GuildName]["누적경험치"] = $minExp;
        $this->rdata[$GuildName] = $minExp;
    }

    /*public function getCountMember(string $GuildName){
      if(!isset($this->gdata[$GuildName])) return;
      return count($this->gdata[$GuildName]["전체길드원"]);
    }

    public function getPosition(string $name){
      if(!isset($this->mdata[$name])) return;
      return $this->mdata[$name]["직위"];
    }*/

    public function getGuildLevel(string $GuildName) {
        if (!isset($this->gdata[$GuildName]))
            return null;
        return $this->gdata[$GuildName]["레벨"];
    }

    public function Guildlevelup(string $GuildName) {
        if ($this->getGuildLevel($GuildName) >= 10)
            return false;
        $this->gdata[$GuildName]["레벨"] += 1;
        if ($this->getGuildLevel($GuildName) >= 10) $this->gdata[$GuildName]["경험치"] = 900000;
        if ($this->getGuildLevel($GuildName) < 10) $this->gdata[$GuildName]["경험치"] = 0;
        if ($this->getGuildLevel($GuildName) >= 10) $this->gdata[$GuildName]["최대경험치"] = 900000;
        if ($this->getGuildLevel($GuildName) < 10) $this->gdata[$GuildName]["최대경험치"] = $this->maxexp[$this->getGuildLevel($GuildName)];
        foreach ($this->gdata[$GuildName]["전체길드원"] as $members) {
            if (($player = $this->getServer()->getPlayer($members)) instanceof Player) {
                $player->sendMessage("{$this->pre} 길드 등급이 올라갔습니다! Lv.{$this->getGuildLevel($GuildName)}");
            }
        }
        return true;
    }

    public function GuildUI($player) {
        if ($player instanceof Player) {
            if (!isset($this->mdata[$player->getName()])) {
                $form = $this->ui->SimpleForm(function (Player $player, array $data) {
                    if (!is_numeric($data[0])) return;
                    $name = $player->getName();
                    if ($data[0] == 0) {
                        $this->CreateGuild->CreateGuild($player);
                    }
                    if ($data[0] == 1) {
                        $this->JoinGuild->JoinGuild($player);
                    }
                    if ($data[0] == 2) {
                        if ($this->adata[$name] == "허용") {
                            $player->sendMessage("{$this->pre} 길드 초대 수신을 거부하였습니다.");
                            $this->adata[$name] = "거부";
                            return;
                        } elseif ($this->adata[$name] == "거부") {
                            $player->sendMessage("{$this->pre} 길드 초대 수신을 허용하였습니다.");
                            $this->adata[$name] = "허용";
                            return;
                        }
                    }
                    if ($data[0] == 3) {
                        $this->GuildList->GuildList($player);
                    }
                });
                $form->setTitle("Tele Guild");
                $form->addButton("§l길드 생성\n§r§820만테나로 길드를 생성합니다.");//
                $form->addButton("§l길드 가입\n§r§8길드에 가입합니다.");//
                $form->addButton("§l길드 초대 수신여부\n§r§8길드 초대수신을 허용,차단시킵니다.");//
                $form->addButton("§l길드 목록\n§r§8길드 목록을 확인합니다.");//
                $form->addButton("§l닫기");
                $form->sendToPlayer($player);
            } else {
                $name = $player->getName();
                $form = $this->ui->SimpleForm(function (Player $player, array $data) {
                    if (!is_numeric($data[0])) return;
                    $name = $player->getName();
                    if ($data[0] == 0) {
                        if ($this->getPosition($name) == "길드장") $this->EntrustGuild->EntrustGuild($player);
                        if ($this->getPosition($name) == "부길드장") $this->InviteMember->InviteMember($player);
                        if ($this->getPosition($name) == "길드원") $this->GuildInfo->GuildInfo($player);
                    }
                    if ($data[0] == 1) {
                        if ($this->getPosition($name) == "길드장") $this->Promotion->Promotion($player);
                        if ($this->getPosition($name) == "부길드장") $this->KickMember->KickMember($player);
                        if ($this->getPosition($name) == "길드원") $this->SponsorGuild->SponsorGuild($player);
                    }
                    if ($data[0] == 2) {
                        if ($this->getPosition($name) == "길드장") $this->Demotion->Demotion($player);
                        if ($this->getPosition($name) == "부길드장") $this->RequestJoinlist->RequestJoinlist($player);
                        if ($this->getPosition($name) == "길드원") $player->sendMessage("{$this->pre} 준비중인 기능입니다."); //$this->DailyCheck($player);
                    }
                    if ($data[0] == 3) {
                        if ($this->getPosition($name) == "길드장") $this->RemoveGuild->RemoveGuild($player);
                        if ($this->getPosition($name) == "부길드장") $player->sendMessage("{$this->pre} 준비중인 기능입니다."); //$this->GuildShop->GuildShop($player);
                        if ($this->getPosition($name) == "길드원") $this->GuildRank->GuildRank($player);
                    }
                    if ($data[0] == 4) {
                        if ($this->getPosition($name) == "길드장") $this->InviteMember->InviteMember($player);
                        if ($this->getPosition($name) == "부길드장") $this->GuildChangeExp->GuildChangeExp($player);
                        if ($this->getPosition($name) == "길드원") $this->GuildList->GuildList($player);
                    }
                    if ($data[0] == 5) {
                        if ($this->getPosition($name) == "길드장") $this->KickMember->KickMember($player);
                        if ($this->getPosition($name) == "부길드장") $this->GuildInfo->GuildInfo($player);
                        if ($this->getPosition($name) == "길드원") $this->ExitGuild->ExitGuild($player);
                    }
                    if ($data[0] == 6) {
                        if ($this->getPosition($name) == "길드장") $this->RequestJoinlist->RequestJoinlist($player);
                        if ($this->getPosition($name) == "부길드장") $this->SponsorGuild->SponsorGuild($player);
                    }
                    if ($data[0] == 7) {
                        if ($this->getPosition($name) == "길드장") $player->sendMessage("{$this->pre} 준비중인 기능입니다."); //$this->GuildShop->GuildShop($player);
                        if ($this->getPosition($name) == "부길드장") $player->sendMessage("{$this->pre} 준비중인 기능입니다."); //$this->DailyCheck($player);
                    }
                    if ($data[0] == 8) {
                        if ($this->getPosition($name) == "길드장") $this->GuildChangeExp->GuildChangeExp($player);
                        if ($this->getPosition($name) == "부길드장") $this->GuildRank->GuildRank($player);
                    }
                    if ($data[0] == 9) {
                        if ($this->getPosition($name) == "길드장") $this->GuildInfo->GuildInfo($player);
                        if ($this->getPosition($name) == "부길드장") $this->GuildList->GuildList($player);
                    }
                    if ($data[0] == 10) {
                        if ($this->getPosition($name) == "길드장") $this->SponsorGuild->SponsorGuild($player);
                        if ($this->getPosition($name) == "부길드장") $this->ExitGuild->ExitGuild($player);
                    }
                    if ($data[0] == 11) {
                        $player->sendMessage("{$this->pre} 준비중인 기능입니다.");
                        //if($this->getPosition($name) == "길드장") $this->DailyCheck($player);
                    }
                    if ($data[0] == 12) {
                        if ($this->getPosition($name) == "길드장") $this->GuildRank->GuildRank($player);
                    }
                    if ($data[0] == 13) {
                        if ($this->getPosition($name) == "길드장") $this->GuildList->GuildList($player);
                    }
                    if ($data[0] == 14) {
                        if ($this->getPosition($name) == "길드장") $this->ExitGuild->ExitGuild($player);
                    }
                });
                $form->setTitle("Tele Guild");
                if ($this->getPosition($name) == "길드장") {
                    $form->addButton("§l길드 위임\n§r§8길드를 위임합니다.");//
                    $form->addButton("§l계급 승급\n§r§8계급을 승급시킵니다.");//
                    $form->addButton("§l계급 강등\n§r§8계급을 강등시킵니다.");//
                    $form->addButton("§l길드 해체\n§r§8길드를 해체시킵니다.");//
                }
                if (($this->getPosition($name) == "길드장") or ($this->getPosition($name) == "부길드장")) {
                    $form->addButton("§l길드 초대\n§r§8길드에 유저를 초대합니다.");//
                    $form->addButton("§l길드 추방\n§r§8길드원을 내보냅니다.");//
                    $form->addButton("§l가입 요청목록\n§r§8가입 요청목록을 불러옵니다.");//
                    $form->addButton("§l길드 상점\n§r§8길드 상점을 불러옵니다.");
                    $form->addButton("§l길드 경험치전환\n§r§8길드 경험치를 전환합니다.");//
                }
                $form->addButton("§l길드 조회\n§r§8길드 정보를 확인합니다.");//
                $form->addButton("§l길드 후원\n§r§8길드에 후원합니다.");//
                $form->addButton("§l출석 체크\n§r§8출석체크를 합니다.");//
                $form->addButton("§l길드 순위\n§r§8길드 순위를 확인합니다.");//
                $form->addButton("§l길드 목록\n§r§8길드 목록을 확인합니다.");//
                $form->addButton("§l길드 탈퇴\n§r§8길드에서 탈퇴합니다.");//
                $form->addButton("§l닫기");
                $form->sendToPlayer($player);
            }
        }
    }

    public function Time($time) {
        return date("h시 i분 s초", $time);
    }

}
