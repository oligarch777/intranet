<?php

namespace Intranet\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Intranet\MainBundle\Entity\Post;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class PostController extends Controller
{
	public function getPostsCountAction($topic_id)
	{
		$em = $this->getDoctrine()->getEntityManager();
		return new Response(var_dump(Post::getPostsCount($em, $topic_id)));
	}
	
	public function getPostsMembersAction($topic_id)
	{
		
	}
	
	public function getNewPostsAction(Request $request, $topic_id)
	{
		
	}
	
	public function getPostsAction(Request $request, $topic_id)
    {
    	$offset = ($request->query->get('offset')) ? $request->query->get('offset') : 0 ;
    	$limit = ($request->query->get('limit')) ? $request->query->get('limit') : 20 ;
    	
    	$repository = $this->getDoctrine()->getRepository('IntranetMainBundle:Post');
    	
    	$posts = Post::getPostsByTopicId($repository, $topic_id, $offset, $limit);
    	
    	$response = new Response(json_encode(array("result" => $posts)));
    	$response->headers->set('Content-Type', 'application/json');
    	
        return $response;
    }
    
    public function addPostAction($topic_id)
    {
    	$em = $this->getDoctrine()->getManager();
    	
    	//$data = $request->request->all();
    	$data = json_decode(file_get_contents("php://input"));
    	$post = (object) $data;
    	
    	$added = Post::addPostByTopicId($em, $post);
    	
    	$response = new Response(json_encode(array("result" => $added)));
    	$response->headers->set('Content-Type', 'application/json');
    	
    	return $response;
    }
}
