<?php

namespace KnpU\CodeBattle\Controller\Api;

use KnpU\CodeBattle\Controller\BaseController;
use KnpU\CodeBattle\Model\Programmer;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProgrammerController extends BaseController
{
    protected function addRoutes(ControllerCollection $controllers)
    {
        $controllers->post('/api/programmers', array($this, 'newAction'));
        $controllers->get('/api/programmers', array($this, 'listAction'));
        $controllers->get('/api/programmers/{nickname}', array($this, 'showAction'))
        ->bind('api_programmers_show');
        $controllers->put('/api/programmers/{nickname}', array($this, 'updateAction'));
        $controllers->delete('/api/programmers/{nickname}', array($this, 'deleteAction'));
    }

    public function newAction(Request $request)
    {
        $programmer = new Programmer();

        $this->handleRequest($request, $programmer);

        $url = $this->generateUrl('api_programmers_show', array(
           'nickname' =>$programmer->nickname,
        ));

        $data = $this->serializeProgrammer($programmer);

        $response = new Response(json_encode($data), 201);
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Location', $url);

        return $response;
    }

    public function updateAction(Request $request, $nickname)
    {
        $programmer = $this->getProgrammerRepository()
            ->findOneByNickname($nickname);

        if(!$programmer){
            $this->throw404('Oh no! this programmer has deserted! We\'ll send a search party!');
        }

       $this->handleRequest($request, $programmer);
        $data = $this->serializeProgrammer($programmer);

        $response = new Response(json_encode($data), 200);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function showAction($nickname)
    {
        $programmer = $this->getProgrammerRepository()
            ->findOneByNickname($nickname);

        if(!$programmer){
            $this->throw404('Oh no! this programmer has deserted! We\'ll send a search party!');
        }

        $data = $this->serializeProgrammer($programmer);

        $response = new Response(json_encode($data), 200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function deleteAction($nickname)
    {
        $programmer = $this->getProgrammerRepository()
            ->findOneByNickname($nickname);

        if (!$programmer) {
            $this->throw404('Oh no! this programmer has deserted! We\'ll send a search party!');
        }else{
            $this->delete($programmer);
        }

        return new Response(null, 204);
    }


    public function listAction()
    {
        $programmers = $this->getProgrammerRepository()
            ->findAll();


        $data = array('programmers' => array());

        foreach ($programmers as $programmer){
            $data['programmers'][] = $this->serializeProgrammer($programmer);
        }

        $response = new Response(json_encode($data), 200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    private function serializeProgrammer(Programmer $programmer){
        $data = array(
            'nickname' => $programmer->nickname,
            'avatarNumber' => $programmer->avatarNumber,
            'powerLevel' => $programmer->powerLevel,
            'tagLine' => $programmer->tagLine
        );

        return $data;
    }

    public function handleRequest(Request $request, Programmer $programmer)
    {

        $data = json_decode($request->getContent(), true);

        if($data === null){
            throw new \Exception('Invalid JSON !!!!'.$request->getContent());
        }

        $isNew = !$programmer->id;

        $apiProperty = array('avatarNumber', 'tagLine');

        if($isNew)
        {
            $apiProperty[] = 'nickname';
        }
        foreach ($apiProperty as $property){
            $val = isset($data[$property]) ? $data[$property] : null;
            $programmer->$property = $val;
        }

        $programmer->userId = $this->findUserByUsername('weaverryan')->id;


        $this->save($programmer);

    }

}
