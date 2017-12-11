<?php

namespace CHU\InfirmierBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CHU\PlatformBundle\Entity\Compte;
use CHU\PlatformBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request; 
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;


class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('CHUInfirmierBundle:Default:index.html.twig');
    }
    
     public function  creerCompteAction(Request $request) {  
       
  
    if ($request->isMethod('GET')) {
   return $this->render('CHUInfirmierBundle:Default:creerCompte.html.twig');
  } else if ($request->isMethod('POST')) {
   $role = $request->request->get("role");
   if($role != null) {
    $compte = new Compte();
    $compte->setType($role);
    $compte->setCode(uniqid());
    $doctrine = $this->getDoctrine()->getManager();
    $doctrine->persist($compte);
    $doctrine->flush();
    return $this->render('CHUInfirmierBundle:Default:creerCompte.html.twig', array("code" => $compte->getCode()));
   }
  }  
   
    }
}
