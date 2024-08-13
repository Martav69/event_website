<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;


class AppFixtures extends Fixture
{

    private const CATEGORIES_NAMES = ['Football', 'Basket', 'Tennis','Rugby' ,'MMA','Arts Martiaux','Athlétisme','Natation','Course', 'Volleyball'];

    public function load(ObjectManager $manager): void
    {

        $faker = Faker\Factory::create('fr_FR');
       

        $categories = [];

        for ($i=0; $i < count(self::CATEGORIES_NAMES) ; $i++) { 
            $category = new Category();
            $category->setName(self::CATEGORIES_NAMES[$i]);

            $manager->persist($category);
            $categories[$i] = $category;
        }

        $manager->flush();
    }
}
