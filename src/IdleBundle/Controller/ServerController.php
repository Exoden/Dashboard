<?php

namespace IdleBundle\Controller;

use AppBundle\Entity\User;
use IdleBundle\Entity\BattleHistory;
use IdleBundle\Entity\Craft;
use IdleBundle\Entity\Enemy;
use IdleBundle\Entity\Hero;
use IdleBundle\Entity\Inventory;
use IdleBundle\Entity\Item;
use IdleBundle\Entity\Loot;
use IdleBundle\Entity\PossessedRecipes;
use IdleBundle\Entity\Recipe;
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

    public function time_sorter($key) {
        return function ($a, $b) use ($key) {
            return $this->timecmp($a[$key], $b[$key]);
        };
    }

    /**
     * @Route("/battle-history/{hero_id}", name="battle_history")
     */
    public function ajaxBattleHistory(Request $request, $hero_id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var BattleManager $battle_manager */
        $battle_manager = $this->container->get('idle.battle_manager');

        /** @var User $user */
        $user = $this->get('security.token_storage')->getToken()->getUser();
        /** @var Hero $hero */
        $hero = $em->getRepository('IdleBundle:Hero')->findOneBy(array('id' => $hero_id, 'user' => $user));
        if (!$hero) {
            $this->addFlash('error', "Hero not found.");
            return new JsonResponse(array('success' => false));
        }

//        $battle_manager->cumulStatsHero($hero_id); // TODO : Cumul stats + skill tree

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
            echo("now : " . $now . "\n");
            echo("end : " . end($historic)['time'] . "\n");
//            if (end($historic)['time'] > $now) { // We play the past events
            $break = false;
            echo("histo_size : " . count($historic) . "\n");
            foreach ($historic as $key => $histo) {
                if ($histo['time'] > $now) { // Stop playing
//                    $battle_history = array_splice($historic, $key);
//                    $start_time = ((count($battle_history) + 1) * $hero->getCharacteristics()->getAttackDelay()); // +1 to skip the current cell, because we keep it and want to generate the following
                    echo("time : " . $histo['time'] . "\n");
                    echo("end : " . end($historic)['time'] . "\n");
                    echo("calc : " . (end($historic)['time'] - $histo['time']) . "\n");
                    $start_time = $until - (end($historic)['time'] - $histo['time']);
                    echo("start : " . $start_time . "\n");
                    $battle_history = array_splice($historic, $key);
                    $break = true;
                    break;
                }
                else { // Play one event
                    echo("play " . $histo['type'] . " : " . $histo['time'] . "\n");
                    $battle_manager->playOneAction($histo, $hero);
                }
            }
            $em->persist($hero);
            $em->flush();

            if (!$break) {
                $now = end($historic)['time']; // now generate historic from the end of the historic
                $now = $battle_manager->playAverageBattleFrom($now);
                // TODO : Generate average battle and execute until now
            }
        }

        ///////////////////////
        // Fill the historic //
        ///////////////////////
        // Enemy
        $cumul_damage = 0; // TODO : Why not doing it based on (CURRENT_ENEMY_HP - damage done) instead of decreasing (MAX_ENEMY_HP - cumul_damage) each time ?????
        $time = $start_time;
        $weapon = $hero->getTypeStuff($em->getRepository('IdleBundle:TypeStuff')->findOneBy(array('name' => "Weapon")))->getCharacteristics();
        if (!$weapon)
            $weapon = $hero->getCharacteristics();
        while ($time <= $until) {
            if ($time == 0) { // first generate an enemy
                /** @var Enemy $enemy */
                $enemy = $em->getRepository('IdleBundle:Enemy')->find(1); // TODO : Randomise
                $hero->getTarget()->setCurrentHealth($enemy->getCharacteristics()->getHealth());
                $hero->getTarget()->setEnemy($enemy);
                $em->flush();

                $battle_history[] = array( // TODO : Get image
                    'type' => "GEN",
                    'time' => $now + $time,
                    'enemy' => $enemy->getId(),
                    'image' => $this->container->get('templating.helper.assets')->getUrl('images/Idle/Enemy/' . $hero->getTarget()->getEnemy()->getImage()),
                    'currentHealth' => $hero->getTarget()->getCurrentHealth(),
                    'health' => $hero->getTarget()->getEnemy()->getCharacteristics()->getHealth(),
                    'characteristics' => array()); // TODO : Send Characs
            }
            else {
                $damage = rand($weapon->getDamageMinimum(), $weapon->getDamageMaximum());
                $cumul_damage += $damage;

                $battle_history[] = array(
                    'type' => "HIT_E",
                    'time' => $now + $time,
                    'damage' => $damage,
                    'currentHealth' => $hero->getTarget()->getCurrentHealth() - $cumul_damage,
                    'health' => $hero->getTarget()->getEnemy()->getCharacteristics()->getHealth());

                if (end($battle_history)['currentHealth'] <= 0) {
                    $flash_msg = "";
                    $loots = $hero->getTarget()->getEnemy()->getLoots();
                    $arr_loot = array();
                    /** @var Loot $loot */
                    foreach ($loots as $loot) {
                        $rate = rand(1, 100000); // Precision 0.001
                        if ($rate < ($loot->getPercent() * 1000)) {
                            array_push($arr_loot, $loot->getItem()->getId());

                            $flash_msg .= "+1 " . $loot->getItem()->getName() . "\n";
                        }
                    }

                    $cumul_damage = 0;
                    $time += 1;
                    /** @var Enemy $enemy */
                    $enemy = $em->getRepository('IdleBundle:Enemy')->find(1); // TODO : Randomise, based on field level and type
                    $hero->getTarget()->setCurrentHealth($enemy->getCharacteristics()->getHealth());
                    $hero->getTarget()->setEnemy($enemy);
                    $em->flush();

                    $battle_history[] = array(
                        'type' => "GEN",
                        'time' => $now + $time,
                        'enemy' => $enemy->getId(),
                        'loot_msg' => (($flash_msg != "") ? $flash_msg : "No loots"),
                        'loots' => $arr_loot,
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

        usort($battle_history, $this->time_sorter('time'));

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

        $crafts = $possessed_recipe->getRecipe()->getCrafts();

        $craftable = true;
        $tab_crafts = array();
        $i = 0;
        /** @var Craft $craft */
        foreach ($crafts as $craft) {
            $tab_crafts[$i]['id'] = $craft->getItemNeeded()->getId();
            $tab_crafts[$i]['name'] = $craft->getItemNeeded()->getName();
            $tab_crafts[$i]['image'] = $this->container->get('templating.helper.assets')->getUrl('images/Idle/' . $craft->getItemNeeded()->getTypeItem()->getName() . '/' . $craft->getItemNeeded()->getImage());
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

        $craftable = true;
        $tab_items = array();
        $i = 0;
        $crafts = $possessed_recipe->getRecipe()->getCrafts();
        /** @var Craft $craft */
        foreach ($crafts as $craft) {
            /** @var Inventory $inv */
            $inv = $em->getRepository('IdleBundle:Inventory')->findOneBy(array('user' => $user, 'item' => $craft->getItemNeeded()));

            if ($inv->getQuantity() < $craft->getQuantity()) {
                return new JsonResponse(array('success' => false));
            }
            else {
                if ($inv->getQuantity() < ($craft->getQuantity() * 2))
                    $craftable = false;

                $inv->setQuantity($inv->getQuantity() - $craft->getQuantity());

                // Used item
                $tab_items[$i]['id'] = $inv->getItem()->getId();
                $tab_items[$i]['name'] = $inv->getItem()->getName();
                $tab_items[$i]['image'] = $this->container->get('templating.helper.assets')->getUrl('images/Idle/' . $inv->getItem()->getTypeItem()->getName() . '/' . $inv->getItem()->getImage());
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
        $tab_items[$i]['image'] = $this->container->get('templating.helper.assets')->getUrl('images/Idle/' . $inventory->getItem()->getTypeItem()->getName() . '/' . $inventory->getItem()->getImage());
        $tab_items[$i]['possessed'] = $inventory->getQuantity();

        $em->flush();

        return new JsonResponse(array('success' => true, 'items' => $tab_items, 'craftable' => $craftable));
    }
    /**
     * @Route("/learn-recipe/{item_id}", name="learn_recipe")
     */
    public function ajaxLearnRecipe(Request $request, $item_id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        /** @var Recipe $recipe */
        $recipe = $em->getRepository('IdleBundle:Recipe')->findOneBy(array('item' => $item_id));
        if (!$recipe) {
            return new JsonResponse(array('success' => false));
        }

        /** @var Inventory $inv */
        $inv = $em->getRepository('IdleBundle:Inventory')->findOneBy(array('user' => $user, 'item' => $recipe->getItem()));
        if (!$inv) {
            return new JsonResponse(array('success' => false));
        }

        /** @var PossessedRecipes $possessed_recipe */
        $possessed_recipe = $em->getRepository('IdleBundle:PossessedRecipes')->findOneBy(array('user' => $user, 'recipe' => $recipe));
        if ($possessed_recipe) { // already has it
            return new JsonResponse(array('success' => false));
        }
        else {
            $possessed_recipe = new PossessedRecipes();
            $possessed_recipe->setUser($user);
            $possessed_recipe->setRecipe($recipe);
            $em->persist($possessed_recipe);
            $em->flush();
        }

        $inv->setQuantity($inv->getQuantity() - 1);
        if ($inv->getQuantity() == 0) {
            $em->remove($inv);
        }
        $em->flush();

        $tab_recipe = array();
        $tab_recipe['id'] = $recipe->getId();
        $tab_recipe['name'] = $recipe->getItemCreated()->getName();
        $tab_recipe['quantity'] = $inv->getQuantity();

        return new JsonResponse(array('success' => true, 'recipe' => $tab_recipe));
    }

    /**
     * @Route("/show-equipment-stats/{hero_id}/{type_name}", name="show_equipment_stats")
     */
    public function ajaxShowEquipmentStats(Request $request, $hero_id, $type_name)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        /** @var Hero $hero */
        $hero = $em->getRepository('IdleBundle:Hero')->findOneBy(array('user' => $user, 'id' => $hero_id));
        if (!$hero) {
            return new JsonResponse(array('success' => false));
        }

        /** @var TypeStuff $type */
        $type = $em->getRepository('IdleBundle:TypeStuff')->findOneBy(array('name' => $type_name));

        $equipment = $hero->getTypeStuff($type);
        if (!$equipment && $type->getName() == "Weapon") {
            /** @var Hero $hero */
            $hero = $em->getRepository('IdleBundle:Hero')->findOneBy(array('user' => $user, 'id' => $hero_id));
            if (!$hero) {
                return new JsonResponse(array('success' => false));
            }
            $stats = $em->getRepository('IdleBundle:Characteristics')->getStatsInArray($hero->getCharacteristics())[0];
            unset($stats['health']);
        }
        else if (!$equipment) {
            return new JsonResponse(array('success' => true, 'stats' => array()));
        }
        else {
            $stats = $em->getRepository('IdleBundle:Characteristics')->getStatsInArray($equipment->getCharacteristics())[0];
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

        /** @var Hero $hero */
        $hero = $em->getRepository('IdleBundle:Hero')->findOneBy(array('user' => $user, 'id' => $hero_id));
        if (!$hero) {
            return new JsonResponse(array('success' => false));
        }

        /** @var TypeStuff $type */
        $type = $em->getRepository('IdleBundle:TypeStuff')->findOneBy(array('name' => $type_name));

        $equipment = $hero->getTypeStuff($type);
        if (!$equipment) {
            return new JsonResponse(array('success' => false));
        }

        /** @var Inventory $inventory */
        $inventory = $em->getRepository('IdleBundle:Inventory')->findOneBy(array('user' => $user, 'item' => $equipment->getItem()));
        if (!$inventory) {
            $inventory = new Inventory();
            $inventory->setUser($user);
            $inventory->setItem($equipment->getItem());
            $inventory->setQuantity(1);
        }
        else {
            $inventory->setQuantity($inventory->getQuantity() + 1);
        }

        $arr_inv = array(
            'id' => $equipment->getId(),
            'name' => $inventory->getItem()->getName(),
            'type' => $equipment->getType()->getName(),
            'quantity' => $inventory->getQuantity(),
            'image' => $this->container->get('templating.helper.assets')->getUrl('images/Idle/' . $inventory->getItem()->getTypeItem()->getName() . '/' . $inventory->getItem()->getImage()));

        $em->persist($inventory);
        $hero->removeStuff($equipment);
        $em->flush();


        return new JsonResponse(array('success' => true, 'inventory' => $arr_inv));
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

        $equipment = $hero->getTypeStuff($stuff->getType());
        if ($equipment && $hero->getUser() != $user) {
            return new JsonResponse(array('success' => false));
        }

        $arr_inv = array();

        // Move the previous stuff to the inventory
        if ($equipment) {
            $hero->removeStuff($equipment); // Remove the previous equipment
            /** @var Inventory $inventory */
            $inventory = $em->getRepository('IdleBundle:Inventory')->findOneBy(array('user' => $user, 'item' => $equipment->getItem()));
            if (!$inventory) { // Add and Create previous equipment to inventory
                $inventory = new Inventory();
                $inventory->setUser($user);
                $inventory->setItem($equipment->getItem());
                $inventory->setQuantity(1);
                array_push($arr_inv, array(
                    'msg' => "new",
                    'id' => $equipment->getId(),
                    'name' => $inventory->getItem()->getName(),
                    'type' => $equipment->getType()->getName(),
                    'quantity' => $inventory->getQuantity(),
                    'image' => $this->container->get('templating.helper.assets')->getUrl('images/Idle/' . $inventory->getItem()->getTypeItem()->getName() . '/' . $inventory->getItem()->getImage())));
            }
            else { // Add +1 previous equipment to inventory
                $inventory->setQuantity($inventory->getQuantity() + 1);
                array_push($arr_inv, array(
                    'msg' => "edit",
                    'id' => $equipment->getId(),
                    'quantity' => $inventory->getQuantity()));
            }
            $em->persist($inventory);
        }

        // Remove quantity of the stuff in the inventory
        if ($inventory->getQuantity() == 1) {
            $em->remove($inventory);
            array_push($arr_inv, array(
                'msg' => "edit",
                'id' => $stuff->getId(),
                'quantity' => 0));
        }
        else {
            $inventory->setQuantity($inventory->getQuantity() - 1);
            $em->persist($inventory);
            array_push($arr_inv, array(
                'msg' => "edit",
                'id' => $stuff->getId(),
                'quantity' => $inventory->getQuantity()));
        }

        $hero->addStuff($stuff); // Equip the stuff
        $em->persist($hero);
        $em->flush();

        return new JsonResponse(array('success' => true, 'inventory' => $arr_inv));
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
