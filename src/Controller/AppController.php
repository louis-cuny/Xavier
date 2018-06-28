<?php

namespace App\Controller;

use Awurth\Slim\Helper\Controller\Controller;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;
use Cartalyst\Sentinel\Sentinel;
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

        $videos = Video::where('estVisible', '=', 1)->get();
        $videos_data = [];
        for ($i = 0; $i < sizeof($videos); $i++) {
            array_push($videos_data, $videos[$i]->id);
        }

        $nb = 5;
        $max_nb = count($sequences) < $nb ? count($sequences) : $nb;
        for ($i = 0; $i < $max_nb; $i++) {
            $seq_data = [
                "id"   => $sequences[$i]->id,
                "name" => $sequences[$i]->video->name,
                "video_id" => $sequences[$i]->video->id,
                "timing" => $sequences[$i]->start . ' - ' . $sequences[$i]->end,
                "duration" => $sequences[$i]->end - $sequences[$i]->start 
                
            ];

            //if($sequences[$i]->video->estVisible){
                array_push($sequences_data, $seq_data);
            /*}else{
                if($this->auth->getUser()){
                    array_push($sequences_data, $seq_data);
                }
            }*/
        }

        $data = [
            "rand_id"   => count($sequences) > 0 ? $sequences[rand(0, count($sequences) - 1)]->id : null,
            "sequences" => $sequences_data,
            "videos" => $videos_data
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

        $commentaires = Comment::where('user_id', '=', $user_id)->orderBy('id', 'desc')->get();

        $data =
            [
                "videos" => $videos_data,
                "commentaires" => $commentaires
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

                $this->flash('success', 'La vidéo a été supprimée avec succès.');
            } else {
                $this->flash('danger', 'La vidéo que vous essayez de supprimer ne semble pas exister.');
            }
        } else {
            $this->flash('danger', 'La vidéo que vous essayez de supprimer ne semble pas vous appartenir.');
        }

        return $this->redirect($response, 'profile');
    }

    public function renameVideo(Request $request, Response $response, $id)
    {
        if ($user = $this->auth->getUser()) {
            $video = Video::find($id);

            if ($video && $video->user->id === $user->id) {
                if(!is_null($request->getParsedBody()['newName'])){
                    $video->name = filter_var($request->getParsedBody()['newName'], FILTER_DEFAULT);
                }
                $video->estVisible = $request->getParsedBody()['isVisible'];
                $video->update();

                $this->flash('success', 'La vidéo a été modifiée avec succès.');
            } else {
                $this->flash('danger', 'La vidéo que vous essayez de changer ne semble pas exister.');
            }
        } else {
            $this->flash('danger', 'La vidéo que vous essayez de changer ne semble pas vous appartenir.');
        }

        return $this->redirect($response, 'profile');
    }

    public function deleteSequence(Request $request, Response $response, $id)
    {
        if (!$sequence = Sequence::find($id)) {
            $this->flash('danger', 'La séquence que vous essayez de supprimer ne semble pas exister.');

            return $this->redirect($response, 'profile');
        }

        $video = $sequence->video;
        if (!$video->user->id === $this->auth->getUser()->id) {
            $this->flash('danger', 'La séquence que vous essayez de suppriler ne semble pas vous appartenir.');

            return $this->redirect($response, 'profile');
        }

        $sequence->delete();

        $this->flash('success', 'The sequence has been successfully deleted.');

        return $this->redirect($response, 'profile');
    }

    public function displayComments(Request $request, Response $response, $id)
    {

        $usr = $this->auth->getUser();
        if($usr != null){
            $user_id = $this->auth->getUser()->id;
        }else{
            $user_id = null;
        }

        if (!$seq = Sequence::find($id)) {
            $this->flash('danger', 'La séquence que vous essayez de regarder ne semble pas exister.');

            return $this->redirect($response, 'home');
        }

        $video = $seq->video;

        $videos = Video::where('estVisible', '=', 1)->get();
        $videos_data = [];
        for ($i = 0; $i < sizeof($videos); $i++) {
            array_push($videos_data, $videos[$i]->id);
        }

        if (is_null($user_id) && !in_array($seq->video->id, $videos_data)) {
            $this->flash('danger', 'La séquence que vous essayez de regarder ne vous est pas accessible.');

            return $this->redirect($response, 'home');
        }

        $comments = [];

        $commented = false;
        $your_comment = '';

        foreach ($seq->comments as $com) {
            array_push($comments, ["id" => $com->id, "comment" => $com->comment]);
            if($com->user_id == $user_id && !is_null($user_id)){
                $commented = true;
                $your_comment = $com->comment;
            }
        }

        $data = [
            'id'       => $seq->id,
            'start'    => $seq->start,
            'end'      => $seq->end,
            'video_name' => $seq->video->name,
            'name' => $seq->label->expression,
            'link'     => $video->link,
            'comments' => $comments,
            'isAdmin'  => $this->auth->getUser() && ($video->user_id === $this->auth->getUser()->id),
            'commented' => $commented,
            'your_comment' => $your_comment
        ];

        return $this->twig->render($response, 'app/videoComments.twig', $data);
    }

    public function addComment(Request $request, Response $response, $id)
    {

        $usr = $this->auth->getUser();
        if($usr != null){
            $user_id = $this->auth->getUser()->id;
        }else{
            $user_id = null;
        }
        
        $videos = Video::where('estVisible', '=', 1)->get();
        $videos_data = [];
        for ($i = 0; $i < sizeof($videos); $i++) {
            array_push($videos_data, $videos[$i]->id);
        }

        if (!$sequence = Sequence::find($id)) {
            $this->flash('danger', "Vous essayez de commenter une séquence qui n'existe pas.");

            return $this->redirect($response, 'home');
        }

        $vid = $sequence->video_id;
        $vid_user = $sequence->video;

        if ($user_id == $vid_user->user_id) {
            $this->flash('danger', "Vous essayez de commenter votre séquence.");

            return $this->redirect($response, 'home');
        }

        if (is_null($user_id) && !in_array($vid, $videos_data)) {
            $this->flash('danger', 'La séquence que vous essayez de commenter ne vous est pas accessible.');

            return $this->redirect($response, 'home');
        }

        $text = $request->getParsedBody()['comment'];

       //$current_user = $this->auth->getUser();

        foreach ($sequence->comments as $com) {
            if($com->user_id == $user_id && $user_id != null){
                $this->flash('danger', "Vous essayez de commenter plusieurs fois une même séquence.");

                return $this->redirect($response, 'home');
            }
        }

        $comment = new Comment();
        $comment->comment = filter_var($text, FILTER_DEFAULT);
        $comment->sequence_id = $id;
        $comment->user_id =  $user_id;
        $comment->save();

        $this->flash('success', 'Votre commentaire a été envoyé avec succès.');

        return $this->redirect($response, 'home');
    }

    public function deleteComment(Request $request, Response $response, $id)
    {
        if (!$comment = Comment::find($id)) {
            $this->flash('success', "Le commentaire que vous essayez de supprimer n'a pas l'air d'exister.");

            return $this->redirect($response, 'home');
        }

        $id_sequence = $comment->sequence->id;
        $comment->delete();

        $this->flash('success', 'Le commentaire a été supprimer avec succès.');

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
            $this->flash('danger', "La vidéo n'a pas l'air d'exister.");

            return $this->redirect($response, 'profile');
        }

        // Checking if the video belongs to the user trying to create a sequence
        $current_user = $this->auth->getUser();

        if (!$video->user_id === $current_user->id) {
            $this->flash('danger', "La vidéo n'a pas l'air de vous appartenir.");

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
        $xml = new \XMLWriter();
        $xml->openMemory();
        $xml->openURI("php://output");
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('Vocabulaire');
        foreach($_POST as $key => $value) {
            if($key != 'csrf_name' && $key != 'csrf_value'){
                $xml->startElement('Expression');
                    $xml->startElement('Pseudo-langage');
                         $xml->writeRaw($key);
                    $xml->endElement();
                if(is_array($value)){
                     for($i = 0 ; $i < count($value) ; $i++){
                        $xml->startElement('Synonyme');
                             $xml->writeRaw($value[$i]);
                        $xml->endElement();
                     }
                }else{
                    $xml->startElement('Synonyme');
                        $xml->writeRaw($value);
                    $xml->endElement();
                }
                $xml->endElement();    
            }
        }
        $xml->endElement();

        $xml->flush();
        $xml->endDocument(); 
        header('Content-type: text/xml');
        header('Content-Disposition: attachment; filename=xavier_export.xml');
        echo $xml->outputMemory();
        
    }

    public function ensemble(Request $request, Response $response)
    {

        $sequences = Sequence::orderBy('created_at', 'desc')->get();
        $sequences_data = [];

        for ($i = 0; $i < sizeof($sequences); $i++) {
            $seq_data = [
                "id"   => $sequences[$i]->id,
                "name" => $sequences[$i]->video->name,
                "video_id" => $sequences[$i]->video->id,
                "timing" => $sequences[$i]->start . ' - ' . $sequences[$i]->end,
                "duration" => $sequences[$i]->end - $sequences[$i]->start 
                
            ];

                array_push($sequences_data, $seq_data);
        }

        $data = [
            "sequences" => $sequences_data
        ];

        return $this->twig->render($response, 'app/ensemble.twig', $data);
    }

    public function admin(Request $request, Response $response)
    {

        $users = $this->auth->getUserRepository()->with('roles')->get();
        $users_data = [];

        foreach($users as $key => $value) {
            $usr_data = [
                "user"   => $value 
            ];

                array_push($users_data, $usr_data);
        }
        
        $data = [
            "tab" => $users_data
        ];

        return $this->twig->render($response, 'app/admin.twig', $data);
    }

    public function changeAdmin(Request $request, Response $response)
    {
        $role = $_Post['role'];
        $roleDel = 0;

        if ($role == 1){
            $roleDel = 2;
        }else{
            $roleDel = 1;
        }

        $user = $this->auth->findById($_POST['id']);
        $roleAjout = $this->auth->findRoleById($role);
        $roleSuppr = $this->auth->findRoleById($roleDel);

        $user->roles()->detach($roleSuppr);
        $user->roles()->attach($roleAjout);

       /* $users_data = [];

        foreach($users as $key => $value) {
            $usr_data = [
                "user"   => $value 
            ];

                array_push($users_data, $usr_data);
        }
        
        $data = [
            "tab" => $users_data
        ];*/

        return $this->twig->render($response, 'app/admin.twig', $data);
    }

}