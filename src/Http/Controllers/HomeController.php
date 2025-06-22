<?php

namespace Gillyware\Gatekeeper\Http\Controllers;

use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('gatekeeper::layout');
    }
}
