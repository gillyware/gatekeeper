<?php

namespace Gillyware\Gatekeeper\Http\Controllers;

use Gillyware\Gatekeeper\Traits\Responds;
use Illuminate\Routing\Controller as BaseController;

abstract class AbstractBaseController extends BaseController
{
    use Responds;
}
