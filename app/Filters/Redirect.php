<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Redirect implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // No action before the request
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Check if this is a POST request to login and user is logged in
        if ($request->getMethod() === 'post' && $request->getUri()->getPath() === 'login') {
            if (session()->get('isLoggedIn')) {
                // Redirect to produk page
                return redirect()->to(site_url('produk'));
            }
        }
    }
}
