<?php

namespace Invoicing\Tests;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

class RequestBuilder
{
    /**
     * @var string
     */
    private $kernelClass;

    /**
     * @var string
     */
    private $method = 'GET';

    /**
     * @var string
     */
    private $route;

    /**
     * @var string|null
     */
    private $content;

    /**
     * @var array
     */
    private $requestHeaders = [];

    /**
     * @var UserInterface[]
     */
    private $usersToAuthenticate = [];

    /**
     * @var array
     */
    private $files = [];

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var array
     */
    private $routeParams = [];

    /**
     * @var array
     */
    private $mockedServices = [];

    /**
     * @var boolean
     */
    private $profiler = false;

    /**
     * @param string $route
     * @param string $kernelClass
     */
    public function __construct($route, $kernelClass)
    {
        $this->kernelClass = $kernelClass;
        $this->route = $route;
    }

    /**
     * @param  string         $method
     * @return RequestBuilder
     */
    public function withMethod($method)
    {
        $builder = clone $this;
        $builder->method = $method;

        return $builder;
    }

    /**
     * @param  array          $params
     * @return RequestBuilder
     */
    public function withRouteParams(array $params)
    {
        $builder = clone $this;
        $builder->routeParams = $params;

        return $builder;
    }

    /**
     * @param  array          $parameters
     * @return RequestBuilder
     */
    public function withParameters(array $parameters)
    {
        $builder = clone $this;
        $builder->parameters = $parameters;

        return $builder;
    }

    /**
     * @param  UserInterface  $user
     * @param  string         $firewall
     * @return RequestBuilder
     */
    public function withAuthenticatedUser(
        UserInterface $user,
        $firewall = 'main'
    ) {
        $builder = clone $this;
        $builder->usersToAuthenticate[$firewall] = $user;

        return $builder;
    }

    /**
     * @param  array          $mockedServices
     * @return RequestBuilder
     */
    public function withMockedServices(array $mockedServices)
    {
        $builder = clone $this;
        $builder->mockedServices = $mockedServices;

        return $builder;
    }

    /**
     * @param  string         $name
     * @param  string         $value
     * @return RequestBuilder
     */
    public function withHeader($name, $value)
    {
        $builder = clone $this;
        $builder->requestHeaders[$name] = $value;

        return $builder;
    }

    /**
     * @param  string         $content
     * @return RequestBuilder
     */
    public function withContent($content)
    {
        $builder = clone $this;
        $builder->content = $content;

        return $builder;
    }

    /**
     * Serializes automatically the content to json and sets content type
     * to json.
     *
     * @param  mixed          $content
     * @return RequestBuilder
     */
    public function withJsonContent($content)
    {
        $builder = clone $this;
        $builder->content = json_encode($content);
        $builder->requestHeaders['CONTENT_TYPE'] = 'application/json';
        $builder->requestHeaders['HTTP_ACCEPT'] = 'application/json';

        return $builder;
    }

    /**
     * @return RequestBuilder
     */
    public function withProfiler()
    {
        $builder = clone $this;
        $builder->profiler = true;

        return $builder;
    }

    /**
     * @param  array          $files
     * @return RequestBuilder
     */
    public function withFiles(array $files)
    {
        $builder = clone $this;
        $builder->files = $files;

        return $builder;
    }

    /**
     * @return Client
     */
    public function doRequest()
    {
        $client = $this->createClient();

        if ($this->profiler) {
            $client->enableProfiler();
        }
        /** @var Router $router */
        $router = $client->getContainer()->get('router');

        $client->request(
            $this->method,
            $router->generate($this->route, $this->routeParams),
            $this->parameters,
            $this->files,
            $this->requestHeaders,
            $this->content
        );

        $d = $client->getContainer()->get('doctrine');
        $d->getConnection()->close();

        return $client;
    }

    /**
     * @return Client
     */
    private function createClient()
    {
        /* @var KernelInterface $kernel */
        $kernel = new $this->kernelClass('test', true);
        $kernel->boot();

        /** @var Client $client */
        $client = $kernel->getContainer()->get('test.client');
        $session = $client->getContainer()->get('session');

        foreach ($this->usersToAuthenticate as $firewall => $user) {
            $token = new UsernamePasswordToken(
                $user,
                null,
                $firewall,
                $user->getRoles()
            );

            $session->set('_security_'.$firewall, serialize($token));
            $session->save();

            $cookie = new Cookie($session->getName(), $session->getId());
            $client->getCookieJar()->set($cookie);
        }

        foreach ($this->mockedServices as $name => $service) {
            $client->getContainer()->set($name, $service);
        }

        $session->start();
        $session->save();

        return $client;
    }
}
