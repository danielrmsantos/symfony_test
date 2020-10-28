<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Post;

class AppFixtures extends Fixture
{
    /**
     * @var \Faker\Factory
     */
    private $faker;
    
    private const CHANNELS = [
        'mobile',
        'website'
    ];
    
    public function __construct()
    {
        $this->faker = \Faker\Factory::create();
    }
    
    public function load(ObjectManager $manager)
    {
        $this->loadPosts($manager);
    }
    
    public function loadPosts(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            $post = new Post();
            $post->setTitle($this->faker->realText(30));
            $post->setDescription($this->faker->realText());
            $post->setChannel($this->getRandomChannel());
            $manager->persist($post);
        }
        
        $manager->flush();
    }
    
    /**
     * @return string
     * @throws \Exception
     */
    protected function getRandomChannel(): string
    {
        return self::CHANNELS[random_int(0, 1)];
    }
}
