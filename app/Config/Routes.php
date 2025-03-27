<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('produk', 'ProdukControllers::index');
$routes->get('keranjang', 'KeranjangControllers::index');
