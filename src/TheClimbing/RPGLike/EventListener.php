<?php
    
    declare(strict_types = 1);
    
    
    namespace TheClimbing\RPGLike;
    
    use pocketmine\event\player\PlayerQuitEvent;
    use pocketmine\Player;
    
    use pocketmine\event\Listener;
    
    use pocketmine\event\entity\EntityDamageByEntityEvent;

    use pocketmine\event\player\PlayerMoveEvent;
    use pocketmine\event\player\PlayerRespawnEvent;
    use pocketmine\event\player\PlayerJoinEvent;
    use pocketmine\event\player\PlayerExperienceChangeEvent;
    use pocketmine\event\player\PlayerDeathEvent;

    use TheClimbing\RPGLike\Players\PlayerManager;
    use TheClimbing\RPGLike\Skills\SkillsManager;

    class EventListener implements Listener
    {
        private $rpg;
        
        public function __construct(RPGLike $rpg)
        {
            $this->rpg = $rpg;
        }
        
        public function onJoin(PlayerJoinEvent $event)
        {
            $player = $event->getPlayer();
            PlayerManager::makePlayer($player->getName(), $this->rpg->getModifiers(), SkillsManager::getInstance());
    
        }
        public function onMove(PlayerMoveEvent $event)
        {
            $player = $event->getPlayer();
            $playerName = $player->getName();
            $playerSkills = PlayerManager::getPlayer($playerName)->getSkills();
            if(!empty($playerSkills)){
                foreach($playerSkills as $playerSkill){
                    PlayerManager::getPlayer($playerName)->getSkill($playerSkill)->checkRange($player);
                }
            }
        }
        public function dealDamageEvent(EntityDamageByEntityEvent $event)
        {
            $this->rpg->applyDamageBonus($event);
            $this->rpg->applyDefenseBonus($event);
        }
        
        public function onLevelUp(PlayerExperienceChangeEvent $event)
        {
            $player = $event->getEntity();
            
            $new_lvl = $event->getNewLevel();
            $old_level = $event->getOldLevel();
            
            if($new_lvl !== null) {
                if($new_lvl > $old_level) {
                    if($player instanceof Player) {
                        $spleft = $new_lvl - $old_level;
                        
                        $playerName = $player->getName();
                        
                        $this->rpg->upgradeStatsForm(PlayerManager::getPlayer($playerName), $spleft);
    
                        $this->rpg->applyVitalityBonus($player);
                        $this->rpg->applyDexterityBonus($player);
                        
                        $this->rpg->savePlayerVariables($playerName);
                        
                    }
                }
            }
        }
        
        public function onDeath(PlayerDeathEvent $event)
        {
            PlayerManager::getPlayer($event->getPlayer()->getName())->reset();
        }
        
        public function onRespawn(PlayerRespawnEvent $event)
        {
            $player = $event->getPlayer();
            
            if($player->getXpLevel() == 0) {
                $player->setXpLevel(1);
            }
        }
        public function playerLeave(PlayerQuitEvent $event)
        {
            PlayerManager::removePlayer($event->getPlayer()->getName());
        }
    }