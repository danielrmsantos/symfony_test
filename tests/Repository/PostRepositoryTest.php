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
    
    protected function setUp()
    {
        $kernel = self::bootKernel();
        
        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }
    
    public function testSearchById()
    {
        $post = $this->entityManager
            ->getRepository(Post::class)
            ->findOneBy(['id' => 1])
        ;
        
        $this->assertSame(1, $post->getId());
    }
    
    protected function tearDown()
    {
        parent::tearDown();
        
        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}