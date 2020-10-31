<?php

namespace App\Tests\Controller;

use App\Entity\Post;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PostsControllerExceptionTest extends WebTestCase
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
     * Test all endpoints with invalid methods
     */
    public function testInvalidMethods(): void
    {
        $this->client->request('POST', '/posts/');
        self::assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $this->client->getResponse()->getStatusCode(), 'Did not get Status Code 405');
        
        $this->client->request('PUT', '/posts/');
        self::assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $this->client->getResponse()->getStatusCode(), 'Did not get Status Code 405');
        
        $this->client->request('DELETE', '/posts/');
        self::assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $this->client->getResponse()->getStatusCode(), 'Did not get Status Code 405');
        
        $this->client->request('PATCH', '/posts/');
        self::assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $this->client->getResponse()->getStatusCode(), 'Did not get Status Code 405');
        
        $this->client->request('POST', '/posts/1');
        self::assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $this->client->getResponse()->getStatusCode(), 'Did not get Status Code 405');
        
        $this->client->request('PATCH', '/posts/1');
        self::assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $this->client->getResponse()->getStatusCode(), 'Did not get Status Code 405');
        
        $this->client->request('POST', '/posts/website');
        self::assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $this->client->getResponse()->getStatusCode(), 'Did not get Status Code 405');
    
        $this->client->request('PUT', '/posts/website');
        self::assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $this->client->getResponse()->getStatusCode(), 'Did not get Status Code 405');
    
        $this->client->request('DELETE', '/posts/website');
        self::assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $this->client->getResponse()->getStatusCode(), 'Did not get Status Code 405');
    
        $this->client->request('PATCH', '/posts/website');
        self::assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $this->client->getResponse()->getStatusCode(), 'Did not get Status Code 405');
        
        $this->client->request('PUT', '/posts/create');
        self::assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $this->client->getResponse()->getStatusCode(), 'Did not get Status Code 405');
        
        $this->client->request('DELETE', '/posts/create');
        self::assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $this->client->getResponse()->getStatusCode(), 'Did not get Status Code 405');
    
        $this->client->request('PATCH', '/posts/create');
        self::assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $this->client->getResponse()->getStatusCode(), 'Did not get Status Code 405');
    }
    
    /**
     * Test a content-type that is not json
     */
    public function testInvalidContentType(): void
    {
        $postContent = json_encode([
            'title' => 'PHP Unit Testing',
            'description' => 'Hello Hello Hello Hello Hello',
            'channel' => 'website',
        ]);
    
        $this->client->request('PUT', '/posts/1', [], [], ['CONTENT_TYPE' => 'text/plain'], $postContent);
        self::assertEquals(Response::HTTP_UNSUPPORTED_MEDIA_TYPE, $this->client->getResponse()->getStatusCode(), 'Did not get Status Code 415');
    
        $this->client->request('POST', '/posts/create', [], [], ['CONTENT_TYPE' => 'text/plain'], $postContent);
        self::assertEquals(Response::HTTP_UNSUPPORTED_MEDIA_TYPE, $this->client->getResponse()->getStatusCode(), 'Did not get Status Code 415');
    }
    
    /**
     * Test not meeting the entity validation rules
     */
    public function testUpsertEntityValidation(): void
    {
        $postContent = json_encode([
            'title' => 'PHP',
            'description' => 'Hello',
            'channel' => 'xpto',
        ]);
    
        $this->client->request('PUT', '/posts/1', [], [], ['CONTENT_TYPE' => 'application/json'], $postContent);
        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode(), 'Did not get Status Code 415');
    
        $this->client->request('POST', '/posts/create', [], [], ['CONTENT_TYPE' => 'application/json'], $postContent);
        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode(), 'Did not get Status Code 415');
    }
}