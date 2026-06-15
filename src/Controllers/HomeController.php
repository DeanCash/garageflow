<?php

namespace GarageFlow\Controllers;

use GarageFlow\Support\View;

class HomeController
{
    public function index(): void
    {
        View::render('home', [], 'Garage Verhoeven - GarageFlow');
    }
}
