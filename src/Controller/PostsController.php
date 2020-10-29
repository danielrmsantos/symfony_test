<?php


namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/posts")
 */
class PostsController extends AbstractController
{
    /**
     * @Route("/", name="post_list", methods={"GET"})
     */
    public function list(Request $request)
    {
        $items =  $this->getDoctrine()->getRepository(Post::class)->findAll();
        
        return $this->json($items);
    }
    
    /**
     * @Route("/{id}", name="post_by_id", requirements={"id"="\d+"}, methods={"GET"})
     */
    public function postByID(Post $post)
    {
        return $this->json($post);
    }
    
    /**
     * @Route("/{channel}", name="posts_by_channel", methods={"GET"})
     */
    public function postsByChannel(Request $request)
    {
        $channel = $request->get('channel');
    
        $items =  $this->getDoctrine()->getRepository(Post::class)->findByChannel($channel);
        
        return $this->json($items);
    }
    
    /**
     * @Route("/create", name="post_create", methods={"POST"})
     */
    public function create(Request $request)
    {
        $this->isValidJson($request);
        
        /** @var \Symfony\Component\Serializer\Serializer $serializer */
        $serializer = $this->get('serializer');
        
        $post = $this->getDoctrine()->getRepository(Post::class)->upsert($request, $serializer);
        
        if (!$post instanceof Post) {
            return $this->json([
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'errors' => $post
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        return $this->json($post, Response::HTTP_CREATED);
    }
    
    /**
     * @Route("/{id}", name="update_post_by_id", requirements={"id"="\d+"}, methods={"PUT"})
     */
    public function update(Post $post, Request $request)
    {
        $this->isValidJson($request);
        
        /** @var \Symfony\Component\Serializer\Serializer $serializer */
        $serializer = $this->get('serializer');
        
        $updatedPost = $this->getDoctrine()->getRepository(Post::class)->upsert($request,$serializer, $post);
    
        if (!$updatedPost instanceof Post) {
            return $this->json([
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'errors' => $updatedPost
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        return $this->json($updatedPost);
    }
    
    /**
     * @Route("/{id}", name="post_delete", requirements={"id"="\d+"}, methods={"DELETE"})
     */
    public function delete(Post $post)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();
        
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
    
    /**
     * Validate if $request content-type is json and syntax is correct
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    private function isValidJson(Request $request)
    {
        $data = json_decode($request->getContent(), true);
    
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new HttpException(400, 'Invalid json');
        }
    
        if ('application/json' !== $request->headers->get('content-type')) {
            throw new HttpException(415, 'Invalid content-type. Accepted only application/json');
        }
    }
    
}