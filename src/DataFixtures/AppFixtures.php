<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\City;
use App\Entity\Country;
use App\Entity\Event;
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
        $pays = [];

       foreach ($countries as $countryCode => $countryName) {
           $country = new Country();
           $country->setName($countryName);
           $manager->persist($country);

           $pays[] = $country;
       }

       // Populate des villes via faker 
       $villes = [];
       for ($i=0; $i < 300 ; $i++) { 
        $ville = new City();
        $ville->setName($faker->city);
        $ville->setCountry($faker->randomElement($pays));
        $manager->persist($ville);

        $villes[$i] = $ville;
       }

       // Populate des events
       for ($i=0; $i < 10 ; $i++) { 

         $event = new Event();
         $event->setTitle($faker->catchPhrase);
         $event->setDescription($faker->paragraph(3,true));
         $event->setPicFileName($faker->imageUrl(640,480,'sports',true,'event'));

         // gestion des date avec clone modifié pour avoir une date de fin supérieur a la date de début
         $startDate = $faker->dateTimeBetween('-1 month', '+1 month');
         $event->setStartDate($startDate);
         $endDate = (clone $startDate)->modify('+1 month');
         $event->setEndDate($faker->dateTimeBetween($startDate, $endDate));

         $event->setPrice($faker->randomFloat(2,20,200));

         $totalSeats = $faker->numberBetween(10,300);
         $event->setTotalSeats($totalSeats);

         $event->setAvailableSeats($faker->numberBetween(0, $totalSeats));

         $event->setCategory($faker->randomElement($categories));
         $event->addCity($faker->randomElement($villes));

         $manager->persist($event);

       }



        $manager->flush();
    }
}
