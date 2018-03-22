<?php

namespace App\Controller;

use Awurth\Slim\Helper\Controller\Controller;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;

use App\Model\Video;
use App\Model\User;

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

        $user_id = $this->auth->getUser()->id;

        $videos = Video::where('user_id', '=', $user_id)->get();
        $videos_data = [];
        foreach($videos as $v)
        {
            $current_vid = [
                "id" => $v->id,
                "name" => $v->name
            ];

            array_push($videos_data, $current_vid);
        }

        $data = 
        [
            "nameKey" => $nameKey,
            "valueKey" => $valueKey,
            "name" => $request->getAttribute($nameKey),
            "value" => $request->getAttribute($valueKey),
            "videos" => $videos_data
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
        }

        $newfile = $files['file'];

        $video_name = uniqid() ;

        // Save file to server
        if ($newfile->getError() === UPLOAD_ERR_OK) {
            $newfile->moveTo("assets/videos/$video_name");
        }

        $new_video = new Video;
        $new_video->link = "assets/videos/$video_name";
        $new_video->name = "New video";
        $new_video->user_id = $this->auth->getUser()->id;

        $new_video->save();

        return $this->twig->render($response, 'app/profile.twig');
    }

    public function deleteVideo(Request $request, Response $response, $id)
    {
        if($user = $this->auth->getUser())
        {
            $video = Video::find($id);

            if($video && $video->user->id === $user->id)
            {
                unlink($video->link);
                $video->delete();

                $this->flash('success', 'The video has been deleted successfully.');                
            }
            else
            {
                $this->flash('danger', 'The video you are trying to delete does not seem to exist.');                
            }
        }
        else 
        {
            $this->flash('danger', 'The video you are trying to delete does not seem to belong to you.');                            
        }

        return $this->redirect($response, 'profile');      
    }

    public function renameVideo(Request $request, Response $response, $id)
    {
        if($user = $this->auth->getUser())
        {
            $video = Video::find($id);

            if($video && $video->user->id === $user->id)
            {
                $video->name = addslashes($request->getParsedBody()['newName']);
                $video->update();

                $this->flash('success', 'The video has been renamed successfully.');                
            }
            else
            {
                $this->flash('danger', 'The video you are trying to rename does not seem to exist.');                
            }
        }
        else 
        {
            $this->flash('danger', 'The video you are trying to rename does not seem to belong to you.');                            
        }

        return $this->redirect($response, 'profile');              
    }

    public function dashboard(Request $request, Response $response)
    {
        return $this->twig->render($response, 'app/dashboard.twig');
    }
}