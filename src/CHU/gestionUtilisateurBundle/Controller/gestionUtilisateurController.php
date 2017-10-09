<?php

// /src/CHU/gestionUtilisateurBundle/Controller/gestionUtilisateurController.php

namespace CHU\gestionUtilisateurBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class gestionUtilisateurController extends Controller
{
    // PARAMETRES DE LOGIN
    const PARAM_USERNAME = 'uname';
    const PARAM_PASSWORD = 'psw';

    // PARAMETRES DE CREATION COMPTE
    const PARAM_LASTNAME_USER = "nom";
    const PARAM_FIRSTNAME_USER = "prenom";

    // VUES
    const VUE_PAGE_LOGIN = 'CHUgestionUtilisateurBundle:LoginPages:login.html.twig';
    const VUE_PAGE_CREATION_UTILISATEUR = 'CHUgestionUtilisateurBundle:CreationUtilisateur:creationUtilisateur.html.twig';
    const VUE_PAGE_ACCUEIL = 'CHUgestionUtilisateurBundle:PagesUtilisateur:accueil.html.twig';

    // VARIABLES VUES
    const VAR_USERNAME = 'username';
    const VAR_PASSWORD = 'password';
    const VAR_FIRSTNAME = 'firstname';
    const VAR_LASTNAME = 'lastname';

    // ROUTES
    const ROUTE_PAGE_LOGIN = 'chu_gestion_utilisateur_page_login';

    // MESSAGES
    const NOT_LOGGED_MESSAGE = "Vous devez d'abord vous connecter.";

    public function indexAction()
    /*
     * Affiche la page de login
     */
    {
      $content = $this->get('templating')->render(self::VUE_PAGE_LOGIN);
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

    public function creationUtilisateurAction(Request $request)
    /*
     * Gère la création de compte d'un utilisateur
     */
    {
      if($request->isMethod('POST')){
        $username = $request->request->get(self::PARAM_USERNAME);
        $password = $request->request->get(self::PARAM_PASSWORD);
        $firstname = $request->request->get(self::PARAM_FIRSTNAME_USER);
        $lastname = $request->request->get(self::PARAM_LASTNAME_USER);

        $param = array(
          self::VAR_USERNAME => $username,
          self::VAR_PASSWORD => $password,
          self::VAR_FIRSTNAME => $firstname,
          self::VAR_LASTNAME => $lastname
        );

        $content = $this->get('templating')->render(self::VUE_PAGE_ACCUEIL,$param);
        return new Response($content);
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

        $param = array(
          self::VAR_USERNAME => $username,
          self::VAR_PASSWORD => $password
        );

        $content = $this->get('templating')->render(self::VUE_PAGE_ACCUEIL,$param);
        return new Response($content);
      }

      throw new NotFoundHttpException("Vous devez vous connecter d'abord");
    }

    public function deconnectionAction(Request $request)
    {
      if($request->isMethod('POST')){
        return $this->redirectToRoute(self::ROUTE_PAGE_LOGIN);
      }

      throw new NotFoundHttpException(self::NOT_LOGGED_MESSAGE);
    }
}
