<?php

namespace CHU\MedecinBundle\Controller;

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
        return $this->render('CHUMedecinBundle:Default:index.html.twig');
    }
    
    
    
         public function  creer_CompteAction(Request $request) {  
       
  
    if ($request->isMethod('GET')) {
   return $this->render('CHUMedecinBundle:Default:creer_compte.html.twig');
  } else if ($request->isMethod('POST')) {
   $role = $request->request->get("role");
   if($role != null) {
    $compte = new Compte();
    $compte->setType($role);
    $compte->setCode(uniqid());
    $doctrine = $this->getDoctrine()->getManager();
    $doctrine->persist($compte);
    $doctrine->flush();
    return $this->render('CHUMedecinBundle:Default:creer_compte.html.twig', array("code" => $compte->getCode()));
   }
  }  
   
    }
    
     public function ajouterPatientAction()
    {
        return $this->render('CHUMedecinBundle:Default:ajouter_patient.html.twig');
    }
    
     public function afficherEtudesAction()
    {
        return $this->render('CHUMedecinBundle:Default:afficher_etudes.html.twig');
    }
    
     public function afficherPatientsAction()
    {
        return $this->render('CHUMedecinBundle:Default:afficher_patients.html.twig');
    }
    
     public function affecterEtudeAction()
    {
        return $this->render('CHUMedecinBundle:Default:affecter_etude.html.twig');
    }
    
    
    
    
    
    
}
