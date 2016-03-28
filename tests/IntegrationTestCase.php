<?php

namespace Tests;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

abstract class IntegrationTestCase extends WebTestCase
{
    protected function setUp()
    {
        gc_collect_cycles();
        gc_disable();

        $this->init();
        $this->up();
    }

    protected function init()
    {
    }

    protected function deinit()
    {
    }

    protected function up()
    {
    }

    protected function down()
    {
    }

    /**
     * @param  string         $route
     * @param  User|null      $authenticatedUser
     * @return RequestBuilder
     */
    protected function aRequest($route, User $authenticatedUser = null)
    {
        $rb = new RequestBuilder($route, \AppKernel::class);

        if ($authenticatedUser) {
            $_SESSION['user'] = $authenticatedUser->getUsername();
        }

        return $rb;
    }

    /**
     * @param Response $response
     */
    protected function assertEmptyResponse(Response $response)
    {
        $this->assertResponse($response, 204, '');
    }

    /**
     * @param Response    $response
     * @param integer     $expectedStatusCode
     * @param string|null $expectedContent
     */
    protected function assertResponse(
        Response $response,
        $expectedStatusCode,
        $expectedContent = null
    ) {
        if (!is_null($expectedContent)) {
            $this->assertEquals($expectedContent, $response->getContent());
        }

        $this->assertEquals(
            $expectedStatusCode,
            $response->getStatusCode(),
            // show content in message to display possible error/exception from response
            'Response status code does not match expected. Response content: ' . $response->getContent()
        );
    }

    /**
     * @param Response   $response
     * @param integer    $expectedStatusCode
     * @param mixed|null $expectedContent
     */
    protected function assertJsonResponse(
        Response $response,
        $expectedStatusCode,
        $expectedContent = null
    ) {
        if (!is_null($expectedContent)) {
            $this->assertEquals(
                json_encode($expectedContent, JSON_PRETTY_PRINT),
                json_encode(json_decode($response->getContent()),  JSON_PRETTY_PRINT)
            );
        }

        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
    }

    protected function tearDown()
    {
        $this->down();
        $this->deinit();

        $d = $this->getContainer()->get('doctrine');

        $d->getConnection()->close();

        gc_enable();

        $refl = new \ReflectionObject($this);
        foreach ($refl->getProperties() as $prop) {
            if (!$prop->isStatic() && 0 !== strpos($prop->getDeclaringClass()->getName(), 'PHPUnit_')) {
                $prop->setAccessible(true);
                $prop->setValue($this, null);
            }
        }
    }
}
