<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class KeranjangControllers extends BaseController
{
    public function index()
    {
        return view('v_keranjang'); // Load the view for the cart page
    }
}
