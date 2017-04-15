<?php

namespace IdleBundle\Controller;

use AppBundle\Entity\User;
use IdleBundle\Entity\BattleHistory;
use IdleBundle\Entity\Craft;
use IdleBundle\Entity\Enemy;
use IdleBundle\Entity\Equipment;
use IdleBundle\Entity\Hero;
use IdleBundle\Entity\Inventory;
use IdleBundle\Entity\PossessedRecipes;
use IdleBundle\Entity\Stuff;
use IdleBundle\Entity\Target;
use IdleBundle\Entity\TypeStuff;
use IdleBundle\Services\BattleManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ServerController extends Controller
{
    public function timecmp($a, $b)
    {
        if ($a < $b)
            return -1;
        else if ($a > $b)
            return 1;
        return 0;
    }

    public function build_sorter($key) {
        return function ($a, $b) use ($key) {
            return $this->timecmp($a[$key], $b[$key]);
        };
    }

    /**
     * @Route("/battle-history/{hero_id}", name="battle_history")
     */
    public function ajaxChangeSelectedCharacter(Request $request, $hero_id)
    {
        $em = $this->getDoctrine()->getManager();

//        /** @var BattleManager $battle_manager */
//        $battle_manager = $this->container->get('idle.battle_manager');

        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();
        /** @var Hero $hero */
        $hero = $em->getRepository('IdleBundle:Hero')->findOneBy(array('id' => $hero_id, 'user' => $user));
        if (!$hero) {
            $this->addFlash('error', "Hero not found.");
            return new JsonResponse(array('success' => false));
        }

        $last_battle = $em->getRepository('IdleBundle:BattleHistory')->findOneBy(array('hero' => $hero));
        if (!$last_battle) {
            $last_battle = new BattleHistory();
            $last_battle->setHero($hero);
        }

        $historic = json_decode($last_battle->getHistoric(), true);
        $now = microtime(true);
        $battle_history = array();
        $start_time = 0;
        $until = (60 * 5); // 5 minutes

        // Get a piece of the previous historic
        if ($historic) {
            $target = $hero->getTarget();
            $em->persist($target);
            if (end($historic)['time'] > $now) { // We play the past events
                foreach ($historic as $key => $histo) {
//                    $battle_manager->applyOneLine($histo, $hero, $target);
                    if ($histo['type'] == "HIT_E") {
                        $target->setCurrentHealth($target->getCurrentHealth() - $histo['damage']);
                    } else if ($histo['type'] == "HIT_H") {
                        $hero->setCurrentHealth($hero->getCurrentHealth() - $histo['damage']);
                    } else if ($histo['type'] == "GEN") {

                    }

                    if ($histo['time'] < $now) {
                        $battle_history = array_splice($historic, $key);
                        $start_time = ((count($battle_history) + 1) * $hero->getCharacteristics()->getAttackDelay()); // +1 to skip the current cell, because we keep it and want to generate the following
                        break;
                    }
                }
                $now = end($historic)['time']; // now generate historic from the end of the historic
                $em->flush();
            } else if (end($historic)['time'] < $now) { // We play all the events
                foreach ($historic as $key => $histo) {
//                    $battle_manager->applyOneLine($histo, $hero, $target);
                    if ($histo['type'] == "HIT_E") {
                        $target->setCurrentHealth($target->getCurrentHealth() - $histo['damage']);
                    } else if ($histo['type'] == "HIT_H") {
                        $hero->setCurrentHealth($hero->getCurrentHealth() - $histo['damage']);
                    } else if ($histo['type'] == "GEN") {

                    }
                }
                // TODO : Generate average battle and execute until now
            }
        }

        ///////////////////////
        // Fill the historic //
        ///////////////////////
        // Enemy
        $cumul_damage = 0;
        $time = $start_time;
        while ($time <= $until) {
            if ($time == 0) { // first generate an enemy
                /** @var Enemy $enemy */
                $enemy = $em->getRepository('IdleBundle:Enemy')->find(1); // TODO : Randomise
                $hero->getTarget()->setCurrentHealth($enemy->getCharacteristics()->getHealth());
                $hero->getTarget()->setEnemy($enemy);
                $em->flush();

                $battle_history[] = array(
                    'type' => "GEN",
                    'time' => $now + $time,
                    'currentHealth' => $hero->getTarget()->getCurrentHealth(),
                    'health' => $hero->getTarget()->getEnemy()->getCharacteristics()->getHealth(),
                    'characteristics' => array());
            }
            else {
                $damage = rand($hero->getCharacteristics()->getDamageMinimum(), $hero->getCharacteristics()->getDamageMaximum());
                $cumul_damage += $damage;

                $battle_history[] = array(
                    'type' => "HIT_E",
                    'time' => $now + $time,
                    'damage' => $damage,
                    'currentHealth' => $hero->getTarget()->getCurrentHealth() - $cumul_damage,
                    'health' => $hero->getTarget()->getEnemy()->getCharacteristics()->getHealth());

                // TODO : If currentHealth <= 0, Generate new enemy and kill the previous one, get Loot
                if (end($battle_history)['currentHealth'] <= 0) {
                    // TODO : Loot line
                    $cumul_damage = 0;
                    $time += 1;
                    /** @var Enemy $enemy */
                    $enemy = $em->getRepository('IdleBundle:Enemy')->find(1); // TODO : Randomise
                    $hero->getTarget()->setCurrentHealth($enemy->getCharacteristics()->getHealth());
                    $hero->getTarget()->setEnemy($enemy);
                    $em->flush();

                    $battle_history[] = array( // TODO : Get image
                        'type' => "GEN",
                        'time' => $now + $time,
                        'image' => $this->container->get('templating.helper.assets')->getUrl('images/Idle/Enemy/' . $hero->getTarget()->getEnemy()->getImage()),
                        'currentHealth' => $hero->getTarget()->getCurrentHealth(),
                        'health' => $hero->getTarget()->getEnemy()->getCharacteristics()->getHealth(),
                        'characteristics' => array()); // TODO : Send Characs
                }
            }

            $time += $hero->getCharacteristics()->getAttackDelay();
        }

        // Hero
        $cumul_damage = 0;
        $time = $start_time + $hero->getTarget()->getEnemy()->getCharacteristics()->getAttackDelay();
        while ($time <= $until) {
            $damage = rand($hero->getTarget()->getEnemy()->getCharacteristics()->getDamageMinimum(), $hero->getTarget()->getEnemy()->getCharacteristics()->getDamageMaximum());
            $cumul_damage += $damage;

            $battle_history[] = array(
                'type' => "HIT_H",
                'time' => $now + $time,
                'damage' => $damage,
                'currentHealth' => $hero->getCurrentHealth() - $cumul_damage,
                'health' => $hero->getCharacteristics()->getHealth());

            $time += $hero->getTarget()->getEnemy()->getCharacteristics()->getAttackDelay();
        }

        usort($battle_history, $this->build_sorter('time'));

        $last_battle->setHistoric(json_encode($battle_history));
        $em->persist($last_battle);
        $em->flush();

        return new JsonResponse(array('success' => true, 'battle_history' => $battle_history));
    }

    /**
     * @Route("/show-recipe-crafts/{recipe_id}", name="show_recipe_crafts")
     */
    public function ajaxShowRecipeCrafts(Request $request, $recipe_id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        /** @var PossessedRecipes $possessed_recipe */
        $possessed_recipe = $em->getRepository('IdleBundle:PossessedRecipes')->findOneBy(array('user' => $user, 'recipe' => $recipe_id));
        if (!$possessed_recipe) {
            return new JsonResponse(array('success' => false));
        }

        $crafts = $em->getRepository('IdleBundle:Craft')->findBy(array('recipe' => $possessed_recipe->getRecipe()));

        $craftable = true;
        $tab_crafts = array();
        $i = 0;
        /** @var Craft $craft */
        foreach ($crafts as $craft) {
            $tab_crafts[$i]['id'] = $craft->getItemNeeded()->getId();
            $tab_crafts[$i]['name'] = $craft->getItemNeeded()->getName();
            $tab_crafts[$i]['image'] = $this->container->get('templating.helper.assets')->getUrl('images/Idle/' . $craft->getItemNeeded()->getTypeItem()->getName() . 's/' . $craft->getItemNeeded()->getImage());
            /** @var Inventory $inv */
            $inv = $em->getRepository('IdleBundle:Inventory')->findOneBy(array('item' => $craft->getItemNeeded()));
            $tab_crafts[$i]['possessed'] = $inv->getQuantity();
            $tab_crafts[$i]['needed'] = $craft->getQuantity();

            if ($tab_crafts[$i]['possessed'] < $tab_crafts[$i]['needed'])
                $craftable = false;

            $i++;
        }

        return new JsonResponse(array('success' => true, 'crafts' => $tab_crafts, 'craftable' => $craftable));
    }

    /**
     * @Route("/show-item-recipes/{item_id}", name="show_item_recipes")
     */
    public function ajaxShowItemRecipes(Request $request, $item_id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        /** @var Inventory $inventory */
        $inventory = $em->getRepository('IdleBundle:Inventory')->findOneBy(array('user' => $user, 'item' => $item_id));
        if (!$inventory) {
            return new JsonResponse(array('success' => false));
        }

        $possessed_recipes = $em->getRepository('IdleBundle:PossessedRecipes')->getRecipesWithItem($inventory->getItem());

        $tab_recipes = array();
        $i = 0;
        /** @var PossessedRecipes $possessed_recipe */
        foreach ($possessed_recipes as $possessed_recipe) {
            $tab_recipes[$i]['id'] = $possessed_recipe->getRecipe()->getId();

            $i++;
        }

        return new JsonResponse(array('success' => true, 'recipes' => $tab_recipes));
    }

    /**
     * @Route("/craft-recipe/{recipe_id}", name="craft_recipe")
     */
    public function ajaxCraftRecipe(Request $request, $recipe_id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        /** @var PossessedRecipes $possessed_recipe */
        $possessed_recipe = $em->getRepository('IdleBundle:PossessedRecipes')->findOneBy(array('user' => $user, 'recipe' => $recipe_id));
        if (!$possessed_recipe) {
            return new JsonResponse(array('success' => false));
        }

        $tab_items = array();
        $i = 0;
        $crafts = $em->getRepository('IdleBundle:Craft')->findBy(array('recipe' => $possessed_recipe->getRecipe()));
        /** @var Craft $craft */
        foreach ($crafts as $craft) {
            /** @var Inventory $inv */
            $inv = $em->getRepository('IdleBundle:Inventory')->findOneBy(array('user' => $user, 'item' => $craft->getItemNeeded()));

            if ($inv->getQuantity() < $craft->getQuantity()) {
                return new JsonResponse(array('success' => false));
            }
            else {
                $inv->setQuantity($inv->getQuantity() - $craft->getQuantity());

                // Used item
                $tab_items[$i]['id'] = $inv->getItem()->getId();
                $tab_items[$i]['name'] = $inv->getItem()->getName();
                $tab_items[$i]['image'] = $this->container->get('templating.helper.assets')->getUrl('images/Idle/' . $inv->getItem()->getTypeItem()->getName() . 's/' . $inv->getItem()->getImage());
                $tab_items[$i]['possessed'] = $inv->getQuantity();
                $tab_items[$i]['needed'] = $craft->getQuantity();
                $tab_items[$i]['type'] = "craft";

                if ($inv->getQuantity() == 0)
                    $em->remove($inv);
            }

            $i++;
        }

        /** @var Inventory $inventory */
        $inventory = $em->getRepository('IdleBundle:Inventory')->findOneBy(array('user' => $user, 'item' => $possessed_recipe->getRecipe()->getItemCreated()));
        if (!$inventory) {
            $inventory = new Inventory();
            $inventory->setUser($user);
            $inventory->setItem($possessed_recipe->getRecipe()->getItemCreated());
            $inventory->setQuantity(1);
            $tab_items[$i]['type'] = "new";
        }
        else {
            $inventory->setQuantity($inventory->getQuantity() + 1);
        }
        $em->persist($inventory);

        // Added item
        $tab_items[$i]['id'] = $inventory->getItem()->getId();
        $tab_items[$i]['name'] = $inventory->getItem()->getName();
        $tab_items[$i]['image'] = $this->container->get('templating.helper.assets')->getUrl('images/Idle/' . $inventory->getItem()->getTypeItem()->getName() . 's/' . $inventory->getItem()->getImage());
        $tab_items[$i]['possessed'] = $inventory->getQuantity();

//        $em->flush();

        return new JsonResponse(array('success' => true, 'items' => $tab_items));
    }

    /**
     * @Route("/show-equipment-stats/{hero_id}/{type_name}", name="show_equipment_stats")
     */
    public function ajaxShowEquipmentStats(Request $request, $hero_id, $type_name)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        /** @var TypeStuff $type */
        $type = $em->getRepository('IdleBundle:TypeStuff')->findOneBy(array('name' => $type_name));

        /** @var Equipment $equipment */
        $equipment = $em->getRepository('IdleBundle:Equipment')->getEquipmentTypeFromHero($hero_id, $type);
        if (!$equipment && $type->getName() == "Weapon") {
            /** @var Hero $hero */
            $hero = $em->getRepository('IdleBundle:Hero')->findOneBy(array('user' => $user, 'id' => $hero_id));
            if (!$hero) {
                return new JsonResponse(array('success' => false));
            }
            $stats = $em->getRepository('IdleBundle:Characteristics')->getStatsInArray($hero->getCharacteristics())[0];
            unset($stats['health']);
        }
        else if (!$equipment || ($equipment && $equipment->getHero()->getUser() != $user)) {
            return new JsonResponse(array('success' => false));
        }
        else {
            $stats = $em->getRepository('IdleBundle:Characteristics')->getStatsInArray($equipment->getStuff()->getCharacteristics())[0];
        }

        unset($stats['id']);

        return new JsonResponse(array('success' => true, 'stats' => $stats));
    }

    /**
     * @Route("/show-stuff-stats/{stuff_id}", name="show_stuff_stats")
     */
    public function ajaxShowStuffStats(Request $request, $stuff_id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        /** @var Stuff $stuff */
        $stuff = $em->getRepository('IdleBundle:Stuff')->find($stuff_id);
        if (!$stuff) {
            return new JsonResponse(array('success' => false));
        }

        $inventory = $em->getRepository('IdleBundle:Inventory')->findOneBy(array('user' => $user, 'item' => $stuff->getItem()));
        if (!$inventory) {
            return new JsonResponse(array('success' => false));
        }

        $stats = $em->getRepository('IdleBundle:Characteristics')->getStatsInArray($stuff->getCharacteristics())[0];
        unset($stats['id']);

        return new JsonResponse(array('success' => true, 'stats' => $stats));
    }

    /**
     * @Route("/drop-equipment/{hero_id}/{type_name}", name="drop_equipment")
     */
    public function ajaxDropEquipment(Request $request, $hero_id, $type_name)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        /** @var TypeStuff $type */
        $type = $em->getRepository('IdleBundle:TypeStuff')->findOneBy(array('name' => $type_name));

        /** @var Equipment $equipment */
        $equipment = $em->getRepository('IdleBundle:Equipment')->getEquipmentTypeFromHero($hero_id, $type->getId());
        if (!$equipment || ($equipment && $equipment->getHero()->getUser() != $user)) {
            return new JsonResponse(array('success' => false));
        }

        /** @var Inventory $inventory */
        $inventory = $em->getRepository('IdleBundle:Inventory')->findOneBy(array('user' => $user, 'item' => $equipment->getStuff()->getItem()));
        if (!$inventory) {
            $inventory = new Inventory();
            $inventory->setUser($user);
            $inventory->setItem($equipment->getStuff()->getItem());
            $inventory->setQuantity(1);
        }
        else {
            $inventory->setQuantity($inventory->getQuantity() + 1);
        }
        $em->persist($inventory);

        $em->remove($equipment);

//        $em->flush();

        return new JsonResponse(array('success' => true, 'inventory' => ''));
    }

    /**
     * @Route("/equip-stuff/{hero_id}/{stuff_id}", name="equip_stuff")
     */
    public function ajaxEquipStuff(Request $request, $hero_id, $stuff_id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        /** @var Stuff $stuff */
        $stuff = $em->getRepository('IdleBundle:Stuff')->find($stuff_id);
        if (!$stuff) {
            return new JsonResponse(array('success' => false));
        }

        /** @var Inventory $inventory */
        $inventory = $em->getRepository('IdleBundle:Inventory')->findOneBy(array('user' => $user, 'item' => $stuff->getItem()));
        if (!$inventory) {
            return new JsonResponse(array('success' => false));
        }

        /** @var Hero $hero */
        $hero = $em->getRepository('IdleBundle:Hero')->findOneBy(array('user' => $user, 'id' => $hero_id));
        if (!$hero) {
            return new JsonResponse(array('success' => false));
        }

        /** @var Equipment $equipment */
        $equipment = $em->getRepository('IdleBundle:Equipment')->getEquipmentTypeFromHero($hero_id, $stuff->getType()->getId());
        if ($equipment && $equipment->getHero()->getUser() != $user) {
            return new JsonResponse(array('success' => false));
        }

        // Remove quantity of the stuff in the inventory
        if ($inventory->getQuantity() == 1) {
            $em->remove($inventory);
        }
        else {
            $inventory->setQuantity($inventory->getQuantity() - 1);
            $em->persist($inventory);
        }

        // Move the previous stuff to the inventory
        if ($equipment) {
            /** @var Inventory $inventory */
            $inventory = $em->getRepository('IdleBundle:Inventory')->findOneBy(array('user' => $user, 'item' => $equipment->getStuff()->getItem()));
            if (!$inventory) {
                $inventory = new Inventory();
                $inventory->setUser($user);
                $inventory->setItem($equipment->getStuff()->getItem());
                $inventory->setQuantity(1);
            }
            else {
                $inventory->setQuantity($inventory->getQuantity() + 1);
            }
            $em->persist($inventory);

            $equipment->setStuff($stuff);
        }
        else {
            $equipment = new Equipment();
            $equipment->setStuff($stuff);
            $equipment->setHero($hero);
        }
        $em->persist($equipment);

//        $em->flush();

        return new JsonResponse(array('success' => true));
    }

//    /**
//     * @Route("/switch-hero/{hero_id}", name="switch_hero")
//     */
//    public function ajaxChangeSelectedCharacter(Request $request, $hero_id)
//    {
//        $em = $this->getDoctrine()->getManager();
//
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//        $heroes = $em->getRepository('IdleBundle:Hero')->findBy(array('user' => $user));
//
//        foreach ($heroes as $hero) {
//            if ($hero->getId() == $hero_id)
//                $hero->setIsSelected(true);
//            else
//                $hero->setIsSelected(false);
//            $em->persist($hero);
//            $em->flush();
//        }
//
//        $hero = $em->getRepository('IdleBundle:Hero')->findOneBy(array('user' => $user, 'isSelected' => true));
//
//        return new JsonResponse(array('success' => true, 'selected_hero_id' => $hero->getId()));
//    }

//    /**
//     * @Route("/exp-hero/{hero_id}", name="exp_hero")
//     */
//    public function ajaxHeroEarnExp(Request $request, $hero_id)
//    {
//        $exp = $request->request->get('exp');
//
//        $em = $this->getDoctrine()->getManager();
//
//        $user = $this->get('security.token_storage')->getToken()->getUser();
//        $hero = $em->getRepository('IdleBundle:Hero')->find($hero_id);
//
//        $hero->setExperience($hero->getExperience() + $exp);
//
//        $next_tick = $em->getRepository('IdleBundle:ExperienceTable')->find($hero->getLevel())->getExperience();
//
//        if ($hero->getExperience() >= $next_tick) {
//            $hero->setExperience($hero->getExperience() - $next_tick);
//            $hero->setLevel($hero->getLevel() + 1);
//            $next_tick = $em->getRepository('IdleBundle:ExperienceTable')->find($hero->getLevel())->getExperience();
//        }
//
//        $em->persist($hero);
//        $em->flush();
//
//        return new JsonResponse(array('success' => true, 'hero_id' => $hero->getId(), 'level' => $hero->getLevel(), 'experience' => $hero->getExperience(), 'next_level' => $next_tick));
//    }
}
