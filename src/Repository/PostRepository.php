<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    private $manager;
    
    public function __construct(ManagerRegistry $registry , EntityManagerInterface $manager)
    {
        parent::__construct($registry, Post::class);
        $this->manager = $manager;
    }

    /**
    * @return Post[] Returns an array of Post objects
    */
    public function findByChannel($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.channel = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            //->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    

    /*
    public function findOneBySomeField($value): ?Post
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    
    public function upsert(Request $request, Serializer $serializer,$post = null)
    {
        
        $em = $this->manager;
        
        if (null !== $post) {
            $data = $serializer->deserialize($request->getContent(), Post::class, 'json');
            /** @var Post $post */
            $post->setTitle($data->getTitle());
            $post->setDescription($data->getDescription());
            $post->setChannel($data->getChannel());
        } else {
            $post = $serializer->deserialize($request->getContent(), Post::class, 'json');
        }
        
        
        $em->persist($post);
        $em->flush();
        
        return $post;
    }
}
