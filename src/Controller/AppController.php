<?php

namespace App\Controller;

use Awurth\Slim\Helper\Controller\Controller;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;

class AppController extends Controller
{
    public function home(Request $request, Response $response)
    {
        return $this->twig->render($response, 'app/home.twig');
    }

    public function profile(Request $request, Response $response)
    {
        $nameKey = $this->csrf->getTokenNameKey();
        $valueKey = $this->csrf->getTokenValueKey();

        $data = 
        [
            "nameKey" => $nameKey,
            "valueKey" => $valueKey,
            "name" => $request->getAttribute($nameKey),
            "value" => $request->getAttribute($valueKey)    
        ];

        return $this->twig->render($response, 'app/profile.twig', $data);
    }

    public function upload(Request $request, Response $response)
    {
        // Get file from request body
        $files = $request->getUploadedFiles();
        if (empty($files['file'])) 
        {
            throw new Exception('Expected a newfile');
            $response->withStatus(400);
        }

        $newfile = $files['file'];

        // Save file to server
        if ($newfile->getError() === UPLOAD_ERR_OK) {
            $uploadFileName = $newfile->getClientFilename();
            $newfile->moveTo( "assets/videos/$uploadFileName" );
        }
        else
        {
            $response->withStatus(401);
        }

        // Redirect / render profile page
        return $this->twig->render($response, 'app/profile.twig');
    }
}
