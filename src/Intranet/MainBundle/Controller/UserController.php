<?php

namespace Intranet\MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Intranet\MainBundle\Entity\User;
use Intranet\MainBundle\Entity\UserSettings;
use Intranet\MainBundle\Entity\Document;
use Intranet\MainBundle\Entity\Office;
use Intranet\MainBundle\Entity\Role;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function addUserAction(Request $request)
    {
    	$parameters = array(
    		'name' => $request->request->get('name'),
    		'surname' => $request->request->get('surname'),
    		'email' => $request->request->get('email'),
    		'username' => $request->request->get('username'),
    		'password' => $request->request->get('password'),
    		'avatar' => $request->request->get('avatar')
    	);
    	
    	$em = $this->getDoctrine()->getManager();
    	
    	if (User::isRegisteredByEmail($em, $parameters['email']) != null)
    	{
    		$request->getSession()->set('register_error', 'User with email: "'.$parameters['email'].'" is already registered!');
    		$request->getSession()->set('register_user', $parameters);
    		return $this->redirect($this->generateUrl('intranet_security')."#register");
    	}
    	elseif (User::isRegisteredByUsername($em, $parameters['username']) != null)
    	{
    		$request->getSession()->set('register_error', 'User with username: "'.$parameters['username'].'" is already registered!');
    		$request->getSession()->set('register_user', $parameters);
    		return $this->redirect($this->generateUrl('intranet_security')."#register");
    	}
        
    	//create new user
    	$user = new User();
    	$factory = $this->get('security.encoder_factory');
    	$encoder = $factory->getEncoder($user);
    	$user->setName($parameters['name']);
    	$user->setSurname($parameters['surname']);
    	$user->setEmail($parameters['email']);
    	$user->setUsername($parameters['username']);
    	$user->setPassword($encoder->encodePassword($parameters['password'], $user->getSalt()));
    	$user->setRegistered(new \DateTime());
    	$user->setLastActive(new \DateTime());
    	$user->setAvatar('eleven.png');
    	$user->addRole(Role::getUserRole($em));
    	
    	//add to public office
    	$tree = Office::getOfficeTree($em);
    	$publicOffice = $tree[0];
    	
    	$user->addOffice($publicOffice);
    	$publicOffice->addUser($user);
    	
    	$em->persist($publicOffice);
    	$em->persist($user);
    	$em->flush();
    	
    	$settings = new UserSettings();
    	$settings->setUserid($user->getId());
    	$settings->setUser($user);
    	$settings->setShowHiddenTopics(true);
    	$settings->setDisableAllOnEmail(false);
    	$settings->setDisableAllOnSite(false);
    	
    	$em->persist($settings);
    	$em->flush();
    	
    	//set session
    	User::forceLogin($user, 'secured_area', $this->get('security.context'), $request);
    	
    	return $this->redirect($this->generateUrl('intranet_main_homepage'));
    }
    
    public function getTopicMembersAction($topic_id)
    {
    	$em = $this->getDoctrine()->getManager();
    
    	$response = new Response(json_encode(array("result" => User::getTopicMembers($em, $topic_id, true))));
    	$response->headers->set('Content-Type', 'application/json');
    
    	return $response;
    }
    
    public function getOfficeMembersAction($office_id)
    {
    	$em = $this->getDoctrine()->getManager();
    
    	$response = new Response(json_encode(array("result" => User::getOfficeMembers($em, $office_id, true))));
    	$response->headers->set('Content-Type', 'application/json');
    
    	return $response;
    }
}
