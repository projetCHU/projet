<?php

namespace CHU\AdministartionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use CHU\PlatformBundle\Entity\Compte;
use CHU\PlatformBundle\Entity\Salarie;
use CHU\PlatformBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request; 
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;


class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('CHUAdministartionBundle:Default:index.html.twig');
        
    }
    
    
    
    
     public function  creerCompteAction(Request $request) {
        
       
        if ($request->isMethod('GET')) {
            return $this->render('CHUAdministartionBundle:Default:creerCompte.html.twig');
        } else if ($request->isMethod('POST')) {
            $role = $request->request->get("role");
            $doctrine = $this->getDoctrine()->getManager();
        
            
            if($role == "ROLE_SALARIE") {
               /* $compte = $this->generateCompte($role);
                return $this->render('CHUAdministartionBundle:Default:creerCompte.html.twig', array("code" => $compte->getCode()));
            */
            
            $current_user = $this->getUser();
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
                return $this->render('CHUAdministartionBundle:Default:creerCompte.html.twig', array("code" => $compte->getCode()));
            
            
            }
            else{

                $mails=$this->getEmails($request->request->get("_email"));
                if(sizeof($mails)>0) {
                    
                    foreach($mails as $mail) {
                        $compte = $this->generateCompte($role);
                        $compte->setEmail($mail);
                        $doctrine->persist($compte);
                        $this->sendMessageTo($mail, $compte->getCode());
                    }
                    $doctrine->flush();
                    $confirmer="votre compte à été crée et renvoyé à l'adresse: ".json_encode($mails);
                    return $this->render('CHUAdministartionBundle:Default:creerCompte.html.twig', array("mail" =>$confirmer));
                } else {
                    return $this->render('CHUAdministartionBundle:Default:creerCompte.html.twig', array("error" =>"Mail invalid"));
                }
                
                
            }
        }  
    }
    
    private function getEmails($email) {
        $mails = explode("\n", $email);
        $emails = array();
        foreach($mails as $mail) {
            if(!empty($mail) && filter_var($mail, FILTER_VALIDATE_EMAIL) ) {
                $emails[] = $mail;
            }
        }
        return $emails;
    }
    
    private function sendMessageTo($to, $code) {
        
        $url = $this->generateUrl("registration")."?token=".$code;
        $message = \Swift_Message::newInstance()
                        ->setSubject('Validation d\'inscription')
                        ->setFrom(array('chuangers1@gmail.com'=>'Insctiption no-reply' ))
                        ->setTo($to)
                        ->setCharset('utf-8')
                        ->setContentType('text')
                        ->setBody("E-mail:".$to."\n"."mot de passe:".$code."\n"."pour modifier votre mot de passe: ". $url);
                        //->setBody("Inscrivez-vous sur ce url ". $url);
        $this->get('mailer')->send($message);
    }
    
    private function generateCompte($role) {
        $compte = new Compte();
        $compte->setType($role);
        $compte->setCode(uniqid());
        $doctrine = $this->getDoctrine()->getManager();
        $doctrine->persist($compte);
        $doctrine->flush();
        
        return $compte;
    }
    
   
    
    public function afficherEtudesAction(){
        return $this->render('CHUAdministartionBundle:Default:afficher_etudes.html.twig');
        
        
    }
    
    
    public function afficherMedecinsAction()
   {
       $repository = $this->getDoctrine()->getManager()->getRepository('CHUPlatformBundle:User');
       $listUtilisateurs = $repository->findAll();
   
      return $this->render('CHUAdministartionBundle:Default:afficher_patients.html.twig', array("utilisateurs"=>$listUtilisateurs));
                                 }
    
    
    public function supprimerUserAction(Request $request){
       if ($request->isMethod('GET')) {
           
            return $this->render('CHUAdministartionBundle:Default:supprimer_user.html.twig');
        }
       else{
           $em=$this->getDoctrine()->getManager();
           $user = $request->request->get("codePatient");
             $repository = $this->getDoctrine()->getManager()->getRepository('CHUPlatformBundle:User');
             
       $listUtilisateurs = $repository->findAll();
       
           foreach($listUtilisateurs as $utilisateur){
               if($utilisateur->getUsername()==$user)
                   $em->remove($utilisateur);
                   $em->flush();
           }
           
           
       }
       
      return $this->render('CHUAdministartionBundle:Default:supprimer_user.html.twig');
       
   }
    
    
}


