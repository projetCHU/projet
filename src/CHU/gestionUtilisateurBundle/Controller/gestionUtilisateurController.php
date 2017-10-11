<?php

// /src/CHU/gestionUtilisateurBundle/Controller/gestionUtilisateurController.php

namespace CHU\gestionUtilisateurBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use CHU\gestionUtilisateurBundle\Entity\Utilisateur;

class gestionUtilisateurController extends Controller
{
    // PARAMETRES
    const PARAM_USERNAME = 'username';
    const PARAM_PASSWORD = 'password';
    const PARAM_NOM = "nom";
    const PARAM_PRENOM = "prenom";
    const PARAM_FONCTION = "fonction";
    const PARAM_DATE_NAISSANCE = "dateNaissance";
    const PARAM_ADRESSE_MAIL = "adresseMail";
    const PARAM_CONFIRM_PASSWORD = "confirmPassword";

    // VUES
    const VUE_PAGE_LOGIN = 'CHUgestionUtilisateurBundle:gestionUtilisateur:login.html.twig';
    const VUE_PAGE_CREATION_UTILISATEUR = 'CHUgestionUtilisateurBundle:gestionUtilisateur:enregistrement.html.twig';
    const VUE_PAGE_ACCUEIL = 'CHUgestionUtilisateurBundle:gestionUtilisateur:accueil.html.twig';
    const VUE_PAGE_RECUPERATION_MDP = 'CHUgestionUtilisateurBundle:gestionUtilisateur:recuperation_mdp.html.twig';

    // ROUTES
    const ROUTE_PAGE_ACCUEIL = 'chu_gestion_utilisateur_accueil';
    const ROUTE_PAGE_LOGIN = 'chu_gestion_utilisateur_page_login';

    // MESSAGES
    const NOT_LOGGED_MESSAGE = "Vous devez d'abord vous connecter.";

    public function indexAction()
    /*
     * Affiche la page de login
     */
    {
      $content = $this->get('templating')->render(self::VUE_PAGE_ACCUEIL);
      return new Response($content);
    }

    public function pageCreationUtilisateurAction()
    /*
     * Affiche la page de création d'utilisateur
     */
    {
      $content = $this->get('templating')->render(self::VUE_PAGE_CREATION_UTILISATEUR);
      return new Response($content);
    }

    public function pageLoginAction(Request $request)
    /*
     * Affiche la page de création d'utilisateur
     */
    {

      $content = $this->get('templating')->render(self::VUE_PAGE_LOGIN);
      return new Response($content);
    }

    public function recuperationAction()
    /*
     * Affiche la page de récupération de mot de passe
     */
    {
      $content = $this->get('templating')->render(self::VUE_PAGE_RECUPERATION_MDP);
      return new Response($content);
    }

    public function creationUtilisateurAction(Request $request)
    /*
     * Gère la création de compte d'un utilisateur
     */
    {
      if($request->isMethod('POST')){
        $username = $request->request->get(self::PARAM_USERNAME);
        $password = $request->request->get(self::PARAM_PASSWORD);
        $confirm_password = $nom = $request->request->get(self::PARAM_CONFIRM_PASSWORD);
        $prenom = $request->request->get(self::PARAM_PRENOM);
        $nom = $request->request->get(self::PARAM_NOM);
        $date_naissance = $request->request->get(self::PARAM_DATE_NAISSANCE);
        $adresse_mail = $request->request->get(self::PARAM_ADRESSE_MAIL);
        $fonction = $request->request->get(self::PARAM_FONCTION);

        $user = new Utilisateur();
        $user->setUsername($username)
             ->setPassword($password)
             ->setPrenom($prenom)
             ->setNom($nom)
             ->setDateNaissance($date_naissance)
             ->setAdresseMail($adresse_mail)
             ->setFonction($fonction);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute(self::ROUTE_PAGE_ACCUEIL);
      }

      throw new NotFoundHttpException(self::NOT_LOGGED_MESSAGE);
    }

    public function loginAction(Request $request)
    /*
     * Gère la connection d'un utilisateur
     */
    {
      if($request->isMethod('POST')){
        $username = $request->request->get(self::PARAM_USERNAME);
        $password = $request->request->get(self::PARAM_PASSWORD);

        $utilisateur_repo = $this->getDoctrine()->getManager()->getRepository('CHUgestionUtilisateurBundle:Utilisateur');

        $user = $utilisateur_repo->findOneBy(array(self::PARAM_USERNAME => $username, self::PARAM_PASSWORD => $password));

        if($user === null){
          $session = $request->getSession();
          $session->getFlashBag()->add('info',"Erreur lors de l'identification");
          return $this->redirectToRoute(self::ROUTE_PAGE_LOGIN);
        }

        return $this->redirectToRoute(self::ROUTE_PAGE_ACCUEIL);

      }

      throw new NotFoundHttpException("Vous devez vous connecter d'abord");
    }

    public function deconnectionAction(Request $request)
    {
      if($request->isMethod('POST')){
        return $this->redirectToRoute(self::ROUTE_PAGE_ACCUEIL);
      }

      throw new NotFoundHttpException(self::NOT_LOGGED_MESSAGE);
    }
}
