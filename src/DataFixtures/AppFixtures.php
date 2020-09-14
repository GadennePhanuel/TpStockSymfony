<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Belonging;
use App\Entity\Stock;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);


        $faker = Faker\Factory::create('en_US');

        // Créer 3 stocks fakées
        for($i = 1; $i <=3; $i++){
            $stock = new Stock();
            $stock->setLabel($faker->state);

            $manager->persist($stock);

            // Créer entre 10 et 15 articles
            for($j = 1; $j <= mt_rand(10, 15); $j++){
                $article = new Article();
                $article->setLabel($faker->name);
                $article->setPrice($faker->randomFloat(2, 100, 10000));
                $article->setRef($faker->postcode);

                $manager->persist($article);


                // Créer mes appartenances
                    $belong = new Belonging();
                    $belong->setStock($stock);
                    $belong->setArticle($article);
                    $belong->setQty($faker->numberBetween(0, 100));

                    $manager->persist($belong);

            }
        }





        $manager->flush();
    }
}
