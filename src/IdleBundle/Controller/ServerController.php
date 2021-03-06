<?php

namespace IdleBundle\Controller;

use AppBundle\Entity\User;
use IdleBundle\Entity\BattleHistory;
use IdleBundle\Entity\Characteristics;
use IdleBundle\Entity\Craft;
use IdleBundle\Entity\Enemy;
use IdleBundle\Entity\Food;
use IdleBundle\Entity\FoodStack;
use IdleBundle\Entity\Hero;
use IdleBundle\Entity\Inventory;
use IdleBundle\Entity\Item;
use IdleBundle\Entity\Loot;
use IdleBundle\Entity\PossessedRecipes;
use IdleBundle\Entity\Recipe;
use IdleBundle\Entity\Stuff;
use IdleBundle\Entity\Target;
use IdleBundle\Entity\TypeStuff;
use IdleBundle\Entity\Zone;
use IdleBundle\Form\FoodStackListType;
use IdleBundle\Form\FoodStackType;
use IdleBundle\Form\HeroType;
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

    public function time_sorter($key)
    {
        return function ($a, $b) use ($key) {
            return $this->timecmp($a[$key], $b[$key]);
        };
    }

    public function latest_historic_type($arr, $type, $stat = '')
    {
        if (!$arr)
            return 0;
        end($arr);
        while (!is_null($key = key($arr))) {
            $val = current($arr);
            if ($val['type'] == $type) {
                if ($type == 'GEN' && $stat != '')
                    return $val['stats'][$stat];
                else
                    return $val['time'];
            }

            prev($arr);
        }

        return 0;
    }

    public function erase_from_latest_type_time($arr, $type, $time)
    {
        if (!$arr)
            return $arr;

        end($arr);
        $i = 0;
        while ($i < count($arr)) {
            if ($arr[$i]['type'] == $type && $arr[$i]['time'] == $time) {
                return array_splice($arr, $i);
            }

            $i++;
        }

        return $arr;
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
        $until = (60 * 0.5); // 5 minutes
        $last_time_hit_e = $this->latest_historic_type($historic, "HIT_E");
        $last_time_hit_h = $this->latest_historic_type($historic, "HIT_H");
        $last_time_gen = $this->latest_historic_type($historic, "GEN");
        $enemy_attack_delay = $this->latest_historic_type($historic, "GEN", 'attackDelay');
        if ($enemy_attack_delay == 0)
            $enemy_attack_delay = $hero->getTarget()->getEnemy()->getCharacteristics()->getAttackDelay();
        $last_time_histo = ($historic) ? end($historic)['time'] : 0;

        // Get a piece of the previous historic
        if ($historic) {
//            echo("now : " . $now . "\n");
//            echo("end : " . end($historic)['time'] . "\n");
            $break = false;
//            echo("histo_size : " . count($historic) . "\n");
            foreach ($historic as $key => $histo) {
                if ($histo['time'] > $now) { // Stop playing
//                    echo("time : " . $histo['time'] . "\n");
//                    echo("end : " . end($historic)['time'] . "\n");
//                    echo("calc : " . (end($historic)['time'] - $histo['time']) . "\n");
                    $start_time = (end($historic)['time'] - $historic[$key]['time']); // TODO : Check time, after refreshing, generating not enough lines !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//                    echo("start : " . $start_time . "\n");
//                    echo("until : " . $until . "\n");
                    $battle_history = array_splice($historic, $key);
                    $break = true;
                    break;
                }
                else { // Play one event
//                    echo("play " . $histo['type'] . " : " . $histo['time'] . "\n");
                    $battle_manager->playOneAction($histo, $hero);
                }
            }
            $em->persist($hero);
            $em->flush();

            if (!$break) {
                $now = end($historic)['time']; // now generate historic from the end of the historic
                $now = $battle_manager->playAverageBattleFrom($now); // TODO : Generate average battle and execute until now
            }
        }


        ///////////////////////////
        // Prepare the variables //
        ///////////////////////////

        $enemy_id = $hero->getTarget()->getEnemy()->getId();
        $enemy_current_life = $hero->getTarget()->getCurrentHealth();
        $enemy_max_life = $hero->getTarget()->getEnemy()->getCharacteristics()->getHealth();
        $weapon = $hero->getTypeStuff($em->getRepository('IdleBundle:TypeStuff')->findOneBy(array('name' => "Weapon")))->getCharacteristics();
        if (!$weapon)
            $weapon = $hero->getCharacteristics();
        $hero_current_life = $hero->getCurrentHealth();
        $enemy_damage_min = $hero->getTarget()->getEnemy()->getCharacteristics()->getDamageMinimum();
        $enemy_damage_max = $hero->getTarget()->getEnemy()->getCharacteristics()->getDamageMaximum();

        $time = $start_time + ($weapon->getAttackDelay() - (($last_time_hit_e > 0 || $last_time_gen > 0) ? ($last_time_histo - (($last_time_hit_e < $last_time_gen) ? $last_time_gen : $last_time_hit_e)) : 0));

        ///////////////////////
        // Fill the historic //
        ///////////////////////

        $arr_time_change_enemy = array();
        // If current target is dead, generate a new one
        if ($start_time == 0 && $enemy_current_life <= 0) { // first generate an enemy
            $battle_history[] = $battle_manager->createGenAction($hero, $now + $time);
            $enemy_current_life = end($battle_history)['currentHealth'];
            $enemy_max_life = end($battle_history)['health'];
            $enemy_id = end($battle_history)['enemy'];
            array_push($arr_time_change_enemy, array( // TODO : If last histo was GEN/not died enemy, then don't do it ??
                'time' => $now + $time,
                'stats' => $em->getRepository('IdleBundle:Characteristics')->getStatsInArray($hero->getTarget()->getEnemy()->getCharacteristics())[0]));
        }

        // If hero is still resting, make the regen come up
        if (!$hero->getIsRested()) {
            $battle_history = $battle_manager->autoRegenHero($hero, $hero_current_life, $now, $time, $battle_history);
            $time = end($battle_history)['time'] - $now; // Extract the $time
            $start_time = $time;
//            echo("after regen time : " . end($battle_history)['time'] . "\n");
        }

//        echo("starting loop 1 time : " . $time . " : " . ($time + $now) . "\n");
//        echo("while : " . $time . " <= " . $until . "\n");
        // Hero attacks Enemy
        while ($time <= $until) {
            // TODO : If not dodge
            $damage = rand($weapon->getDamageMinimum(), $weapon->getDamageMaximum()); // TODO : Minus enemy armor
            $enemy_current_life -= $damage;
            if ($enemy_current_life < 0)
                $enemy_current_life = 0;


//            echo("enemy hit at : " . ($now + $time) . "\n");
            $battle_history[] = array(
                'type' => "HIT_E",
                'time' => $now + $time,
                'damage' => $damage,
                'currentHealth' => $enemy_current_life,
                'health' => $enemy_max_life);

            if ($enemy_current_life <= 0) {
//                echo("enemy dies" . "\n");
                $time += 1; // Reload life bar

//                echo("enemy gen at : " . ($now + $time) . "\n");
                $battle_history[] = $battle_manager->createGenAction($hero, $now + $time, $enemy_id, true);
                $enemy_current_life = end($battle_history)['currentHealth'];
                $enemy_max_life = end($battle_history)['health'];
                $enemy_id = end($battle_history)['enemy'];
                array_push($arr_time_change_enemy, array(
                    'time' => $now + $time,
                    'stats' => $em->getRepository('IdleBundle:Characteristics')->getStatsInArray($em->getRepository('IdleBundle:Enemy')->find($enemy_id)->getCharacteristics())[0]));
            }

            $time += $weapon->getAttackDelay();
        }

        // Enemy attacks Hero
        $time = $start_time + ($enemy_attack_delay - (($last_time_hit_h > 0 || $last_time_gen > 0) ? ($last_time_histo - (($last_time_hit_h < $last_time_gen) ? $last_time_gen : $last_time_hit_h)) : 0));
//        echo("starting loop 2 time : " . $time . " : " . ($time + $now) . "\n");
        $i = 0;
//        echo("while : " . $time . " <= " . $until . "\n");
        while ($time <= $until) {
            $damage = rand($enemy_damage_min, $enemy_damage_max);
            $hero_current_life -= $damage;
            if ($hero_current_life < 0)
                $hero_current_life = 0;

            $battle_history[] = array(
                'type' => "HIT_H",
                'time' => $now + $time,
                'damage' => $damage,
                'currentHealth' => $hero_current_life,
                'health' => $hero->getCharacteristics()->getHealth());
//            echo("hero hit at : " . ($now + $time) . "\n");

            if ($hero_current_life == 0) {
                $time += 1;

                $food_stack = $hero->getfoodStackList();
                if ($food_stack && count($food_stack) > 0) {
                    /** @var FoodStack $first_food */
                    $first_food = $food_stack[0];
                    if ($first_food->getQuantity() > 0) {
                        /** @var Food $food */
                        $food = $em->getRepository('IdleBundle:Item')->getItemParentClass($first_food->getItem());

                        $hero_current_life += $food->getHealthRegen();
                        $battle_history[] = array(
                            'type' => "FOOD",
                            'time' => $now + $time,
                            'item_id' => $first_food->getItem()->getId(),
                            'heal' => $food->getHealthRegen(),
                            'currentHealth' => ($hero_current_life > $hero->getCharacteristics()->getHealth()) ? $hero->getCharacteristics()->getHealth() : $hero_current_life,
                            'health' => $hero->getCharacteristics()->getHealth());
                    }
                }
                else {
                    $battle_history[] = array(
                        'type' => "STA",
                        'time' => $now + $time,
                        'state' => 'rest');

                    usort($battle_history, $this->time_sorter('time'));

                    $last_time_hit_e = $this->latest_historic_type($historic, "HIT_E");
                    $last_time_gen = $this->latest_historic_type($historic, "GEN");
                    if ($last_time_hit_e > $last_time_gen)
                        $battle_history = $this->erase_from_latest_type_time($battle_history, "HIT_E", $last_time_hit_e);
                    else
                        $battle_history = $this->erase_from_latest_type_time($battle_history, "GEN", $last_time_gen);

                    $battle_history = $battle_manager->autoRegenHero($hero, $hero_current_life, $now, $time, $battle_history);
                    $time = end($battle_history)['time'] - $now; // Extract the $time
                }
            }

            if ((count($arr_time_change_enemy) > $i) && (($now + $time + $enemy_attack_delay) > $arr_time_change_enemy[$i]['time'])) { // enemy dies before his next attack
//                echo("enemy dies at : " . $arr_time_change_enemy[$i]['time'] . "\n");
                $time = $arr_time_change_enemy[$i]['time']; // Time GEN enemy
//                echo("enemy gen at : " . $time . "\n");

                $enemy_damage_min = $arr_time_change_enemy[$i]['stats']['damageMinimum'];
                $enemy_damage_max = $arr_time_change_enemy[$i]['stats']['damageMaximum'];
                $enemy_attack_delay = $arr_time_change_enemy[$i]['stats']['attackDelay'];

                $i++;
            }

            $time += $enemy_attack_delay;
        }

        usort($battle_history, $this->time_sorter('time'));

        $last_battle->setHistoric(json_encode($battle_history));
        $em->persist($last_battle);
        $em->flush();

//        echo 'now : ' . microtime(true) . "\n"; // TODO : now is previous to battle_history, must be posterior
//        echo 'first : ' . $battle_history[0]['time'] . "\n";
//        echo 'seconde : ' . $battle_history[1]['time'] . "\n";

        return new JsonResponse(array('success' => true, 'battle_history' => $battle_history));
    }

    /**
     * @Route("/create-hero", name="create_hero")
     */
    public function ajaxCreateHero(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $this->getUser();

        $hero = new Hero();

        $form = $this->createForm(HeroType::class, $hero);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Characteristics $charac */
            $charac = new Characteristics();
            $charac->setArmor(0);
            $charac->setAttackDelay(2.5);
            $charac->setDamageMinimum(1);
            $charac->setDamageMaximum(1);
            $charac->setDodge(0);
            $charac->setHealth(100);
            $charac->setHitPrecision(100);
            $charac->setCriticalChance(0);
            $charac->setBlocking(0);
            $em->persist($charac);

            /** @var Enemy $enemy */
            $enemy = $em->getRepository('IdleBundle:Enemy')->find(1);
            $target = new Target();
            $target->setCurrentHealth($enemy->getCharacteristics()->getHealth());
            $target->setEnemy($enemy);
            $em->persist($target);

            $hero->setCharacteristics($charac);
            $hero->setAge(15);
            $hero->setCurrentHealth(100);
            $hero->setIsRested(true);
            $hero->setRestStartTime(null);
            $hero->setRestEndTime(null);
            $hero->setUser($user);
            $hero->setTarget($target);
            $em->persist($hero);

            $zone = new Zone();
            $zone->setHero($hero);
            $zone->setArea($em->getRepository('IdleBundle:Area')->find(1));
            $zone->setActivated(true);
            $zone->setCurrentField(1);
            $zone->setMaxField(1);
            $em->persist($zone);

            $em->flush();

            return $this->redirectToRoute('homepage_idle'); // TODO : Is it reloading the page ?
        }
        return $this->render('IdleBundle:Modal:create_hero.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("/manage-food/{hero_id}", name="manage_food")
     */
    public function ajaxManageFood(Request $request, $hero_id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $this->getUser();

        /** @var Hero $hero */
        $hero = $em->getRepository('IdleBundle:Hero')->findOneBy(array('id' => $hero_id, 'user' => $user));
        if (!$hero)
            return false;

        // Get all Food items in Inventory
        $inv_food = $em->getRepository('IdleBundle:Inventory')->getFood($user);

        // Display in form the FoodStack already used and the Food items not used at 0 value
        /** @var Inventory $elem */
        foreach ($inv_food as $elem) {
            $foodStack = $em->getRepository('IdleBundle:FoodStack')->findOneBy(array('hero' => $hero, 'item' => $elem->getItem()));
            if (!$hero->getfoodStackList()->contains($foodStack)) {
                $food_stack = new FoodStack();
                $food_stack->setItem($elem->getItem());
                $food_stack->setQuantity(0);

                $hero->addfoodStackList($food_stack);
            }
        }

        $food_arr = array();
        /** @var FoodStack $item */
        foreach ($hero->getfoodStackList() as $foodStack) {
            $food_arr[$foodStack->getItem()->getId()]['food'] = $em->getRepository('IdleBundle:Item')->getItemParentClass($foodStack->getItem());
            $inv_item = $em->getRepository('IdleBundle:Inventory')->findOneBy(array('user' => $user, 'item' => $foodStack->getItem()));
            $food_arr[$foodStack->getItem()->getId()]['quantity'] = ($inv_item != null) ? $inv_item->getQuantity() : 0;
        }

        $form = $this->createForm(FoodStackListType::class, $hero);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Hero $data */
            $data = $form->getData();
//            dump($data->getfoodStackList());
//            dump($hero->getfoodStackList());
//            dump($request->request->get('initial_quantity'));

            /** @var FoodStack $foodStack */
            foreach ($data->getfoodStackList() as $key => $foodStack) {
                if ($foodStack->getQuantity() <= 0) {
                    $hero->removefoodStackList($foodStack);
                }

                /** @var Inventory $inv */
                $inv = $em->getRepository('IdleBundle:Inventory')->findOneBy(array('user' => $user, 'item' => $foodStack->getItem()));
                if (!$inv) {
                    $inv = new Inventory();
                    $inv->setItem($foodStack->getItem());
                    $inv->setUser($user);
                    $inv->setQuantity(0);
                }
                $inv->setQuantity($inv->getQuantity() + ($request->request->get('initial_quantity')[$key] - $foodStack->getQuantity()));
                $em->persist($inv);

                if ($inv->getQuantity() == 0) {
                    $em->remove($inv);
                    $em->flush();
                }
            }

            $em->persist($hero);

            $em->flush();

            return $this->redirectToRoute('homepage_idle'); // TODO : Vider le battle_historic et recalculer
        }
        return $this->render('IdleBundle:Modal:manage_food.html.twig', array('form' => $form->createView(), 'hero' => $hero, 'food_arr' => $food_arr));
    }

    /**
     * @Route("/hero-skill-tree/{hero_id}", name="hero_skill_tree")
     */
    public function ajaxHeroSkillTree(Request $request, $hero_id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var User $user */
        $user = $this->getUser();

        $hero = $em->getRepository('IdleBundle:Hero')->findOneBy(array('id' => $hero_id, 'user' => $user));
        if (!$hero)
            return false;

        // TODO : Make skill tree form
//        $form = $this->createForm(HeroType::class, $hero);
//
//        $form->handleRequest($request);
//        if ($form->isSubmitted() && $form->isValid()) {
//            $em->persist($hero);
//
//            $em->flush();
//
//            return $this->redirect('homepage_idle');
//        }
//        return new JsonResponse(array('success' => true, $this->render('IdleBundle:Modal:skill_tree.html.twig', array('form' => $form->createView(), 'hero' => $hero))));
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
            if ($inv)
                $tab_crafts[$i]['possessed'] = $inv->getQuantity();
            else
                $tab_crafts[$i]['possessed'] = 0;
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
