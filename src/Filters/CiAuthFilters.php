<?php

namespace Anwarqasem\CiAuth\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Anwarqasem\CiAuth\CiAuth;

class CiAuthFilters implements FilterInterface
{

    /**
     * @param RequestInterface $request
     * @param null $arguments
     * @return bool
     */
    public function before(RequestInterface $request, $arguments = null): bool
    {
        $isLogged = new CiAuth();
        if (!$isLogged->isLogged()) {
            $result = [
                'error'         => false,
                'require_login' => true,
                'data'          => ["User not logged in"]
            ];
            $isLogged->view($result);
        }
        return true;
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param null $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {

    }

}