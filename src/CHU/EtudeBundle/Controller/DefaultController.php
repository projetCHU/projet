<?php

namespace CHU\EtudeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Request;

//include des entités nécessaires.
use CHU\EtudeBundle\Entity\Question;
use CHU\EtudeBundle\Entity\Question_Type;
use CHU\EtudeBundle\Entity\Etude;
use CHU\EtudeBundle\Entity\Answer;



/***
PETITES INSTRUCTIONS POUR LA GENERATION DE QUESTIONNAIRE. V.1

Le formBuilder génère en fait un formulaire ( ha bon ? :)),
  c'est à dire le code html de la balise <form> et son contenu.

Pour s'en servir correctement, il faut que le formulaire généré par le formbuilder
  soit configuré correctement, c'est-à-dire:
    <form .... method='post'  action='{{ path('chu_etude_questionnaire_repondu')}}'>

    NE PAS GÉNÉRER la balise de fermeture du formulaire.

Le formulaire généré est ainsi stocké dans le champ 'content' de l'entité 'Etude'.

4 actions différentes concernant le questionnaire déjà créés. Toutes sont gérées dans la methode 'action_questionnaireAction()'.
  1->Afficher le formulaire:
        -On recupère le formulaire et on pense à rajouter la balise de fermeture du formulaire à sa fin.
  2->Répondre au formulaire:
        -On récupère le formulaire,
         on ajoute un input pour indiquer l'id de l'étude,
         on ajoute le bouton submit,
         et enfin on ajoute la balise de fermeture du formulaire.
  3->Supprimer le formulaire:
        -On supprime l'entité. Uniquement si aucune réponse n'est liée.
  4->Voir les réponses au questionnaire:
        -On récupère le formulaire et son id.
         On récupère toutes les entités réponses correspondant à ce formulaire.
         On affiche l'ensemble des réponses.
*/




class DefaultController extends Controller
{

  const VUE_HOMEPAGE = "CHUEtudeBundle:Default:index.html.twig";
  const VUE_FORM_BUILDER = "CHUEtudeBundle:Default:build_form.html.twig";
  const VUE_LISTE_ETUDES = "CHUEtudeBundle:Default:show_etudes.html.twig";
  const VUE_SHOW_QUESTIONNAIRE = "CHUEtudeBundle:Default:show_questionnaire.html.twig";
  const VUE_DO_QUESTIONNAIRE = "CHUEtudeBundle:Default:do_questionnaire.html.twig";
  const VUE_SHOW_REPONSES = "CHUEtudeBundle:Default:show_reponses.html.twig";

  const ENTITY_ETUDE = 'CHUEtudeBundle:Etude';
  const ENTITY_ANSWER = 'CHUEtudeBundle:Answer';
  const ENTITY_QUESTION = 'CHUEtudeBundle:Question';
    public function indexAction()
    {
        return $this->render(self::VUE_HOMEPAGE);
    }


    //fonction qui affiche le form_builder
    public function build_formAction(){
      /**
      * Ici on afficher le form_builder à l'utilisateur
      */

      $content = $this->get('templating')->render(self::VUE_FORM_BUILDER);
      return new Response($content);
    }

    //fonction qui gère la validation de la création d'un formulaire.
    public function submit_builded_formAction(Request $request){
      /**
      * Gestionnaire de validation du formulaire créé.
      */
      //on vérifie qu'il s'agit d'une méthode POST
      if(! $request->isMethod('POST'))
        return new Response('Vous ne devriez pas être là.');

      //si methode POST
      //On récupère le questionnaire créé
      $questionnaire = $request->request->get('form_builded');
      //on récupère le titre du formulaire créé
      $questionnaire_title = $request->request->get('form_title');

      //vérifications
        //on vérifie qu'il ne soit pas vide
        if($questionnaire == null )
          return new Response('le questionnaire créé semble vide');

      //si non vide alors
      $em = $this->getDoctrine()->getManager();   //le entity manager
      //on créer une nouvell Etude avec le bon arguments.
      $new_Etude = new Etude();     //nouvelle Etude
      $new_Etude->setName($questionnaire_title); //on renseign son nom
      $new_Etude->setContent($questionnaire); //on renseign son contenu

      $em->persist($new_Etude);
      $em->flush();

      //mettre une annonce flash pour signaler l'ajout
      return $this->indexAction();
    }

    //Afficher la liste des questionnaires pour soit les afficher soit voir les réponses.
    public function show_etudesAction(){
      /**
      * Affichage de la liste des études disponibles.
      */
      //vérification de l'identité avant de continuer.
      //RIEN POUR LE MOMENT


      //on récupère le manager
      $em= $this->getDoctrine()->getManager();
      //On récupère la liste des études disponibles dans la base
      $etudeList = $em->getRepository(self::ENTITY_ETUDE)->findAll();
      //on envoit dans la vue.
      return $this->render(self::VUE_LISTE_ETUDES,array('EtudeList' => $etudeList));
    }

    //Afficher le questionnaire mais en bloquant l'action
    public function action_questionnaireAction(Request $request){
      /**
      * Affichage d'un questionnaire. Blocage des fonction du formulaire.
      * Il s'agit de simplement voir l'apparence du formulaire.
      */
      //verification de l'identité avant de continuer
      //RIEN POUR LE MOMENT

      //on vérifie qu'il s'agit d'une méthode POST
      if(! $request->isMethod('POST'))
        return new Response('Vous ne devriez pas être là.');

      //on récupère l'entity manager
      $em = $this->getDoctrine()->getManager();
      //on récupère le questionnaire demandé
      $idetude = $request->request->get('idetude');
      $letude = $em->getRepository(self::ENTITY_ETUDE)->find($idetude);

      $lesreponses=null;
      //si on veut voir les réponses
      if($request->request->get('mode') == 'reponses'){
        //on récupère les réponses à cette étude.
        $query = $em->createQuery("select a from CHUEtudeBundle:Answer a join a.etude e where e.id = :id");
        $query->setParameter('id', $idetude);
        //on stocke le tableau de résultat.
        $lesreponses = $query->getResult();
        if(sizeof($lesreponses)==0)
          return new Response("Il n'y a pas de réponses pour cette étude pour le moment.");
        return $this->render(self::VUE_SHOW_REPONSES,array('reponses'=>$lesreponses));
      }
      //si on veut y répondre
      if($request->request->get('mode') == 'repondre'){
        //alors on ouvre le questionnaire afin de pouvoir y repondre
        return $this->render(self::VUE_DO_QUESTIONNAIRE,array('etude'=>$letude));
      }
      //si on veut supprimer
      if($request->request->get('mode') == 'supprimer'){
        //alors on ouvre le questionnaire afin de pouvoir y repondre
        $em->remove($letude);
        $em->flush();
        return new Response(" l'etude a bien été supprimée.");
      }
      //si on veut afficher le questionnaire simplement
      //on envoit le questionnaire dans la vue de questionnaire
      return $this->render(self::VUE_SHOW_QUESTIONNAIRE,array('etude'=>$letude));


    }

    //Gérer la réponse à un questionnaire
    public function questionnaire_reponduAction(Request $request){
      /**
      * Gérer la réponse à un questionnaire.
      */
      //verification de l'identité avant de continuer
      //RIEN POUR LE MOMENT
      //on vérifie qu'il s'agit d'une méthode POST
      if(! $request->isMethod('POST'))
        return new Response('Vous ne devriez pas être là.');

      //on récupère l'entity manager
      $em = $this->getDoctrine()->getManager();

      //on récupère le tableau envoyé par la méthode POST
      $array_reponses = $request->request;
      //id de l'etude correspondante
      $idetude = $request->request->get('idetude');
      //on récupère l'étude correspondante
      $letude = $em->getRepository(self::ENTITY_ETUDE)->find($idetude);
      echo "id de létude correspondante : ";
      $answer = new Answer();
      $answer->setEtude($letude);
      $answer->setContenu("{");
      //on stocke chaque reponses dans le champ contenu de l'entité Answer.
      foreach($array_reponses as $index=>$valeur){
        if($index != 'idetude')
        {
          //echo '- '.$valeur.'<br/>';
          $answer->setContenu("".$answer->getContenu()."{ q_index:".$index.", r_content:".$valeur."}");
        }
      }
      $answer->setContenu($answer->getContenu()."}");


      //on persiste et on sauvegarde.
      $em->persist($answer);
      $em->flush();

      return new Response('réponses enregistrés');
    }

    //Gérer la suppression d'une réponse
    public function delete_answerAction(Request $request){
      /**
      * Gérer la suppression d'une réponse.
      */
      //verification de l'identité avant de continuer
      //RIEN POUR LE MOMENT
      //on vérifie qu'il s'agit d'une méthode POST
      if(! $request->isMethod('POST'))
        return new Response('Vous ne devriez pas être là.');

      //on récupère l'entity manager
      $em = $this->getDoctrine()->getManager();

      //on récupère l'id de la réponse à supprimer.
      $idreponse =  $request->request->get('idreponse');
      //on récupère l'entité correspondante.
      $reponse =  $em->getRepository(self::ENTITY_ANSWER)->find($idreponse);
      if($reponse == null ){
        return new Response("erreur lors de la suppression de e la réponse. <br/> Cette réponse n'existe pas.");
      }

      $em->remove($reponse);
      $em->flush();

      return $this->show_etudesAction();
    }




//PREVIOUS METHODES
//Les methodes qui suivent ne sont plus à utiliser. Il s'agit de mon brouillon.
    public function questionnaireAction(Request $request, $id)
    {
      //on vérifie qu'il ne s'agit pas d'une intrusion malencontreuse
      if(( !($request->isMethod('POST')) && $id<=0 ) || $id===0 )
        return new Response("Vous ne devriez pas être là. <a href='/'>retour accueil</a>");

      $etude_id = $id;
      $em = $this->getDoctrine()->getManager();
      $questionsList = $em->getRepository('CHUEtudeBundle:Question')
      ->findBy(
        array('etude' => $etude_id),//critère
        array('id' => 'asc')
      );
      //contruction du tableau à envoyer pour la page questionnaire
      for($i=0;$i<count($questionsList);$i++){
        $QList[] = array('id'=>$questionsList[$i]->getId(),"contenu"=>$questionsList[$i]->getContenu());
      }
      if(! isset($QList) )
        return new Response("Il n'y a pas encore de question dans cette étude.");
      var_dump($QList);
      return $this->render('CHUEtudeBundle:Default:questionnaire.html.twig',
       array('id' => $etude_id, 'questionnaire' => $QList));
    }

    public function reponseAction(Request $request)
    /**
     * Gère l'enregistrement des réponses du questionnaires.
     */
    {
        if($request->isMethod('POST')){
          $Age = $request->request->get('Age');
          $Taille = $request->request->get('Taille');
          $Metier = $request->request->get('Metier');
          $Fou = $request->request->get('Fou');

          return new Response("age :".$Age." taille :".$Taille." metier :".$Metier." fou :".$Fou);

          /**
          * Remplir une entité quesionnaire avec ces réponses et les persister.
          */
        }
    }

    public function reponseQuestionnaireAction(Request $request, $id)
    /**
     * Gère la reponse à la question.
     */
    {
          //on verifie l'origine de la requête
          if(! $request->isMethod('POST') )
            return new Response("Vous ne devriez pas être là. <a href='/'>accueil</a>");
          //echo $id;

          //on récupère l'Entity Manager
          $em = $this->getDoctrine()->getManager();
          // on récupère l'ensemble des questions de l'étude
          $questionsList = $em->getRepository('CHUEtudeBundle:Question')
          ->findBy(
            array('etude' => $id),//critère
            array('id' => 'asc')
          );
          //on récupère l'ensemble des réponses et on persiste la réponse
          for($i=0;$i<count($questionsList);$i++){
            //on ajoute dans le tableau
            $answersList[] = array('id'=> $questionsList[$i]->getId(),"contenu"=>$request->request->get($questionsList[$i]->getId()));
            //on crer une nouvelle réponse
            $answer = new Answer();
            //on prend la question crrespondante
            $question = $questionsList[$i];
            $answer->setQuestion($question);
            //on renseigne le contenu de la réponse
            $answer->setContenu($request->request->get($questionsList[$i]->getId()));
            //on fixe le user
            $answer->setUser("Louis_Test");
            $em->persist($answer);



          }
          var_dump($answersList);

          $em->flush();

          return new Response("La réponse \"".$answer->getContenu()."\" à la question :\"".$answer->getQuestion()->getContenu()." a bien été enregistrée.");

          /**
          * Remplir une entité quesionnaire avec ces réponses et les persister.
          */

    }


    public function newEtudeAction(Request $request)
    /**
     * Gère la création d'une nouvelle etude.
     */
    {
      return $this->render('CHUEtudeBundle:Default:newEtude.html.twig');
    }

    public function newEtude_doneAction(Request $request)
    /**
     *Gère la création d'une nouvelle étude
     */
    {
      //recupération de l'entityManager
      $em = $this->getDoctrine()->getManager();
      //on créer une nouvelle etude
      $new_etude = new Etude();
      //on renseigne le contenu de la nouvelle etude et son commentaire
      $new_etude->setName($request->request->get('newEtude'));
      $new_etude->setCommentary($request->request->get('newEtudeCommentary'));
      //on persiste la nouvelle etude
      $em->persist($new_etude);
      $em->flush();
      return new Response("L'étude \"".$new_etude->getName()."\"
        <br/> ".$new_etude->getCommentary()." <br/> a bien été enregistrée.");
    }

    public function newQuestionAction(Request $request)
    /**
     * Gère la création d'une nouvelle question.
     */
    {
      //on recupere le Manager
      $em = $this->getDoctrine()->getManager();
      $etudeList = $em->getRepository('CHUEtudeBundle:Etude')->findAll();
      return $this->render('CHUEtudeBundle:Default:newQuestion.html.twig',array('listetude'=>$etudeList));
    }

    public function newQuestion_doneAction(Request $request)
    {
      //recupération de l'entityManager
      $em = $this->getDoctrine()->getManager();
      //on créer une nouvelle question
      $new_question = new Question();
      //on renseigne le contenu de la nouvelle question
      $new_question->setContenu($request->request->get('newQuestion'));
      //on renseigne le type de la question en allant chercher le type de question
      $typ_question = $em->getRepository('CHUEtudeBundle:Question_Type')->find(1);
      $new_question->setQuestionType($typ_question);
      //cherche l'etude de la question en focntion du paraetre choisis dans le firmulaire de creation de la question
      $etude = $em->getRepository('CHUEtudeBundle:Etude')->find($request->request->get('idetude'));
      $new_question->setEtude($etude);

      $em->persist($new_question);
      $em->flush();
      return new Response("La question \"".$new_question->getContenu()."\" a bien été enregistrée.");
    }

    public function EtudeListAction(){
      $em = $this->getDoctrine()->getManager();
      $listEtude = $em->getRepository('CHUEtudeBundle:Etude')->findAll();
      return $this->render('CHUEtudeBundle:Default:listEtude.html.twig',array('EtudeList' => $listEtude ));
    }

    public function ResultatsAction(){
      //on récupère le manager
      $em= $this->getDoctrine()->getManager();
      //on prepare toutes les entités pour l'affichage es resultats/
      $etudeList = $em->getRepository('CHUEtudeBundle:Etude')->findAll();
      return $this->render('CHUEtudeBundle:Default:Results.html.twig',array('EtudeList' => $etudeList));
    }

    //Page d'Affichage des Etudes.
    public function EtudeDisplayAction(Request $request){
      //on verifie qu'il s'agit d'une methode post
      if(! $request->isMethod('POST'))
        return new Response("Vous ne devriez pas être là.");
      //recupération du manager d'entity
      $em = $this->getDoctrine()->getManager();
      $idetude = $request->request->get('idetude');
      //on récupère l'étude
      $letude = $em->getRepository('CHUEtudeBundle:Etude')->find($idetude);
      //on recupere les questions de l'etude
      $listeQuestions = $em->getRepository('CHUEtudeBundle:Question')->findByEtude($idetude);
      //on récupère le mode souhaité (affichage des questions de l'étude ou affichage des réponses)
      $mode = $request->request->get('mode');
      if($mode == 'reponse'){
        //on récupères les questions.
        $query = $em->createQuery("select a from CHUEtudeBundle:Answer a join a.question q where q.etude = :id");
        $query->setParameter('id', $idetude);

        //on stocke le tableau de résultat.
        $Tab_Answers = $query->getResult();

        return $this->render('CHUEtudeBundle:Default:Etude_Answers_Display.html.twig',array('etude'=>$letude,'ListAnswers'=>$Tab_Answers));
#########################!!z!dzdaz
        //"select a from CHUEtudeBundle:Answer a join a.question q where q.etude = 1"
        //$listeReponses = $em->getRepository('CHUEtudeBundle:Answer')->findBy
      }
      else{
          //on affiche les questions.
          return $this->render('CHUEtudeBundle:Default:Etude_Display.html.twig',array('questionList'=> $listeQuestions));
      }
    }

}
