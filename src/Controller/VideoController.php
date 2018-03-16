<?php

namespace App\Controller;

use Awurth\Slim\Helper\Controller\Controller;
use Slim\Http\Request;
use Slim\Http\Response;

class VideoController extends Controller
{
    public function videos(Request $request, Response $response)
    {

        return $this->twig->render($response, 'app/videos.twig');
    }
}
