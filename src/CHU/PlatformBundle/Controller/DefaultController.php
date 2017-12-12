<?php

namespace CHU\PlatformBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Model\UserManagerInterface;
use CHU\PlatformBundle\Entity\Salarie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class DefaultController extends Controller
{
    public function indexAction()
        
    {/*
        $message = \Swift_Message::newInstance()
                            ->setSubject('Validation d\'inscription')
                            ->setFrom(array('test@test.com'=>'Insctiption no-reply' ))
                            ->setTo("ash.rachid1@gmail.com")
                            ->setCharset('utf-8')
                            ->setContentType('text\html')
                            ->setBody("test message");
				$this->get('mailer')->send($message);*/
        return $this->render('CHUPlatformBundle:Default:index.html.twig');
    }
    
    public function loginAction(Request $request)
        
        
    {
        
        
        
        $nom=$request->request->get('usr');
        $pass=$nom=$request->request->get('pass');
        //echo  $nom;
        //echo "</br>";
        //echo  $pass;
        return $this->render('CHUPlatformBundle:Default:login.html.twig');
    }
    
    public function passOublierAction(Request $request)
        
        
    {  
        $email=$request->request->get('email');
        
        
        //echo  $email;
       
        return $this->render('CHUPlatformBundle:Default:passOublier.html.twig');
    }
    
     public function validerIscriptionAction(Request $request)
        
        
    {  
        $codeIndcription=$request->request->get('codeIndcription');
        
        
        //echo  $codeIndcription;
       
        return $this->render('CHUPlatformBundle:Default:validerInscription.html.twig');
    }
          
   public function enregistrementAction(Request $request) {  
 
   if ($request->isMethod('GET')) {
       
       $token = $request->query->get('token');
       if($token != null) {
            $compte = $this->getCompte($token);
           if($compte != null) {
               return $this->render('CHUPlatformBundle:Default:enregistrement.html.twig', array("code" =>  $compte->getCode()));
           }
       }
       return $this->render('CHUPlatformBundle:Default:enregistrement.html.twig');

   } else if ($request->isMethod('POST')) { 
   
        $email=$request->request->get('_email');
        $pass=$request->request->get('_password');
        $code = $request->request->get('code');
        $compte = ($code == null) ? null : $this->getCompte($code);
        $doctrine = $this->getDoctrine()->getManager();
       
       
        if($email == null && $compte != null) { $email = $compte->getEmail(); } 
        if($compte != null && $email != null) {
            $user = $this->createUser($email, $pass, $compte);
            
                if($compte->getType() == "ROLE_SALARIE") {$patient = new Salarie();
                   $patient->setUser($user);}
            

               
            return $this->render('CHUPlatformBundle:Default:enregistrement.html.twig');
        } else {

             if($compte != null) {
                 $isPatient = ($compte->getType() == "ROLE_SALARIE") ? true : false;
                 if($isPatient) {
                     $user = $this->getDoctrine()
                        ->getRepository('CHUPlatformBundle:User')
                        ->findOneBy(['username' => $compte->getCode()]);
                     
                     $isValid = $this->get('security.password_encoder')
                        ->isPasswordValid($user, $compte->getCode());
 
                    if (!$isValid) {
                        throw new BadCredentialsException();
                    }
                     
                    $token = new UsernamePasswordToken($user, $compte->getCode(), "main", array($compte->getType()));
                    $this->get('security.token_storage')->setToken($token);

                    return  new RedirectResponse($this->generateUrl('chu_patient_homepage'));
                    return $this->render('CHUPatientBundle:Default:index.html.twig');
                 } 
                return $this->render('CHUPlatformBundle:Default:enregistrement.html.twig', array("code" =>  $compte->getCode(), "patient" => $isPatient));
             } else {
                return $this->render('CHUPlatformBundle:Default:enregistrement.html.twig');
             }
        }
   }   
    }
    
     public function postConnexionAction()
        
        
    {  
     

       
        return $this->render('CHUPlatformBundle:Default:postConnexion.html.twig', array('user' => 'utilisateur'));
    }
     public function  creerCompteAction()
        
        
    {  
     
        
        
       
       
        return $this->render('CHUPlatformBundle:Default:creerCompte.html.twig');
    }
    
    private function createUser($email, $pass, $compte) {

         $um =  $this->container->get('fos_user.user_manager');
         $_user = $um->createUser();
        
        if($compte->getType() == "ROLE_SALARIE") {
            $_user->setUsername($email);
            $_user->setEmail($email);
            $_user->setEmailCanonical($email);
        } else {
            $_user->setUsername($compte->getEmail());
            $_user->setEmail($compte->getEmail());
            $_user->setEmailCanonical($compte->getEmail());
        }
         
         $_user->setEnabled(1);
         $_user->setPlainPassword($pass);
         $_user->addRole($compte->getType());
         $um->updateUser($_user);
         return $_user;
    }
    
    private function getCompte($code) {
        
        $doctrine = $this->getDoctrine()->getManager();
        $compteRepo = $doctrine->getRepository('CHUPlatformBundle:Compte');
        return $compteRepo->findOneBy(array('code'=> $code));
    }
    
   
    
    
}
