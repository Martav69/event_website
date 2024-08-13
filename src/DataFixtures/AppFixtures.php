<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Country;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;




class AppFixtures extends Fixture
{

    private const CATEGORIES_NAMES = ['Football', 'Basket', 'Tennis','Rugby' ,'MMA','Arts Martiaux','Athlétisme','Natation','Course', 'Volleyball'];

    public function load(ObjectManager $manager): void
    {
        // load faker pour l'utiliser en FR
        $faker = Faker\Factory::create('fr_FR');
       


        // Populate les category
        $categories = [];

        for ($i=0; $i < count(self::CATEGORIES_NAMES) ; $i++) { 
            $category = new Category();
            $category->setName(self::CATEGORIES_NAMES[$i]);

            $manager->persist($category);
            $categories[$i] = $category;
        }

        // Populate des pays en utilisant umpirsky package
       // Chemin vers le fichier JSON 
       $filePath = __DIR__ . '/../../vendor/umpirsky/country-list/data/fr/country.json';
       $countries = json_decode(file_get_contents($filePath), true);

       foreach ($countries as $countryCode => $countryName) {
           $country = new Country();
           $country->setName($countryName);
           $manager->persist($country);
       }



        $manager->flush();
    }
}
