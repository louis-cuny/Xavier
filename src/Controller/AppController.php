<?php

namespace App\Controller;

use Awurth\Slim\Helper\Controller\Controller;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;

use App\Model\Video;
use App\Model\User;
use App\Model\Sequence;
use App\Model\Comment;

class AppController extends Controller
{
    public function home(Request $request, Response $response)
    {
        $sequences = Sequence::orderBy('created_at', 'desc')->get();
        $sequences_data = [];

        $nb = 5;
        $max_nb = count($sequences) < $nb ? count($sequences) : $nb; 
        for($i = 0 ; $i < $max_nb ; $i++)
        {
            $seq_data = [
                "id" => $sequences[$i]->id,
                "name" => $sequences[$i]->name
            ];

            array_push($sequences_data, $seq_data);
        }

        $data = [
            "rand_id" => count($sequences) > 0 ? $sequences[rand(0 ,count($sequences) - 1)]->id : null,
            "sequences" => $sequences_data
        ];

        return $this->twig->render($response, 'app/home.twig', $data);
    }

    public function profile(Request $request, Response $response)
    {
        $user_id = $this->auth->getUser()->id;

        $videos = Video::where('user_id', '=', $user_id)->get();
        $videos_data = [];
        foreach($videos as $v)
        {
            $current_vid = [
                "id" => $v->id,
                "name" => $v->name,
                "sequences" => []
            ];

            $sequences = $v->sequences()->get();
            foreach($sequences as $seq)
            {
                array_push($current_vid["sequences"], [
                    "id" => $seq->id,
                    "name" => $seq->name,
                    "start" => $seq->start,
                    "end"=> $seq->end
                ]);
            }

            array_push($videos_data, $current_vid);
        }

        $data = 
        [
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

        $video_name = uniqid();

        // Save file to server
        if ($newfile->getError() === UPLOAD_ERR_OK) {
            $newfile->moveTo("assets/videos/$video_name");
        }

        $new_video = new Video;
        $new_video->link = "assets/videos/$video_name";
        $new_video->name = "Nouvelle vidéo";
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

                $this->flash('success', 'La vidéo a été supprimé avec succès.');                
            }
            else
            {
                $this->flash('danger', 'La vidéo que vous essayez de supprimer ne semble pas exister.');                
            }
        }
        else 
        {
            $this->flash('danger', 'La vidéo que vous essayez de supprimer ne semble pas vous appartenir.');                            
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
                $video->name = filter_var($request->getParsedBody()['newName'], FILTER_DEFAULT);
                $video->update();

                $this->flash('success', 'La vidéo a été renommé avec succès.');                
            }
            else
            {
                $this->flash('danger', 'La vidéo que vous essayez de renommer ne semble pas exister.');                
            }
        }
        else 
        {
            $this->flash('danger', 'La vidéo que vous essayez de renommer ne semble pas vous appartenir.');                            
        }

        return $this->redirect($response, 'profile');              
    }

    public function deleteSequence(Request $request, Response $response, $id)
    {
        if(! $sequence = Sequence::find($id))
        {
            $this->flash('danger', 'La séquence que vous essayez de supprimer ne semble pas exister.');                            
         
            return $this->redirect($response, 'profile');                          
        }

        $video = $sequence->video;
        if(! $video->user->id === $this->auth->getUser()->id)
        {
            $this->flash('danger', 'La séquence que vous essayez de supprimer ne semble pas vous appartenir.');                            
         
            return $this->redirect($response, 'profile'); 
        }

        $sequence->delete();

        $this->flash('success', 'La séquence a été supprimé avec succès.');                                    

        return $this->redirect($response, 'profile');              
    }

    public function renameSequence(Request $request, Response $response, $id)
    {
        if($user = $this->auth->getUser())
        {
            $seq = Sequence::find($id);

            if($seq && $seq->video->user->id === $user->id)
            {
                $seq->name = filter_var($request->getParsedBody()['newName'], FILTER_DEFAULT);
                $seq->update();

                $this->flash('success', 'La séquence a été renommé avec succès.');                
            }
            else
            {
                $this->flash('danger', 'La séquence que vous essayez de supprimer ne semble pas exister.');                
            }
        }
        else 
        {
            $this->flash('danger', 'La séquence vous que essayez de renommer ne semble pas vous appartenir.');                            
        }

        return $this->redirect($response, 'profile');                     
    }

    public function displayComments(Request $request, Response $response, $id)
    {
        if(! $seq = Sequence::find($id))
        {
            $this->flash('danger', 'La séquence que vous essayez de regarder ne semble pas exister.');                            

            return $this->redirect($response, 'home');                     
        }

        $video = $seq->video;

        $comments = [];

        foreach($seq->comments as $com)
        {
            array_push($comments, ["id" => $com->id, "comment" => $com->comment]);
        }


        $data = [
            "id" => $seq->id,
            "start" => $seq->start,
            "end" => $seq->end,
            "name" => $seq->name,
            "link" => $video->link,
            "comments" => $comments,
            "isAdmin" => $this->auth->getUser() && ($video->user_id === $this->auth->getUser()->id)
        ];

        return $this->twig->render($response, 'app/videoComments.twig', $data);        
    }

    public function addComment(Request $request, Response $response, $id)
    {
        if(! $sequence = Sequence::find($id))
        {
            $this->flash('danger', 'Vous avez essayé de commenter une séquence qui n\'existe pas.'); 
            
            return $this->redirect($response, 'home');              
        }

        $text = $request->getParsedBody()["comment"];

        $comment = new Comment();
        $comment->comment = filter_var($text, FILTER_DEFAULT);
        $comment->sequence_id = $id;
        $comment->save();

        $this->flash('success', 'Votre commentaire a bien été pris en compte.'); 
    
        return $this->redirect($response, 'home');  
    }

    public function deleteComment(Request $request, Response $response, $id)
    {
        if(! $comment = Comment::find($id))
        {
            $this->flash('success', 'Le commentaire que vous essayez de supprimer ne semble pas exister.');
            
            return $this->redirect($response, 'home');              
        }

        $id_sequence = $comment->sequence->id;
        $comment->delete();

        $this->flash('success', 'Le commentaire a été supprimé avec succès.');  

        $url = $this->router->pathFor('comments', ['id' => $id_sequence]);

        return $response->withStatus(200)->withHeader('Location', $url);  
    }

    public function dashboard(Request $request, Response $response, $id)
    {
        if ($request->isPost()) {
            $sequence = new Sequence($_POST);
            $sequence->save();
            $this->flash('success', 'Votre séquence a été sauvegardé.');

            return $this->redirect($response, 'profile');             
        }

        // Checking if the video exists
        if(! $video = Video::find($id))
        {
            $this->flash('danger', 'La vidéo à laquelle vous essayez d\'acceder ne semble pas exister.');   
            
            return $this->redirect($response, 'profile'); 
        }

        // Checking if the video belongs to the user trying to create a sequence
        $current_user = $this->auth->getUser();

        if(! $video->user_id === $current_user->id)
        {
            $this->flash('danger', 'La vidéo à laquelle vous essayez d\'accéder ne semble pas vous appartenir.');            
                
            return $this->redirect($response, 'profile');              
        }

        return $this->twig->render($response, 'app/dashboard.twig', 
            [
                "id" => $video->id,
                "name" => $video->name,
                "link" => $video->link
            ]);
    }

    public function export(Request $request, Response $response)
    {
        $sequences = Sequence::orderBy('created_at', 'desc')->get();
        $sequences_data = [];
        //$comments = [];

        for($i = 0 ; $i < count($sequences) ; $i++)
        {
            $comments = [];
            foreach($sequences[$i]->comments as $com)
            {
                array_push($comments, ["id" => $com->id, "comment" => $com->comment]);
            }

            $seq_data = [
                "id" => $sequences[$i]->id,
                "name" => $sequences[$i]->name,
                "expression" => $sequences[$i]->expression,
                "comments" => $comments
            ];

            array_push($sequences_data, $seq_data);
        }

        $data = [
            "sequences" => $sequences_data
        ];

        return $this->twig->render($response, 'app/export.twig', $data);
    }

    public function xmlExport(Request $request, Response $response)
    {

//standby, CSRF erreur

         $xml = new XMLWriter();

        $xml->openURI("php://output");
        $xml->startDocument();
        $xml->setIndent(true);

        $xml->startElement('Langage');
        //for
          $xml->startElement("Pseudo-langage");
          $xml->writeAttribute('desc', $_POST['name']);
          //for
            $xml->startElement("langage-humain");
            $xml->writeRaw($_POST['desc']);
            $xml->endElement();
          //endfor
          $xml->endElement();
        //endfor
          $xml->endElement();

        header('Content-type: text/xml');
        $xml->flush();
    }


}