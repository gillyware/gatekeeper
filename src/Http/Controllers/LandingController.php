<?php

namespace Gillyware\Gatekeeper\Http\Controllers;

use Illuminate\Contracts\View\View;

class LandingController extends AbstractBaseController
{
    public function __invoke(): View
    {
        return view('gatekeeper::layout');
    }
}
