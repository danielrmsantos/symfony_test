<?php

namespace App\Tests\Repository;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PostRepositoryTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * @var int
     */
    private $defaultPostId;
    
    
    /**
     * @var string
     */
    private $defaultPostChannel;
    
    protected function setUp()
    {
        $kernel = self::bootKernel();
        
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->defaultPostId = 1;
        $this->defaultPostChannel = 'website';
    }
    
    /**
     * Find a Post by id
     */
    public function testFindById(): void
    {
        $post = $this->entityManager->getRepository(Post::class)->findOneBy(['id' => $this->defaultPostId]);
        
        self::assertSame($this->defaultPostId, $post->getId());
        self::assertInstanceOf(Post::class, $post);
        $this->validatePost($post);
    }
    
    /**
     * Find a Post by channel
     */
    public function testFindByChannel()
    {
        $posts = $this->entityManager->getRepository(Post::class)->findByChannel($this->defaultPostChannel);
    
        self::assertNotEmpty($posts, 'No Posts found on given channel: ' . $this->defaultPostChannel);
    
        foreach ($posts as $post) {
            $this->validatePost($post);
        }
    }
    
    /**
     * Validate that Post has expected attributes and values
     *
     * @param Post $post
     */
    private function validatePost(Post $post): void
    {
        self::assertObjectHasAttribute('id', $post);
        self::assertObjectHasAttribute('title', $post);
        self::assertObjectHasAttribute('description', $post);
        self::assertObjectHasAttribute('channel', $post);
        self::assertIsInt($post->getId(), 'Got a ' . gettype($post->getId()) . ' instead of integer');
        self::assertIsString($post->getTitle(), 'Got a ' . gettype($post->getTitle()) . ' instead of string');
        self::assertIsString($post->getDescription(), 'Got a ' . gettype($post->getDescription()) . ' instead of string');
        self::assertIsString($post->getChannel(), 'Got a ' . gettype($post->getChannel()) . ' instead of string');
    }
    
    protected function tearDown()
    {
        parent::tearDown();
        
        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}