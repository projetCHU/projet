<?php

namespace CHU\MedecinBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CHU\PlatformBundle\Entity\Compte;
use CHU\PlatformBundle\Entity\User;
use CHU\PlatformBundle\Entity\Salarie;
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
       
  
        $doctrine = $this->getDoctrine()->getManager();
        $current_user = $this->getUser();
        
        if ($request->isMethod('GET')) {
            return $this->render('CHUMedecinBundle:Default:creer_compte.html.twig');
        } else if ($request->isMethod('POST')) {
            $role = "ROLE_SALARIE";
            if($role != null) {
                $compte = new Compte();
                $compte->setType($role);
                $compte->setCode(uniqid());
                
                $um =  $this->container->get('fos_user.user_manager');
                $_user = $um->createUser();
                $_user->setUsername($compte->getCode());
                $_user->setEmail($compte->getCode());
                $_user->setEmailCanonical($compte->getCode());
                $_user->setEnabled(1);
                $_user->setPlainPassword($compte->getCode());
                $_user->addRole($role);
                $um->updateUser($_user);
                
                $salarie = new Salarie();
                $salarie->setCompte($compte);
                $salarie->addUser($current_user);
                $salarie->setUserCompte($_user);
                
                $doctrine->persist($compte);
                $doctrine->persist($salarie);
                $doctrine->flush();
                return $this->render('CHUMedecinBundle:Default:creer_compte.html.twig', array("code" => $compte->getCode()));
            }
        }  
   
    }
    
     public function ajouterPatientAction(Request $request)
    {
         if ($request->isMethod('GET')) {
             
             return $this->render('CHUMedecinBundle:Default:ajouter_patient.html.twig');
         }
         else {
             
             $code = $request->request->get("codePatient");
             $current_user = $this->getUser();
             $doctrine = $this->getDoctrine()->getManager();
             $compteRepo = $doctrine->getRepository('CHUPlatformBundle:Compte');
             $patientRepo = $doctrine->getRepository('CHUPlatformBundle:Salarie');
             
             if(!empty($code)) {
                 $compte = $compteRepo->findOneBy(array('code'=> $code));
                 $patient = $patientRepo->findOneBy(array('compte'=>$compte));
                 
                 if($compte !=null && $patient !=null) {
                     $patient->addUser($current_user);
                     $doctrine->persist($patient);
                     $doctrine->flush();
                 }
                 return $this->render('CHUMedecinBundle:Default:ajouter_patient.html.twig', array("message"=> "ok"));
             }
             return $this->render('CHUMedecinBundle:Default:ajouter_patient.html.twig', array("error"=>"no"));
         }
        
         
    }
    
     public function afficherEtudesAction()
    {
        return $this->render('CHUMedecinBundle:Default:afficher_etudes.html.twig');
    }
    
    
     public function affecterEtudeAction()
    {
        return $this->render('CHUMedecinBundle:Default:affecter_etude.html.twig');
    }
    
    
    public function afficherPatientsAction(Request $request)
   {
        if ($request->isMethod('GET')) {
            $current_user = $this->getUser();
            $patients=$current_user->getPatients();
            $codes=array();
            foreach($patients as $patient){
                $codes[]=$patient->getCompte()->getCode();
            }
           // echo $current_user->getPatients()[0]->getId();
            //return new Response(var_dump($current_user->getPatients()));
            return $this->render('CHUMedecinBundle:Default:afficher_patients.html.twig', array("codes"=>$codes));
         
                                 }
    
    }
    
}
