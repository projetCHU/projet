<?php

namespace CHU\EtudeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//include des entités nécessaires.
use CHU\EtudeBundle\Entity\Question;
use CHU\EtudeBundle\Entity\Etude;
use CHU\EtudeBundle\Entity\Answer;
use CHU\EtudeBundle\Entity\ValeursReferences;

 use Doctrine\MongoDB\Database;
 use MongoDB\BSON\ObjectID;



/*
authors : LOUIS MARCHAND / JULIEN FONTAINE (fonfonjuju49@gmail.com)
*/

class DefaultController extends Controller
{

  const VUE_HOMEPAGE = "CHUEtudeBundle:Default:index.html.twig";
  const VUE_MESSAGE = "CHUEtudeBundle:Default:message_page.html.twig";
  const VUE_CREATE_FORM = "CHUEtudeBundle:Default:create_form.html.twig";
  const VUE_FORM_BUILDER = "CHUEtudeBundle:Default:build_form.html.twig";
  const VUE_LISTE_ETUDES = "CHUEtudeBundle:Default:show_etudes.html.twig";
  const VUE_SHOW_QUESTIONNAIRE = "CHUEtudeBundle:Default:show_questionnaire.html.twig";
  const VUE_DO_QUESTIONNAIRE = "CHUEtudeBundle:Default:do_questionnaire.html.twig";
  const VUE_SHOW_REPONSES = "CHUEtudeBundle:Default:show_reponses.html.twig";

  const PARAM_VUE_FORM_BUILDER_TITRE = "titre";
  const PARAM_VUE_FORM_BUILDER_FORMULAIRE = 'formulaire';

  const ARG_VUE_LISTE_ETUDES = 'liste_etudes';
  const PARAM_VUE_LISTE_ETUDES_ID_ETUDE = 'id_etude';

  const ARG_VUE_MESSAGE = 'message';
  const ARG_VUE_MESSAGE_TITRE = 'titre_page';

  const ARG_VUE_DO_QUESTIONNAIRE_ETUDE_NAME = 'etude_name';
  const ARG_VUE_DO_QUESTIONNAIRE_ETUDE_FORM = 'etude_form';

  const ATTRIBUT_FORMULAIRE_TYPE = 'type';
  const ATTRIBUT_FORMULAIRE_VALUE = 'value';

  const ERROR_PAGE_INEXISTANTE = 'Page inexistante.';

  // CONSTANTE POUR MONGODB
  const MONGO_DATABASE_NAME = 'CHU_TEST';
  const MONGO_DATABASE_CONNECTION = 'doctrine_mongodb.odm.default_connection';

  const COLLECTION_ETUDES = 'etudes';
  const COLLECTION_REPONSES = 'reponses';
  const COLLECTION_QUESTIONS = 'questions';
  const COLLECTION_VALEURS_REFERENCES = "valeurs_references";

  const CHAMP_ID_COLLECTION_ETUDES = '_id';
  const CHAMP_TITRE_COLLECTION_ETUDES = 'titre';
  const CHAMP_CREATEUR_COLLECTION_ETUDES = 'createur';

  const CHAMP_ID_COLLECTION_REPONSES = '_id';
  const CHAMP_ID_UTILISATEUR_COLLECTION_REPONSES = 'id_utilisateur';
  const CHAMP_ID_ETUDE_COLLECTION_REPONSES = 'id_etude';

  const CHAMP_ID_COLLECTION_QUESTIONS = '_id';
  const CHAMP_LABEL_COLLECTION_QUESTIONS = 'label';
  const CHAMP_TYPE_COLLECTION_QUESTIONS = 'type';
  const CHAMP_ID_ETUDE_COLLECTION_QUESTIONS = 'id_etude';
  const CHAMP_SECTION_TEXTE_QUESTIONS = 'section';
  const CHAMP_TYPE_GRAPH_QUESTIONS = 'graph';
  const CHAMP_DATA_CONSTRAINT_QUESTIONS = 'dataConstraint';
  const CHAMP_DATA_CONSTRAINT_TYPE_QUESTIONS = 'type';
  const CHAMP_DATA_CONSTRAINT_MIN_QUESTIONS = "min";
  const CHAMP_DATA_CONSTRAINT_MAX_QUESTIONS = "max";
  const CHAMP_DATA_CONSTRAINT_STEP_QUESTIONS = "step";

  const CHAMP_LABEL_REPONSES_COLLECTION_QUESTIONS = 'label_reponses';
  const KEY_LABEL_REPONSES_COLLECTION_QUESTIONS = 'label';
  const CHAMP_INDICE_POSITION_COLLECTION_QUESTIONS = 'indice_position';
  const CHAMP_ID_VALEUR_REFERENCE_COLLECTION_QUESTIONS = 'id_valeur_reference';





    /**
      AFFICHAGE DE LA PAGE D'ACCUEIL
    **/
    public function indexAction()
    {
        return $this->render(self::VUE_HOMEPAGE);
    }


    /**
      AFFICHER LA PAGE DE CRÉATION D'UN QUESTIONNAIRE
    **/
    public function create_formAction($erreur=null,$titre=null){
      return $this->render(self::VUE_CREATE_FORM,array('erreur' => $erreur, 'titre' => $titre));
    }

    /**
      AFFICHER LE FORMBUILDER POUR CONSTRUIRE UN QUESTIONNAIRE
    **/
    public function build_formAction(Request $request){
      /**
      * Ici on afficher le form_builder à l'utilisateur
      */
      if(! $request->isMethod('POST'))
        throw new NotFoundHttpException(self::ERROR_PAGE_INEXISTANTE);

      $parameters = $request->request->all();
      $title_form = $parameters['form_title'];        //on récupère le titre dans la variable POST

      // On vérifie que le nom de l'étude n'est pas dejà utilisé
      $m = $this->container->get(self::MONGO_DATABASE_CONNECTION);
      $db = $m->selectDatabase(self::MONGO_DATABASE_NAME);
      $collection = $db->selectCollection(self::COLLECTION_ETUDES);
      $filter = array( self::CHAMP_TITRE_COLLECTION_ETUDES => $title_form);
      $doc = $collection->findOne($filter);

      if(!empty($doc)){ // si on trouve une étude du même nom
        return $this->render(self::VUE_CREATE_FORM,array('erreur' => 'Ce nom d\'étude existe dejà', 'titre' => $title_form));
      }

      $string_score=""; //on récupère les scores dans la variable POST
      if(isset($parameters['scores'])){
        foreach($parameters['scores'] as $key => $value){
          if($key==sizeof($parameters))
            $string_score.="".$value;
          else
            $string_score.=$value.";";
        }
    }

    //le tableau de valeur de références écrit en dur.
    $valRef = "R3AQ2,R3AGENUM,R3AQ33,R3EC3,PCS03_2digit,R3AQ48,R3AQ49,R3AQ573,R3AQ576,R3AQ579,R3AQ588,R3AQ5826,R3AQ48,CRR3pench";

    return $this->render(self::VUE_FORM_BUILDER,array('scores'=>$string_score,'form_title'=>$title_form,
      'valref'=>$valRef)
    );
  }

    /**
      VALIDATION DE LA CRÉATION D'UNE ETUDE ET PERSISTENCE EN BASE DE DONNÉE DES
      INFORMATIONS CONCERNANT L'ETUDE
    **/
    public function submitBuiltFormAction(Request $request){
      /**
      * Gestionnaire de validation du questionnaire créé.
      */

      //on vérifie qu'il s'agit d'une méthode POST
      if(! $request->isMethod('POST'))
        throw new NotFoundHttpException(self::ERROR_PAGE_INEXISTANTE);

      //si methode POST
      $m = $this->container->get(self::MONGO_DATABASE_CONNECTION);

      // Selection de la base de donnée
      $db = $m->selectDatabase(self::MONGO_DATABASE_NAME);

      // Recupération des informations sur le questionnaire

      // Boucle pour ajouter les questions une par une dans la collection "question"

      // Ajout de l'étude dans la collection "etude" de la BDD
      $collection = $db->selectCollection(self::COLLECTION_ETUDES);

      $titre = $request->request->get(self::PARAM_VUE_FORM_BUILDER_TITRE);

      $document = array();
      $document[self::CHAMP_TITRE_COLLECTION_ETUDES]=$titre;
      $document[self::CHAMP_CREATEUR_COLLECTION_ETUDES]="a14de5d1dsd"; // ID du créateur de l'étude
      $collection->insert($document);
      $id_etude = new ObjectId($document[self::CHAMP_ID_COLLECTION_ETUDES]);

      $formulaire = $request->request->get(self::PARAM_VUE_FORM_BUILDER_FORMULAIRE);
      $label_scores = explode(";",$request->request->get('scores'));
      array_pop($label_scores);

      $this->persistQuestionsFromHtml($formulaire,$id_etude,$label_scores,$db); // On stocke les différentes questions du formulaire

      // On informe l'utilisateur que l'ajout s'est bien déroulé
      return $this->render(self::VUE_MESSAGE,array(self::ARG_VUE_MESSAGE => 'Votre étude a bien été enregistrée !',
                                                   self::ARG_VUE_MESSAGE_TITRE => 'Enregistrement : '.$titre));
    }

    /**
      AFFICHER LA LISTE DES ETUDES
    **/
    public function showListFormsAction(){
      /**
      * Affichage de la liste des études disponibles.
      */
      //vérification de l'identité avant de continuer.
      //RIEN POUR LE MOMENT

      //on récupère le manager
      $m = $this->container->get(self::MONGO_DATABASE_CONNECTION);
      $db = $m->selectDatabase(self::MONGO_DATABASE_NAME);
      $collection = $db->selectCollection(self::COLLECTION_ETUDES);
      //On récupère la liste des études disponibles dans la base
      $cursor = $collection->find();

      $liste_etudes = array();
      while($cursor->hasNext()){
        $liste_etudes[] = $cursor->getNext();
      }

      //on envoie dans la vue.
      return $this->render(self::VUE_LISTE_ETUDES,array(self::ARG_VUE_LISTE_ETUDES => $liste_etudes));
    }

    /**
    SUPPRIMER UNE ETUDE
    **/
    public function deleteFormAction(Request $request){
      //on vérifie qu'il s'agit d'une méthode POST
      if(! $request->isMethod('POST'))
        throw new NotFoundHttpException(self::ERROR_PAGE_INEXISTANTE);

      // On choisi la BDD
      $m = $this->container->get(self::MONGO_DATABASE_CONNECTION);
      $db = $m->selectDatabase(self::MONGO_DATABASE_NAME);

      //on récupère le questionnaire demandé
      $id_etude = new ObjectId($request->request->get(self::PARAM_VUE_LISTE_ETUDES_ID_ETUDE));

      $collection = $db->selectCollection(self::COLLECTION_ETUDES);
      $filter_etude = array( self::CHAMP_ID_COLLECTION_ETUDES => $id_etude );
      $collection->findAndRemove($filter_etude);

      $collection = $db->selectCollection(self::COLLECTION_REPONSES);
      $filter_reponses = array( self::CHAMP_ID_ETUDE_COLLECTION_REPONSES => $id_etude );
      $collection->remove($filter_reponses);

      $collection = $db->selectCollection(self::COLLECTION_QUESTIONS);
      $filter_reponses = array( self::CHAMP_ID_ETUDE_COLLECTION_QUESTIONS => $id_etude );
      $collection->remove($filter_reponses);

      return $this->render(self::VUE_MESSAGE,array(self::ARG_VUE_MESSAGE => 'L\'étude a bien été supprimée !',
                                                   self::ARG_VUE_MESSAGE_TITRE => 'Succès'));
    }

    /**
    RÉPONDRE À UNE ÉTUDE
    **/
    public function answerFormAction(Request $request) {
      //on vérifie qu'il s'agit d'une méthode POST
      if(! $request->isMethod('POST'))
        throw new NotFoundHttpException(self::ERROR_PAGE_INEXISTANTE);

      // On choisi la BDD
      $m = $this->container->get(self::MONGO_DATABASE_CONNECTION);
      $db = $m->selectDatabase(self::MONGO_DATABASE_NAME);

      //on récupère le questionnaire demandé
      $id_etude = new ObjectId($request->request->get(self::PARAM_VUE_LISTE_ETUDES_ID_ETUDE));

      $collection = $db->selectCollection(self::COLLECTION_ETUDES);

      $filter = array( self::CHAMP_ID_COLLECTION_ETUDES => $id_etude );
      $etude = $collection->findOne($filter);



      //si on veut y répondre
        //on récupère le format html du questionnaire, et activé
        $questionnaire = $this->fetchQuestionsToHtml($etude, true, $db);

        //alors on ouvre le questionnaire afin de pouvoir y repondre
        return $this->render(self::VUE_DO_QUESTIONNAIRE,
                             array(self::ARG_VUE_DO_QUESTIONNAIRE_ETUDE_NAME => $etude[self::CHAMP_TITRE_COLLECTION_ETUDES],
                                   self::ARG_VUE_DO_QUESTIONNAIRE_ETUDE_FORM => $questionnaire
                                   )
                            );
    }

    /**
    VOIR LES RÉSULTATS D'UNE ÉTUDE
    **/
    public function checkResultsAction(Request $request){
      //on vérifie qu'il s'agit d'une méthode POST
      if(! $request->isMethod('POST'))
        throw new NotFoundHttpException(self::ERROR_PAGE_INEXISTANTE);
      // On choisi la BDD
      $m = $this->container->get(self::MONGO_DATABASE_CONNECTION);
      $db = $m->selectDatabase(self::MONGO_DATABASE_NAME);

      $id_etude = new ObjectId($request->request->get(                          //on récupère l'étude demandée
        self::PARAM_VUE_LISTE_ETUDES_ID_ETUDE));
      $collection = $db->selectCollection(self::COLLECTION_ETUDES);
      $filter = array( self::CHAMP_ID_COLLECTION_ETUDES => $id_etude );
      $etude = $collection->findOne($filter);

      $collection = $db->selectCollection(self::COLLECTION_REPONSES);           //on récupère les réponses à cette étude
      $filter = array( self::CHAMP_ID_ETUDE_COLLECTION_REPONSES => $id_etude );
      $cursor = $collection->find($filter);
      $reponses = array();                                                      //le tableau des réponses (des utilisateurs)
      while($cursor->hasNext()){
        $reponses[] = $cursor->getNext();
      }

      if(sizeof($reponses)==0)                                                  //s'il n'y a pas de réponses on retourne "pas de réponses".
        return $this->render(self::VUE_MESSAGE,array(self::ARG_VUE_MESSAGE => 'Il n\'y a pas de réponses à ce questionnaire pour le moment',
                                                     self::ARG_VUE_MESSAGE_TITRE => 'Consulter les réponses'));


      $collection = $db->selectCollection(self::COLLECTION_QUESTIONS);          //récolte des questions de l'étude
      $filter = array(self::CHAMP_ID_ETUDE_COLLECTION_QUESTIONS => $id_etude);
      $cursor = $collection->find($filter);
      $questions = array();                                                     //le tableau de questions
      while($cursor->hasNext()){
       $questions[] = $cursor->getNext();
      }


      $scores = $this->calculScore($reponses,$questions);                       //calcul des scores de chaque "reponse"(Chaque ensemble de réponses des utilisateurs)

      $htmlform = $this->fetchQuestionsForGraph($etude,$questions,$db);         //Création du formulaire avec valeurs de références intégrées.

      return $this->render(self::VUE_SHOW_REPONSES,array('scores' => $scores,'form'=>$htmlform));
    }





    /**
      PERSISTENCE DES RÉPONSES D'UNE ETUDE
    **/
    public function submitFormAnsweredAction(Request $request){
      /**
      * Gérer la réponse à un questionnaire.
      */

    if(! $request->isMethod('POST'))
        throw new NotFoundHttpException(self::ERROR_PAGE_INEXISTANTE);

      $m = $this->container->get(self::MONGO_DATABASE_CONNECTION);
      $db = $m->selectDatabase(self::MONGO_DATABASE_NAME);
      $collection = $db->selectCollection(self::COLLECTION_REPONSES);

      //on récupère le tableau envoyé par la méthode POST
      $reponses = $request->request->all();
      $reponses[self::CHAMP_ID_UTILISATEUR_COLLECTION_REPONSES] = '152364789'; // On met une valeur en dur en attendant de pouvoir récupérer l'id de l'id_utilisateur
      $reponses[self::CHAMP_ID_ETUDE_COLLECTION_REPONSES] = new ObjectId($reponses[self::CHAMP_ID_ETUDE_COLLECTION_REPONSES]);

      $collection->insert($reponses);
      return $this->render(self::VUE_MESSAGE,array(self::ARG_VUE_MESSAGE => 'Vos réponses ont bien été enregistrées !',
                                                   self::ARG_VUE_MESSAGE_TITRE => 'Succès !'));

    }//fin function




/**
      FONCTIONS PRIVÉES.
**/

    /**
     CONVERSION DE CHACUNE DES QUESTIONS EN HTML D'UNE ETUDE ET
     PERSISTENCE DE CHACUNE DES QUESTIONS EN BDD
    */
    private function persistQuestionsFromHtml(String $questionnaire_html, ObjectId $id_etude, Array $label_scores, Database $database){

      // On selectionne la collection "question" de la BDD
      $collection = $database->selectCollection(self::COLLECTION_QUESTIONS);

      //CHARGEMENT DU QUESTIONNAIRE CRÉÉ
      //On récupère le questionnaire créé
      //et on ajoute l'entête xml
      $questionnaire ="<?xml version='1.0' standalone='yes'?>".$questionnaire_html;

      //on charge le parser xml
      $xml = simplexml_load_string($questionnaire);
      //chaque question est représenter par une balise div directement sous la balise form
      //on récupère l'enseble des questions.
      $questions = null;
      foreach($xml->children() as $child){
        $questions[] = $child;
      }

      $indice_position = 0; // Permet de préciser l'ordre des questions dans le questionnaire en BDD
      foreach($questions as $ques){
        //le tableau de la question
        $question = array();
        //l'id de l'étude de la question
        $question[self::CHAMP_ID_ETUDE_COLLECTION_QUESTIONS] = $id_etude;

        //on récupère l'intitulé de la question
        $question[self::CHAMP_LABEL_COLLECTION_QUESTIONS] = trim($ques->label);

        //on récupère le type de question
        $question[self::CHAMP_TYPE_COLLECTION_QUESTIONS] = trim($ques->div->attributes()[self::ATTRIBUT_FORMULAIRE_TYPE]);

        //on récupère la valeur de référence de la question
        $valRef= trim((String)$ques->div->val);
        if($valRef=="")$valRef="NONE";          //NONE si pas de valeurs de références

        //on récupère le type de graphe.
        $graphtype = trim((String)$ques->div->graph);
        if($graphtype=="")$graphtype="NONE";    //NONE si pas de type de graph

        //on teste la nature des reponses attendues
        // et on récupère laliste des réponses si nécessaire.
        $label_reponses = array();
        switch($ques->div->attributes()[self::ATTRIBUT_FORMULAIRE_TYPE]){
          case 'section':
            //on récupère le text et le titre de la section
            $SectionText = (String)$ques->div->textarea;
            $SectionTitle= (String)$ques->label;
            //on inscrit les valeurs dans le tableau
            $question[self::CHAMP_SECTION_TEXTE_QUESTIONS] = trim($SectionText);
            break;
          case 'text':
            //LES CONTRAINTES SUR LES DONNÉES
                //on vérifie la contrainte sur le type de donnée attendu.
                $dataConstraint = (String)$ques->div->input->attributes()['type'];
                if($dataConstraint=="number"){
                  $les_constraints_string = explode(";",(String)$ques->div->constraints);         //on split la chaine
                  foreach($les_constraints_string as $contrainte){                                //pour chaque contrainte
                    $keyVal=explode("=",$contrainte);                                             //on split la contrainte ( nom -> valeur)
                    $les_constraints_tab[trim($keyVal[0])]=trim($keyVal[1]);                      //on ajoute la contrainte au tableau de contrainte
                  }

                  $Constraints[self::CHAMP_DATA_CONSTRAINT_TYPE_QUESTIONS]= trim($dataConstraint);//le type de donnée
                  $Constraints[self::CHAMP_DATA_CONSTRAINT_MAX_QUESTIONS]=$les_constraints_tab[self::CHAMP_DATA_CONSTRAINT_MAX_QUESTIONS];
                  $Constraints[self::CHAMP_DATA_CONSTRAINT_MIN_QUESTIONS]=$les_constraints_tab[self::CHAMP_DATA_CONSTRAINT_MIN_QUESTIONS];
                  $Constraints[self::CHAMP_DATA_CONSTRAINT_STEP_QUESTIONS]=$les_constraints_tab[self::CHAMP_DATA_CONSTRAINT_STEP_QUESTIONS];
                }
                else{
                  $Constraints = array();
                  $Constraints[self::CHAMP_DATA_CONSTRAINT_TYPE_QUESTIONS]= trim($dataConstraint);//le type de donnée
                }
                $question[self::CHAMP_DATA_CONSTRAINT_QUESTIONS]=$Constraints;
            break;
          case 'textarea':
            //VALEURS DE RÉFÉRENCE - CORRECTION
                $valRef= trim((String)$ques->div->div->val);
                if($valRef=="")$valRef="NONE";          //NONE si pas de valeurs de références
            //TYPE DE GRAPHE - CORRECTION
                $graphtype = trim((String)$ques->div->graph);
                if($graphtype=="")$graphtype="NONE";    //NONE si pas de type de graph
            break;
          case 'option'://même traitement que 'option-multiple'
          case 'option-multiple':
            //on récupère toutes les réponses possibles.
            foreach($ques->div->select->children() as $reponse){
              $scores = (string)$reponse->attributes()['value'];
              $value_scores = explode(" ; ", $scores);
              $label_reponse[self::KEY_LABEL_REPONSES_COLLECTION_QUESTIONS] = trim($reponse);

              foreach($label_scores as $indice => $label_score){
                $label_reponse[$label_score] = (int)$value_scores[$indice];
              }

              $label_reponses[] = $label_reponse;
            }

            break;
        }

        $question[self::CHAMP_LABEL_REPONSES_COLLECTION_QUESTIONS] = $label_reponses;
        $question[self::CHAMP_INDICE_POSITION_COLLECTION_QUESTIONS] = $indice_position;
        $question[self::CHAMP_ID_VALEUR_REFERENCE_COLLECTION_QUESTIONS] = $valRef;
        $question[self::CHAMP_TYPE_GRAPH_QUESTIONS] = $graphtype;
        $indice_position++;
        // On ajoute la question dans la BDD
        $collection->insert($question);
        $id_questions[] = $question[self::CHAMP_ID_COLLECTION_QUESTIONS];
      }//fin de traitement des questions.

    }//end fonction



    /**
      RECUPERATION DES QUESTIONS D'UNE ETUDE ET TRANSFORMATION EN HTML POUR AFFICHAGE DES RÉSULTAT AVEC GRAPHIQUES.
    **/
    private function fetchQuestionsForGraph(Array $etude, Array $questions, Database $database){
      if(! isset($etude))                                                               //si l'étude est null, on sort
        return null;

      $id_etude = $etude[self::CHAMP_ID_COLLECTION_ETUDES];

      $collection = $database->selectCollection(self::COLLECTION_QUESTIONS);            //Obtention des questions correspondant à l'id de l'étude
      $filter = array(self::CHAMP_ID_ETUDE_COLLECTION_QUESTIONS => $id_etude);          //le filtre pour la requete
      $cursor = $collection->find($filter);                                             //la requête
      $sort_filter = array(self::CHAMP_INDICE_POSITION_COLLECTION_QUESTIONS => 1);      //le filtre pour le tri
      $cursor->sort($sort_filter);                                                      //le tri

      $questions = array();                                                             //le tableau de questions de l'étude
      while($cursor->hasNext()){                                                        //remplissage du tableau avec le cursor de la requête
        $questions[] = $cursor->getNext();
      }


      $html="";                                                                         //Construction du code HTML pour le retour de la fonction
      $html.='<legend id="form_title">'.$titre = $etude[self::CHAMP_TITRE_COLLECTION_ETUDES].'</legend>';//le titre du questionnaire
      $html.='<input type="hidden" name="id_etude" id="id_etude" value="'.$id_etude.'">';//

      if(sizeof($questions)!=0)                                                         //Inscription des question dans le ode HTML
      {
        foreach($questions as $question){                                               //pour chaque question
          $numero       =$question[self::CHAMP_ID_COLLECTION_QUESTIONS];                //numéro de question (ordre dans l'étude)
          $intitule     =$question[self::CHAMP_LABEL_COLLECTION_QUESTIONS];             //l'intitulé de la question
          $type         =$question[self::CHAMP_TYPE_COLLECTION_QUESTIONS];              //le type de la question
          $type_graph   =$question[self::CHAMP_TYPE_GRAPH_QUESTIONS];                   //le type de graphe associé
          $reference    =$question[self::CHAMP_ID_VALEUR_REFERENCE_COLLECTION_QUESTIONS];//la référence associée
          $array_valRef =$this->getValeursReferences($reference,$database);             //le tableau des valeurs de références triées,groupées
          $array_valRef =json_encode($array_valRef);                                    //encodage en JSON des valeurs de références

          $enteteQuestion='<div class="form-group" style="cursor: pointer;">';          //div qui englobe la question
          $titreQuestion="";                                                            //titre de la question
          $valeursReferences="";                                                        //options de la question (pour list, select)
          $Section="";                                                                  //section (s'il s'agit d'un section)

          $reponses = null;                                                             //les reponses
                                                                                        //on récupère les réponses possibles selon le type de question
                                                                                        //et on construit l'intérieur de la balise <div> de cette question

          switch($type)                                                                //SELON le type de la question
          {
            case 'section':                                                             //SECTION
              $sectionText = $question['section'];                                      //le texte de la section
              $enteteQuestion.='<!-- Section -->';
              $sectionText='<div class="FormSection"><p class="FormSectionText">'.$sectionText.'</p></div>';
              $Section.=$sectionText;
              break;
            case 'text':                                                                //TEXT
                $enteteQuestion.='<!-- Champs de texte -->';                            //label de la question
                $titreQuestion.='<label class="control-label col-sm-4" for="textInput" style="cursor: pointer;">';
                $titreQuestion.=$intitule.'</label>';
              break;
            case 'textarea':                                                            //TEXTAREA
              $enteteQuestion.='<!-- Champ de texte : Commentaire -->';                 //label de la question
              $titreQuestion.='<label class="col-sm-4 control-label" for="textInput">';
              $titreQuestion.=$intitule.'</label>';
              break;
            case 'option':                                                              //OPTION (select mono)
              $enteteQuestion.='<!-- Select Single -->';                                //label de la question
              $titreQuestion.='<label class="col-sm-4 control-label" for="selectSingle">';
              $titreQuestion.=$intitule.'</label>';
              break;
            case 'option-multiple':                                                     //OPTION (select multi)
              $enteteQuestion.='<!-- Select Single -->';                                //label de la question
              $titreQuestion.='<label class="col-sm-4 control-label" for="selectMultiple">';
              $titreQuestion.=$intitule.'</label>';
              break;
          }

          if($type_graph!="NONE"){
            $valeursReferences.='<div class="col-sm-7" name="valref">';
            $valeursReferences.='<p type="'.$type_graph.';'.$reference.'" style="display:none">';//la div des valeurs de références
            $valeursReferences.=$array_valRef;
            $valeursReferences.='</p>';
            $valeursReferences.="</div>";
          }

          $html.=$enteteQuestion.$titreQuestion.$valeursReferences.$Section;               //INSCRIPTION de la question dans le code HTML de retour
          $html.='</div>';
        }//END FOR EACH                                                                 //fin de boucle sur les questions
      }//endif

      return $html;                                                                     //RETOUR du questionnaire html formé
    }

    /**
      RECUPERATION DES QUESTIONS D'UNE ETUDE EN BDD ET CONVERSION DE CELLES-CI EN FORMULAIRE HTML.
      Reproduit un formualire html à partir des questions tirées de la base.
    */
    private function fetchQuestionsToHtml(Array $etude, bool $toActivate, Database $database){
      if(! isset($etude))                                                               //si l'étude est null, on sort
        return null;

      $id_etude = $etude[self::CHAMP_ID_COLLECTION_ETUDES];                             //l'id de l'étude

      $collection = $database->selectCollection(self::COLLECTION_QUESTIONS);            //Obtention des questions correspondant à l'id de l'étude
      $filter = array(self::CHAMP_ID_ETUDE_COLLECTION_QUESTIONS => $id_etude);          //le filtre pour la requete
      $cursor = $collection->find($filter);                                             //la requête
      $sort_filter = array(self::CHAMP_INDICE_POSITION_COLLECTION_QUESTIONS => 1);      //le filtre pour le tri
      $cursor->sort($sort_filter);                                                      //le tri

      $questions = array();                                                             //le tableau de questions de l'étude
      while($cursor->hasNext()){                                                        //remplissage du tableau avec le cursor de la requête
        $questions[] = $cursor->getNext();
      }


      $html="";                                                                         //Construction du code HTML pour le retour de la fonction
      $html.="<legend>".$etude[self::CHAMP_TITRE_COLLECTION_ETUDES]."</legend>";//le titre du questionnaire
      $html.='<input type="hidden" name="id_etude" id="id_etude" value="'.$id_etude.'">';//

      if(sizeof($questions)!=0)                                                         //Inscription des question dans le ode HTML
      {
        foreach($questions as $question){                                               //pour chaque question

          $enteteQuestion='<div class="form-group" style="cursor: pointer;">';          //div qui englobe la question
          $titreQuestion="";                                                            //titre de la question
          $optionQuestion="";                                                           //options de la question (pour list, select)
          $Section="";                                                                  //section (s'il s'agit d'un section)

          $numero = $question[self::CHAMP_ID_COLLECTION_QUESTIONS];                     //numéro de question (ordre dans l'étude)
          $intitule = $question[self::CHAMP_LABEL_COLLECTION_QUESTIONS];                //l'intitulé de la question
          $type = $question[self::CHAMP_TYPE_COLLECTION_QUESTIONS];                     //le type de la question

          $reponses = null;                                                             //les reponses
                                                                                        //on récupère les réponses possibles selon le type de question
                                                                                        //et on construit l'intérieur de la balise <div> de cette question

          switch($type)                                                                //SELON le type de la question
          {
            case 'section':                                                             //SECTION
              $sectionText = $question['section'];                                      //le texte de la section
              $enteteQuestion.='<!-- Section -->';
              $sectionText='<div class="FormSection"><p class="FormSectionText">'.$sectionText.'</p></div>';
              $Section.=$sectionText;
              break;
            case 'text':                                                                //TEXT
                $enteteQuestion.='<!-- Champs de texte -->';                            //label de la question
                $titreQuestion.='<label class="control-label col-sm-4" for="textInput" style="cursor: pointer;">';
                $titreQuestion.=$intitule.'</label>';

                $optionQuestion.='<div class="col-sm-7" reponseType="text">';           //les options

                $dataConstraint = $question[self::CHAMP_DATA_CONSTRAINT_QUESTIONS];     //le type de contrainte sur la question
                $constraints_attr = "";

                if($dataConstraint[self::CHAMP_DATA_CONSTRAINT_TYPE_QUESTIONS]=="text"){//si type contrainte text
                }
                else if($dataConstraint[self::CHAMP_DATA_CONSTRAINT_TYPE_QUESTIONS]=="number"){//si type contrainte number
                  $min = $dataConstraint[self::CHAMP_DATA_CONSTRAINT_MIN_QUESTIONS];            //on récupère les contraintes sur le type de valeurs autorisé (min,max, etc..)
                  $max = $dataConstraint[self::CHAMP_DATA_CONSTRAINT_MAX_QUESTIONS];
                  $step = $dataConstraint[self::CHAMP_DATA_CONSTRAINT_STEP_QUESTIONS];

                  if($min != null){$constraints_attr.=' min="'.$min.'"';}
                  if($max != null){$constraints_attr.=' max="'.$max.'"';}
                  if($step != null){$constraints_attr.=' step="'.$step.'"';}
                }

                $optionQuestion.='<input id="textInput" name="'.$numero.'" ';           //renseignement des contraintes dans l'attribut type de la div interne de la question.
                $optionQuestion.=' class="form-control" type="'.$dataConstraint[self::CHAMP_DATA_CONSTRAINT_TYPE_QUESTIONS].'" '.$constraints_attr.'  required/>';
                $optionQuestion.='</div>';
              break;
            case 'textarea':                                                            //TEXTAREA
              $enteteQuestion.='<!-- Champ de texte : Commentaire -->';                 //label de la question
              $titreQuestion.='<label class="col-sm-4 control-label" for="textInput">';
              $titreQuestion.=$intitule.'</label>';

              $optionQuestion.='<div class="col-sm-7" reponseType="textarea">';         //options de la question
              $optionQuestion.='<input id="textareaInput" name="'.$numero.'" class="form-control" type="textarea" required/>';
              $optionQuestion.='</div>';
              break;
            case 'option':                                                              //OPTION (select mono)
              $enteteQuestion.='<!-- Select Single -->';                                //label de la question
              $titreQuestion.='<label class="col-sm-4 control-label" for="selectSingle">';
              $titreQuestion.=$intitule.'</label>';

              $optionQuestion.='<div class="col-sm-7" >';                               //options de la question
              $optionQuestion.='<select id="selectSingle" name="'.$numero.'" class="form-control" required>';
              foreach($question[self::CHAMP_LABEL_REPONSES_COLLECTION_QUESTIONS] as $reponse){ //on fetch les différentes réponse possibles pour la question
                $optionQuestion .= '<option value="'.$reponse[self::KEY_LABEL_REPONSES_COLLECTION_QUESTIONS].'">';
                $optionQuestion .= $reponse[self::KEY_LABEL_REPONSES_COLLECTION_QUESTIONS].'</option>';
              }
              $optionQuestion.='</select>';
              $optionQuestion.='</div>';
              break;
            case 'option-multiple':                                                     //OPTION (select multi)
              $enteteQuestion.='<!-- Select Single -->';                                //label de la question
              $titreQuestion.='<label class="col-sm-4 control-label" for="selectMultiple">';
              $titreQuestion.=$intitule.'</label>';

              $optionQuestion.='<div class="col-sm-7" >';                               //options de la question
              $optionQuestion.='<select id="selectMultiple"  name="'.$numero.'[]" class="form-control" multiple required>';
              foreach($question[self::CHAMP_LABEL_REPONSES_COLLECTION_QUESTIONS] as $reponse){ //on fetch les différentes réponse possibles pour la question
                  $optionQuestion .= '<option value="'.$reponse[self::KEY_LABEL_REPONSES_COLLECTION_QUESTIONS].'">';
                  $optionQuestion .= $reponse[self::KEY_LABEL_REPONSES_COLLECTION_QUESTIONS].'</option>';
              }
              $optionQuestion.='</select>';
              $optionQuestion.='</div>';
              break;
          }

          $html.=$enteteQuestion.$titreQuestion.$optionQuestion.$Section;               //INSCRIPTION de la question dans le code HTML de retour
          $html.='</div>';
        }//END FOR EACH                                                                 //fin de boucle sur les questions
      }//endif

      return $html;                                                                     //RETOUR du questionnaire html formé
    }

    /**
      Calculer les scores d'un ensemble de réponses.
      POUR LE MOMENT on NE PREND QUE LES REPONSES DE LA PREIERE PERSONNE QU'ON TROUVE
    **/
    private function calculScore(Array $reponses, Array $questions){
      $score = array();
      $reponse = $reponses[0]; // POUR FIXER A UN ET UN SEUL USER (cas d'exemple)
      foreach($reponse as $key => $value){ // pour chaque reponse on récupère l'id de la question et la/les reponses données
        if( $key != '_id' && $key != 'id_etude' && $key != 'id_utilisateur'){
          foreach($questions as $question){ // on cherche à quelle question de l'etude l'id récupéré dans la reponse fait référence
            if($key == $question[self::CHAMP_ID_COLLECTION_QUESTIONS]){
              foreach($question[self::CHAMP_LABEL_REPONSES_COLLECTION_QUESTIONS] as $label){ // On parcours les différents labels de la question
                if($label[self::KEY_LABEL_REPONSES_COLLECTION_QUESTIONS] == $value){ // Jusqu'à trouver celui qui match avec la réponse donnée par l'utilisateur
                  foreach($label as $label_score => $value_score){
                    if($label_score != self::KEY_LABEL_REPONSES_COLLECTION_QUESTIONS){
                      if(isset($score[$label_score]))
                        $score[$label_score]+=(int)$value_score;
                      else
                        $score[$label_score]=(int)$value_score;
                    }
                }
                }
              }
            }
          }
        }
      }
      return $score;
  }

  /**
  OBTENIR TABLEAU DES VALEURS POUR LA RÉFÉRENCES $ValRef.
    Le tableau est trié par ordre alphanumérique et chase entrée est : $key => $nbReponse_correspondantes
    (Grouper sur les $key.)
  **/
  public function getValeursReferences($ValRef, $mongoDB){
    $collectionValRef = $mongoDB->selectCollection(              //la collection qui contient les valeurs de références
      self::COLLECTION_VALEURS_REFERENCES);
    $diffReponses = $collectionValRef->distinct($ValRef);       //les différentes réponses dans la base des valeurs de références.
    $cursorReponse = $collectionValRef->find([],[$ValRef=>1]);  //l'ensemble des réponses dans la base des valeurs de références.

    $array_valRef = array();                //le tableau brut des valeurs pour cette référence (pas trié, pas groupé)
    foreach($diffReponses as $key=>$value){ //on récupère les réponses dans la base de références.
      $array_valRef[]=$value;
    }
    sort($array_valRef);                    //tri du tableau sur les clés

    $array_valRef_sorted = array();         //le tableau trié et groupé
    foreach($array_valRef as $k => $v){     //on l'initialise
      $array_valRef_sorted[$v]=0;
    }
    foreach($cursorReponse as $value){      //regroupement des réponses vers le tableau trié.
      foreach($value as $key => $val){
        if($key==$ValRef){                  //évite les entrées vides. (cette colonne peut être vide en base de donnée)
          $array_valRef_sorted[$val]+=1;    //incrément pour cette réponse
        }//end if
      }//end foreach
    }//end foreach
    return $array_valRef_sorted;            //on renvoit le tableau trié et groupé
  }

}//END CLASS
