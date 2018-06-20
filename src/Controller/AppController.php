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
use App\Model\Label;

class AppController extends Controller
{
    public function home(Request $request, Response $response)
    {
        $sequences = Sequence::orderBy('created_at', 'desc')->get();
        $sequences_data = [];

        $nb = 5;
        $max_nb = count($sequences) < $nb ? count($sequences) : $nb;
        for ($i = 0; $i < $max_nb; $i++) {
            $seq_data = [
                "id"   => $sequences[$i]->id,
                "name" => $sequences[$i]->video->name,
                "timing" => $sequences[$i]->start . ' - ' . $sequences[$i]->end
            ];

            array_push($sequences_data, $seq_data);
        }

        $data = [
            "rand_id"   => count($sequences) > 0 ? $sequences[rand(0, count($sequences) - 1)]->id : null,
            "sequences" => $sequences_data
        ];

        return $this->twig->render($response, 'app/home.twig', $data);
    }

    public function profile(Request $request, Response $response)
    {
        $user_id = $this->auth->getUser()->id;

        $videos = Video::where('user_id', '=', $user_id)->get();
        $videos_data = [];
        foreach ($videos as $v) {
            $current_vid = [
                "id"        => $v->id,
                "name"      => $v->name,
                "sequences" => []
            ];

            $sequences = $v->sequences()->get();
            foreach ($sequences as $seq) {
                array_push($current_vid["sequences"], [
                    "id"    => $seq->id,
                    "name"  => $seq->label->expression,
                    "timing" => $seq->start . ' - ' . $seq->end,
                    "start" => $seq->start,
                    "end"   => $seq->end
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
        if (empty($files['file'])) {
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
        if ($user = $this->auth->getUser()) {
            $video = Video::find($id);

            if ($video && $video->user->id === $user->id) {
                unlink($video->link);
                $video->delete();

                $this->flash('success', 'The video has been deleted successfully.');
            } else {
                $this->flash('danger', 'The video you are trying to delete does not seem to exist.');
            }
        } else {
            $this->flash('danger', 'The video you are trying to delete does not seem to belong to you.');
        }

        return $this->redirect($response, 'profile');
    }

    public function renameVideo(Request $request, Response $response, $id)
    {
        if ($user = $this->auth->getUser()) {
            $video = Video::find($id);

            if ($video && $video->user->id === $user->id) {
                $video->name = filter_var($request->getParsedBody()['newName'], FILTER_DEFAULT);
                $video->update();

                $this->flash('success', 'The video has been renamed successfully.');
            } else {
                $this->flash('danger', 'The video you are trying to rename does not seem to exist.');
            }
        } else {
            $this->flash('danger', 'The video you are trying to rename does not seem to belong to you.');
        }

        return $this->redirect($response, 'profile');
    }

    public function deleteSequence(Request $request, Response $response, $id)
    {
        if (!$sequence = Sequence::find($id)) {
            $this->flash('danger', 'The sequence you are trying to delete does not seem to exist.');

            return $this->redirect($response, 'profile');
        }

        $video = $sequence->video;
        if (!$video->user->id === $this->auth->getUser()->id) {
            $this->flash('danger', 'The sequence you are trying to delete does not seem to belong to you.');

            return $this->redirect($response, 'profile');
        }

        $sequence->delete();

        $this->flash('success', 'The sequence has been successfully deleted.');

        return $this->redirect($response, 'profile');
    }

    public function displayComments(Request $request, Response $response, $id)
    {
        if (!$seq = Sequence::find($id)) {
            $this->flash('danger', 'The sequence you are trying to watch does not seem to exist.');

            return $this->redirect($response, 'home');
        }

        $video = $seq->video;

        $comments = [];

        foreach ($seq->comments as $com) {
            array_push($comments, ["id" => $com->id, "comment" => $com->comment]);
        }

        $data = [
            'id'       => $seq->id,
            'start'    => $seq->start,
            'end'      => $seq->end,
            'video_name' => $seq->video->name,
            'name' => $seq->label->expression,
            'link'     => $video->link,
            'comments' => $comments,
            'isAdmin'  => $this->auth->getUser() && ($video->user_id === $this->auth->getUser()->id)
        ];

        return $this->twig->render($response, 'app/videoComments.twig', $data);
    }

    public function addComment(Request $request, Response $response, $id)
    {
        if (!$sequence = Sequence::find($id)) {
            $this->flash('danger', 'You are trying to comment on a non-existing sequence.');

            return $this->redirect($response, 'home');
        }

        $text = $request->getParsedBody()['comment'];

        $comment = new Comment();
        $comment->comment = filter_var($text, FILTER_DEFAULT);
        $comment->sequence_id = $id;
        $comment->save();

        $this->flash('success', 'Your comment has been successfully sent.');

        return $this->redirect($response, 'home');
    }

    public function deleteComment(Request $request, Response $response, $id)
    {
        if (!$comment = Comment::find($id)) {
            $this->flash('success', 'The comment you are trying to delete does not seem to exist.');

            return $this->redirect($response, 'home');
        }

        $id_sequence = $comment->sequence->id;
        $comment->delete();

        $this->flash('success', 'The comment has been successfully deleted.');

        $url = $this->router->pathFor('comments', ['id' => $id_sequence]);

        return $response->withStatus(200)->withHeader('Location', $url);
    }

    public function dashboard(Request $request, Response $response, $id)
    {
        if ($request->isPost()) {
            $data = json_decode($_POST['data']);
            
            $tmpId = [];
            foreach($data as $seq)
            {
                $newSeq = new Sequence();
                $newSeq->start = $seq->start;
                $newSeq->end = $seq->end;
                $newSeq->video_id = $id;
                
                $idSeq = explode('_', $seq->id);

                if($idSeq[0] === 'db')
                {
                    // Cas où le label vient de la db
                    $newSeq->label_id = $idSeq[1];
                }
                else
                {
                    // Cas où le label a été créé dynamiquement
                    if(! array_key_exists($idSeq[1], $tmpId))
                    {
                        // Cas ou le label n'existe pas encore
                        // On crée le label en bdd
                        $newLabel = new Label();
                        $newLabel->expression = $seq->content;
                        $newLabel->save();

                        // On l'attribue à notre nouvelle séquence
                        $newSeq->label_id = $newLabel->id;

                        // On l'ajoute à la liste des id tmp
                        $tmpId[$idSeq[1]] = $newLabel->id;
                    }
                    else
                    {
                        // Cas ou on a créer le label lors d'une itération précédente
                        $newSeq->label_id = $tmpId[$idSeq[1]];                        
                    }
                    
                }

                $newSeq->save();
            }

            if(sizeof($data) > 1)
                $this->flash('success', 'Vos séquences ont été sauvegardé.');
            else
                $this->flash('success', 'Votre séquence a été sauvegardé.');

            return $this->redirect($response, 'profile');
        }

        // Checking if the video exists
        if (!$video = Video::find($id)) {
            $this->flash('danger', 'The video you are trying to access does not seem to exist.');

            return $this->redirect($response, 'profile');
        }

        // Checking if the video belongs to the user trying to create a sequence
        $current_user = $this->auth->getUser();

        if (!$video->user_id === $current_user->id) {
            $this->flash('danger', 'The video you are trying to access does not seem to belong to you.');

            return $this->redirect($response, 'profile');
        }

        $labels_data = Label::all();
        $labels = [];
        foreach($labels_data as $label)
        {
            $labels[] = [
                'id' => 'db_' . $label->id,
                'expression' => $label->expression
            ];
        }

        return $this->twig->render(
            $response,
            'app/dashboard.twig',
            [
                'id'   => $video->id,
                'name' => $video->name,
                'link' => $video->link,
                'labels' => $labels
            ]
        );
    }
}