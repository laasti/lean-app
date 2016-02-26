<?php

namespace Laasti\LeanApp\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class WelcomeController
{

    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {
        $response->getBody()->write('Hello world!');
        return $response;
    }
}
