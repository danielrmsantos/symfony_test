<?php

namespace App\Tests\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class PostsControllerTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    
    /**
     * @var KernelBrowser
     */
    private $client;
    
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
        $this->client = self::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $this->defaultPostId = 1;
        $this->defaultPostChannel = 'website';
    }
    
    /**
     * Test GET all Posts endpoint
     */
    public function testGetPosts(): void
    {
        $this->client->request('GET', '/posts/');
        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        
        $items = $this->entityManager->getRepository(Post::class)->findAll();
        
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode(), 'Did not get Status Code 200');
        self::assertSame('application/json', $response->headers->get('Content-Type'),
            'Did not get application/json Content-Type in headers.');
        self::assertNotEmpty($responseData, 'Response is empty');
        self::assertGreaterThanOrEqual(1, count($responseData), 'Should get at least one Post with channel website');
        self::assertCount(count($items), $responseData,
            'Results count from response expected to be the same as from DB query');
        
        foreach ($responseData as $post) {
            $this->validatePost($post);
        }
    }
    
    /**
     * Test GET Post by id endpoint
     */
    public function testGetPostById(): void
    {
        $this->client->request('GET', '/posts/' . $this->defaultPostId);
        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode(), 'Did not get Status Code 200');
        self::assertSame('application/json', $response->headers->get('Content-Type'),
            'Did not get application/json Content-Type in headers.');
        self::assertNotEmpty($responseData, 'Response is empty');
        $this->validatePost($responseData);
    }
    
    /**
     * Test GET Post by channel endpoint
     */
    public function testGetPostsByChannel(): void
    {
        $this->client->request('GET', '/posts/' . $this->defaultPostChannel);
        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        
        $items = $this->entityManager->getRepository(Post::class)->findByChannel($this->defaultPostChannel);
        
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode(), 'Did not get Status Code 200');
        self::assertSame('application/json', $response->headers->get('Content-Type'),
            'Did not get application/json Content-Type in headers.');
        self::assertNotEmpty($responseData, 'Response is empty');
        self::assertGreaterThanOrEqual(1, count($responseData),
            'Should get at least one Post with channel ' . $this->defaultPostChannel);
        self::assertCount(count($items), $responseData,
            'Results count from response expected to be the same as from DB query');
        
        foreach ($responseData as $post) {
            $this->validatePost($post);
        }
    }
    
    /**
     * Test Create a Post endpoint
     * The created Post id is returned to testDeletePost function to be deleted
     * as suggested dama/doctrine-test-bundle package throws deprecation notices
     */
    public function testCreatePost(): int
    {
        $postContent = json_encode([
            'title' => 'PHP Unit Testing',
            'description' => 'Hello Hello Hello Hello Hello',
            'channel' => $this->defaultPostChannel,
        ]);
        
        $this->client->request('POST', '/posts/create', [], [], ['CONTENT_TYPE' => 'application/json'], $postContent);
        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        
        self::assertEquals(Response::HTTP_CREATED, $response->getStatusCode(), 'Did not get Status Code 201');
        self::assertSame('application/json', $response->headers->get('Content-Type'),
            'Did not get application/json Content-Type in headers.');
        self::assertNotEmpty($responseData, 'Response is empty');
        $this->validatePost($responseData);
        
        return $responseData['id'];
    }
    
    /**
     * Test Update a Post endpoint
     */
    public function testUpdatePost(): void
    {
        $post = $this->entityManager->getRepository(Post::class)->findOneBy(['id' => $this->defaultPostId]);
        $currentPostTitle = $post->getTitle();
        
        $title = 'PHP Unit Testing UPDATED';
        $postContent = json_encode([
            'title' => $title,
        ]);
        
        $this->client->request('PUT', '/posts/1', [], [], ['CONTENT_TYPE' => 'application/json'], $postContent);
        $response = $this->client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode(), 'Did not get Status Code 200');
        self::assertSame('application/json', $response->headers->get('Content-Type'),
            'Did not get application/json Content-Type in headers.');
        self::assertNotEmpty($responseData, 'Response is empty');
        $this->validatePost($responseData);
        self::assertEquals($title, $responseData['title']);
        
        /*
         * Reset updated post manually as suggested dama/doctrine-test-bundle throws deprecation notices
         * @see https://github.com/dmaicher/doctrine-test-bundle/issues/129
         *
         */
        $post->setTitle($currentPostTitle);
        $this->entityManager->persist($post);
        $this->entityManager->flush();
    }
    
    /**
     * Test Delete a Post endpoint
     *
     * Delete the created Post manually as suggested dama/doctrine-test-bundle package throws deprecation notices
     * @see https://github.com/dmaicher/doctrine-test-bundle/issues/129
     *
     * @depends testCreatePost
     *
     * @param integer $id
     */
    public function testDeletePost($id): void
    {
        $this->client->request('DELETE', '/posts/' . $id);
        $response = $this->client->getResponse();
        
        self::assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode(), 'Did not get Status Code 204');
        self::assertEmpty($response->getContent(), 'Response is not empty');
    
        $post = $this->entityManager->getRepository(Post::class)->findOneBy(['id' => $id]);
        self::assertEmpty($post);
    }
    
    /**
     * Validate that Post has expected attributes and values
     *
     * @param $post
     */
    private function validatePost($post): void
    {
        self::assertArrayHasKey('id', $post);
        self::assertArrayHasKey('title', $post);
        self::assertArrayHasKey('description', $post);
        self::assertArrayHasKey('channel', $post);
        self::assertIsInt($post['id'], 'Got a ' . gettype($post['id']) . ' instead of integer');
        self::assertIsString($post['title'], 'Got a ' . gettype($post['title']) . ' instead of string');
        self::assertIsString($post['description'], 'Got a ' . gettype($post['description']) . ' instead of string');
        self::assertIsString($post['channel'], 'Got a ' . gettype($post['channel']) . ' instead of string');
    }
    
    protected function tearDown()
    {
        parent::tearDown();
        
        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}