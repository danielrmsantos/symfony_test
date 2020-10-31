<?php
namespace App\Tests\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class PostControllerTest extends WebTestCase
{
    /**
     * @var KernelBrowser
     */
    private $client;
    
    protected function setUp()
    {
        $this->client = self::createClient();
    }
    
    /**
     * Test GET Post by id endpoint
     */
    public function testGetPostById(): void
    {
        $this->client->request('GET', '/posts/1');
        $response = $this->client->getResponse();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode(), 'Did not get Status Code 200');
        self::assertSame('application/json', $response->headers->get('Content-Type'), 'Did not get application/json Content-Type in headers.');
        self::assertNotEmpty($responseData, 'Response is empty');
        $this->validatePost($responseData);
    }
    
    /**
     * Test GET Post by channel endpoint
     */
    public function testGetPostsByChannel(): void
    {
        $this->client->request('GET', '/posts/website');
        $response = $this->client->getResponse();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
    
        $em = $this->client->getContainer()->get('doctrine')->getManager();
    
        $items = $em->getRepository(Post::class)->findByChannel('website');
        
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode(), 'Did not get Status Code 200');
        self::assertSame('application/json', $response->headers->get('Content-Type'), 'Did not get application/json Content-Type in headers.');
        self::assertNotEmpty($responseData, 'Response is empty');
        self::assertGreaterThanOrEqual(1, count($responseData), 'Should get at least one Post with channel website');
        self::assertCount(count($items), $responseData, 'Results count from response expected to be the same as from DB query');
        
        foreach ($responseData as $post) {
            $this->validatePost($post);
        }
    }
    
    /**
     * Test GET all Posts endpoint
     */
    public function testGetPosts(): void
    {
        $this->client->request('GET', '/posts/');
        $response = $this->client->getResponse();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
    
        $em = $this->client->getContainer()->get('doctrine')->getManager();
    
        $items = $em->getRepository(Post::class)->findAll();
        
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode(), 'Did not get Status Code 200');
        self::assertSame('application/json', $response->headers->get('Content-Type'), 'Did not get application/json Content-Type in headers.');
        self::assertNotEmpty($responseData, 'Response is empty');
        self::assertGreaterThanOrEqual(1, count($responseData), 'Should get at least one Post with channel website');
        self::assertCount(count($items), $responseData, 'Results count from response expected to be the same as from DB query');
        
        foreach ($responseData as $post) {
            $this->validatePost($post);
        }
    }
    
    /**
     * Test create a Post endpoint
     */
    public function testCreatePost(): void
    {
        $post = json_encode([
            'title' => 'PHP Unit Testing',
            'description' => 'Hello Hello Hello Hello Hello',
            'channel' => 'website',
        ]);
        
        $this->client->request('POST', '/posts/create', [], [], ['CONTENT_TYPE' => 'application/json'], $post);
        $response = $this->client->getResponse();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        
        self::assertEquals(Response::HTTP_CREATED, $response->getStatusCode(), 'Did not get Status Code 201');
        self::assertSame('application/json', $response->headers->get('Content-Type'), 'Did not get application/json Content-Type in headers.');
        self::assertNotEmpty($responseData, 'Response is empty');
        $this->validatePost($responseData);
    }
    
    /**
     * Validate that Post has expected properties and values
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
}