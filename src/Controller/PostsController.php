<?php


namespace App\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

/**
 * @Route("/posts")
 */
class PostsController extends AbstractController
{
    /**
     * List all Posts.
     *
     * @Route("/", name="post_list", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns an array with all Posts",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Post::class))
     *     )
     * )
     */
    public function list(Request $request)
    {
        $items =  $this->getDoctrine()->getRepository(Post::class)->findAll();
        
        return $this->json($items);
    }
    
    /**
     * Gets a Post by Id
     *
     * @Route("/{id}", name="post_by_id", requirements={"id"="\d+"}, methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns one Post",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Post::class))
     *     )
     * )
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="The Id of the Post to find",
     *     @OA\Schema(type="integer")
     * )
     */
    public function postByID(Post $post)
    {
        return $this->json($post);
    }
    
    /**
     * Gets Posts by channel
     *
     * @Route("/{channel}", name="posts_by_channel", methods={"GET"})
     * @OA\Response(
     *     response=200,
     *     description="Returns an array of Posts on the given channel",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Post::class))
     *     )
     * )
     * @OA\Parameter(
     *     name="channel",
     *     in="path",
     *     description="The channel name of the Post to find",
     *     required=true,
     *     @OA\Schema(
     *         type="array",
     *         @OA\Items(
     *             type="string",
     *             enum={"website", "mobile"},
     *              default="website"
     *          ),
     *     ),
     *     style="form"
     * )
     */
    public function postsByChannel(Request $request)
    {
        $channel = $request->get('channel');
    
        $items =  $this->getDoctrine()->getRepository(Post::class)->findByChannel($channel);
        
        return $this->json($items);
    }
    
    /**
     * Create a Post
     *
     * @Route("/create", name="post_create", methods={"POST"})
     * @OA\Response(
     *     response=201,
     *     description="Returns the created Post",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Post::class))
     *     )
     * )
     * @OA\Response(
     *     response=422,
     *     description="Returns the status code and an array with a list of errors",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Post::class))
     *     )
     * )
     * @OA\RequestBody(
     *     description="Title, description and channel",
     *     required=true,
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/Post")
     *     )
     * )
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
     * Update a Post on the given Id
     *
     * @Route("/{id}", name="update_post_by_id", requirements={"id"="\d+"}, methods={"PUT"})
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="The Id of the Post to update",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Response(
     *     response=200,
     *     description="Returns the updated Post",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Post::class))
     *     )
     * )
     * @OA\Response(
     *     response=422,
     *     description="Returns the status code and an array with a list of errors",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Post::class))
     *     )
     * )
     * @OA\RequestBody(
     *     description="Title, description and channel",
     *     required=true,
     *     @OA\MediaType(
     *         mediaType="application/json",
     *         @OA\Schema(ref="#/components/schemas/Post")
     *     )
     * )
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
     * Delete a Post
     *
     * @Route("/{id}", name="post_delete", requirements={"id"="\d+"}, methods={"DELETE"})
     * @OA\Parameter(
     *     name="id",
     *     in="path",
     *     description="The Id of the Post to delete",
     *     @OA\Schema(type="integer")
     * )
     * @OA\Response(
     *     response=204,
     *     description="The resource was deleted successfully."
     * )
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