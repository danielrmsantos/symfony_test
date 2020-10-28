<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    private $manager;
    
    private $validator;
    
    public function __construct(ManagerRegistry $registry , EntityManagerInterface $manager, ValidatorInterface $validator)
    {
        parent::__construct($registry, Post::class);
        $this->manager = $manager;
        $this->validator = $validator;
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
            ->getQuery()
            ->getResult()
        ;
    }
    
    public function upsert(Request $request, Serializer $serializer,$post = null)
    {
        $em = $this->manager;
        
        if (null !== $post) {
            $serializer->deserialize($request->getContent(), Post::class, 'json',  ['object_to_populate' => $post]);
            $errors = $this->validator->validate($post, null, ['update']);
        } else {
            $post = $serializer->deserialize($request->getContent(), Post::class, 'json');
            $errors = $this->validator->validate($post, null, ['create']);
        }
        
        if (count($errors) > 0) {
            $errorMessages = [];
            /** @var ConstraintViolationList $error */
            foreach ($errors as $error) {
                $errorMessages[] = [
                    'property' => $error->getPropertyPath(),
                    'message' => $error->getMessage(),
                ];
            }
            
            return $errorMessages;
        }
        
        $em->persist($post);
        $em->flush();
        
        return $post;
    }
}
