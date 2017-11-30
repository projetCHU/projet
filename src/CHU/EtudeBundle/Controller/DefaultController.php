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

 use Doctrine\MongoDB\Database;
 use MongoDB\BSON\ObjectID;



/*
authors : LOUIS MARCHAND / JULIEN FONTAINE (fonfonjuju49@gmail.com)
*/

class DefaultController extends Controller
{

  const VUE_HOMEPAGE = "CHUEtudeBundle:Default:index.html.twig";
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

  const PARAM_VUE_LISTE_ETUDES_MODE = 'mode';
  const PARAM_VUE_LISTE_ETUDES_MODE_REPONSES = 'reponses';
  const PARAM_VUE_LISTE_ETUDES_MODE_REPONDRE = 'repondre';
  const PARAM_VUE_LISTE_ETUDES_MODE_SUPPRIMER = 'supprimer';

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
  const CHAMP_LABEL_REPONSES_COLLECTION_QUESTIONS = 'label_reponses';
  const KEY_LABEL_REPONSES_COLLECTION_QUESTIONS = 'label';
  const CHAMP_INDICE_POSITION_COLLECTION_QUESTIONS = 'indice_position';
  const CHAMP_ID_VALEUR_REFERENCE_COLLECTION_QUESTION = 'id_valeur_reference';



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
    public function create_formAction(){
      return $this->render(self::VUE_CREATE_FORM);
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

      $les_vars = $request->request->all();
      $title_form = $les_vars['form_title'];        //on récupère le titre dans la variable POST
      $string_score="";                             //on récupère les scores dans la variable POST
      foreach($les_vars['scores'] as $key => $value){
        if($key==sizeof($les_vars))
          $string_score.="".$value;
        else
          $string_score.=$value.";";
      }
      return $this->render(self::VUE_FORM_BUILDER,array('scores'=>$string_score,'form_title'=>$title_form));
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

      $document = array();
      $document[self::CHAMP_TITRE_COLLECTION_ETUDES]=$request->request->get(self::PARAM_VUE_FORM_BUILDER_TITRE);
      $document[self::CHAMP_CREATEUR_COLLECTION_ETUDES]="a14de5d1dsd"; // ID du créateur de l'étude
      $collection->insert($document);
      $id_etude = new ObjectId($document[self::CHAMP_ID_COLLECTION_ETUDES]);

      $formulaire = $request->request->get(self::PARAM_VUE_FORM_BUILDER_FORMULAIRE);
      $this->persistQuestionsFromHtml($formulaire,$id_etude,$db); // On stocke les différentes questions du formulaire


      // On informe l'utilisateur que l'ajout s'est bien déroulé
      return new Response(" questionnaire enregistré. <a href='\'>retour</a>");
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
      GÉRER LES ACTIONS DISPONIBLES POUR UNE ÉTUDE
    **/
    public function handleChoiceAction(Request $request){
      /**
      * Affichage d'un questionnaire. Blocage des fonction du formulaire.
      * Il s'agit de simplement voir l'apparence du formulaire.
      */
      //verification de l'identité avant de continuer
      //RIEN POUR LE MOMENT

      //on vérifie qu'il s'agit d'une méthode POST
      if(! $request->isMethod('POST'))
        throw new NotFoundHttpException(self::ERROR_PAGE_INEXISTANTE);

      // On choisi la BDD
      $m = $this->container->get(self::MONGO_DATABASE_CONNECTION);
      $db = $m->selectDatabase(self::MONGO_DATABASE_NAME);

      //on récupère le questionnaire demandé
      $id_etude = new ObjectId($request->request->get(self::PARAM_VUE_LISTE_ETUDES_ID_ETUDE));

      //ON REGARDE LA VALEUR DE LA VARIABLE 'mode', et on opère en fonction.
      switch($request->request->get(self::PARAM_VUE_LISTE_ETUDES_MODE)){
        //ON VEUT AFFICHER LES RÉPONSES DE L'ÉTUDE
        case self::PARAM_VUE_LISTE_ETUDES_MODE_REPONSES:
            //si on veut voir les réponses
              //on récupère les réponses à cette étude.
              $collection = $db->selectCollection(self::COLLECTION_REPONSES);
              $filter = array( self::CHAMP_ID_ETUDE_COLLECTION_REPONSES => $id_etude );
              $cursor = $collection->find($filter);

              $reponses = array();
              while($cursor->hasNext()){
                $reponses[] = $cursor->getNext();
              }

              //si il n'y a pas de réponses on le dit.
              if(sizeof($reponses)==0)
                return new Response("Il n'y a pas de réponses à ce questionnaire pour le moment.");

              $score = $this->calculScore($reponses, $id_etude, $db);

              //on affiche la page avec le tableau de réponse
              return new Response($score);
            break;
        //ON VEUT RÉPONDRE À L'ÉTUDE
        case self::PARAM_VUE_LISTE_ETUDES_MODE_REPONDRE:
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
            break;
        //ON VEUT SUPPRIMER L'ÉTUDE
        case self::PARAM_VUE_LISTE_ETUDES_MODE_SUPPRIMER:

              $collection = $db->selectCollection(self::COLLECTION_ETUDES);
              $filter_etude = array( self::CHAMP_ID_COLLECTION_ETUDES => $id_etude );
              $collection->findAndRemove($filter_etude);

              $collection = $db->selectCollection(self::COLLECTION_REPONSES);
              $filter_reponses = array( self::CHAMP_ID_ETUDE_COLLECTION_REPONSES => $id_etude );
              $collection->remove($filter_reponses);

              $collection = $db->selectCollection(self::COLLECTION_QUESTIONS);
              $filter_reponses = array( self::CHAMP_ID_ETUDE_COLLECTION_QUESTIONS => $id_etude );
              $collection->remove($filter_reponses);

              return new Response("L'etude a bien été supprimée. </br> <a href=\"\\etude\">Retour</a>");
            break;
        //CAS D'ÉCHEC
        default:
            throw new NotFoundHttpException(self::ERROR_PAGE_INEXISTANTE);
            break;
      }
    }//fin function

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
      return new Response('réponses enregistrés <a href="\etude"> retour à la liste.</a>');
    }//fin function




/**
      FONCTIONS PRIVÉES.
**/

    /**
     CONVERSION DE CHACUNE DES QUESTIONS EN HTML D'UNE ETUDE ET
     PERSISTENCE DE CHACUNE DES QUESTIONS EN BDD
    */
    private function persistQuestionsFromHtml(String $questionnaire_html, ObjectId $id_etude, Database $database){

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

        $question = array();

        $question[self::CHAMP_ID_ETUDE_COLLECTION_QUESTIONS] = $id_etude;

        //on récupère l'intitulé de la question
        $question[self::CHAMP_LABEL_COLLECTION_QUESTIONS] = trim($ques->label);

        //on récupère le type de question
        $question[self::CHAMP_TYPE_COLLECTION_QUESTIONS] = trim($ques->div->attributes()[self::ATTRIBUT_FORMULAIRE_TYPE]);

        //on teste la nature des reponses attendues
        // et on récupère laliste des réponses si nécessaire.
        $label_reponses = array();
        switch($ques->div->attributes()[self::ATTRIBUT_FORMULAIRE_TYPE]){
          case 'text'://on attend un champ texte.
            //rien à ajouter.
            break;
          case 'textarea':
            //rien à ajouter.
            break;
          case 'radio':
            //on récupère toutes les réponses possibles.
            foreach($ques->div->children() as $reponse){
              $label_reponse[self::KEY_LABEL_REPONSES_COLLECTION_QUESTIONS] = trim($reponse->input->attributes()[self::ATTRIBUT_FORMULAIRE_VALUE]);
              $label_reponse['MS'] = 1;
              $label_reponse['RACHIS'] = 2;
              $label_reponses[] = $label_reponse;
            }
            break;
          case 'checkbox':
            //on récupère toutes les réponses possibles.
            foreach($ques->div->children() as $reponse){
              $label_reponse[self::KEY_LABEL_REPONSES_COLLECTION_QUESTIONS] = trim($reponse->input->attributes()[self::ATTRIBUT_FORMULAIRE_VALUE]);
              $label_reponse['MS'] = 1;
              $label_reponse['RACHIS'] = 2;
              $label_reponses[] = $label_reponse;
            }
            break;
          case 'option'://même traitement que 'option-multiple'
          case 'option-multiple':
            //on récupère toutes les réponses possibles.
            foreach($ques->div->select->children() as $reponse){
              $label_reponse[self::KEY_LABEL_REPONSES_COLLECTION_QUESTIONS] = trim($reponse);
              $label_reponse['MS'] = 1;
              $label_reponse['RACHIS'] = 2;
              $label_reponses[] = $label_reponse;
            }
            break;
        }

        $question[self::CHAMP_LABEL_REPONSES_COLLECTION_QUESTIONS] = $label_reponses;
        $question[self::CHAMP_INDICE_POSITION_COLLECTION_QUESTIONS] = $indice_position;
        $question[self::CHAMP_ID_VALEUR_REFERENCE_COLLECTION_QUESTION] = "NONE";
        $indice_position++;
        // On ajoute la question dans la BDD
        $collection->insert($question);
        $id_questions[] = $question[self::CHAMP_ID_COLLECTION_QUESTIONS];
      }//fin de traitement des questions.

    }

    /**
     RECUPERATION DES QUESTIONS D'UNE ETUDE EN BDD ET CONVERSION DE CELLES-CI EN HTML
    */
    private function fetchQuestionsToHtml(Array $etude, bool $toActivate, Database $database){
      //si la chaine est null ou vide, on renvoit null
      if(! isset($etude))
        return null;
      //sinon, on continue le traitement

      $id_etude = $etude[self::CHAMP_ID_COLLECTION_ETUDES];

      $collection = $database->selectCollection(self::COLLECTION_QUESTIONS);

      $filter = array(self::CHAMP_ID_ETUDE_COLLECTION_QUESTIONS => $id_etude);
      $cursor = $collection->find($filter);

      $sort_filter = array(self::CHAMP_INDICE_POSITION_COLLECTION_QUESTIONS => 1);
      $cursor->sort($sort_filter);

      $questions = array();
      while($cursor->hasNext()){
        $questions[] = $cursor->getNext();
      }
      //la variable string qui va contenir le code html du questionnaire construit
      $html="";
      //on récupère le titre du questionnaire
      $html.="<legend>".$titre = $etude[self::CHAMP_TITRE_COLLECTION_ETUDES]."</legend>";
      $html.='<input type="hidden" name="id_etude" id="id_etude" value="'.$id_etude.'">';


      //si il y a des questions
      if(sizeof($questions)!=0)
      {
        //on récupère les questions;
        foreach($questions as $question){
          //la balise div qui entoure une question
          $enteteQuestion='<div class="form-group" style="cursor: pointer;">';
          //titre question
          $titreQuestion="";
          //option de la question
          $optionQuestion="";

          //numéro de question
          $numero = $question[self::CHAMP_ID_COLLECTION_QUESTIONS];
          //l'intitulé de la question
          $intitule = $question[self::CHAMP_LABEL_COLLECTION_QUESTIONS];
          //le type de la question
          $type = $question[self::CHAMP_TYPE_COLLECTION_QUESTIONS];

          $reponses = null;
          //on récupère les réponses possibles selon le type de question
          //et on construit l'intérieur de la balise <div> de cette question
          switch($type){
            case 'text':
              # code...
                //entete
                $enteteQuestion.='<!-- Champs de texte -->';
                //titre
                $titreQuestion.='<label class="control-label col-sm-4" for="textInput" style="cursor: pointer;">';
                $titreQuestion.=$intitule.'</label>';
                //options
                #PAS SUR QUE METTRE LE TYPE DE REPONSE DANS LA BALISE SOIT UTILE
                $optionQuestion.='<div class="col-sm-7" reponseType="text">';
                #A MODIFIER POUR METTRE LE BON TAG POUR LE NOM DE LA BALISE
                $optionQuestion.='<input id="textInput" name="'.$numero.'" class="form-control" type="text" />';
                $optionQuestion.='</div>';
              break;
            case 'textarea':
              # code...
              //entete
              $enteteQuestion.='<!-- Champ de texte : Commentaire -->';
              //titre
              $titreQuestion.='<label class="col-sm-4 control-label" for="textInput">';
              $titreQuestion.=$intitule.'</label>';
              //options
              #PAS SUR QUE METTRE LE TYPE DE REPONSE DANS LA BALISE SOIT UTILE
              $optionQuestion.='<div class="col-sm-7" reponseType="textarea">';
              #A MODIFIER POUR METTRE LE BON TAG POUR LE NOM DE LA BALISE
              $optionQuestion.='<input id="textareaInput" name="'.$numero.'" class="form-control" type="textarea" />';
              $optionQuestion.='</div>';
              break;
            case 'radio':
              # code...
              //on récupère les réponses
              $reponses = $question[self::CHAMP_LABEL_REPONSES_COLLECTION_QUESTIONS];
              //entete
              $enteteQuestion.='<!-- Multiple radios -->';
              //titre
              $titreQuestion.='<label class="col-sm-4 control-label" for="textInput">';
              $titreQuestion.=$intitule.'</label>';
              //options
              $optionQuestion.='<div class="col-sm-7" >';

              #POUR CHAQUE_QUESTION
              $ind=0;
              foreach($question[self::CHAMP_LABEL_REPONSES_COLLECTION_QUESTIONS] as $reponse){
                  $optionQuestion .= '<label class="radio" for="sexe">';
                  $optionQuestion .= '<input name="'.$numero.'" id="radios-'.$ind.'" value="'.$reponse[self::KEY_LABEL_REPONSES_COLLECTION_QUESTIONS];
                  $optionQuestion .= '" type="radio" />'.$reponse[self::KEY_LABEL_REPONSES_COLLECTION_QUESTIONS];
                  $optionQuestion .= '</label>';
                  $ind++;
                }
                //on ferme les options
              $optionQuestion.='</div>';
              break;
            case 'checkbox':
              # code...
              //on récupère les réponses
              $reponses = $question[self::CHAMP_LABEL_REPONSES_COLLECTION_QUESTIONS];
              //entete
              $enteteQuestion.='<!-- Multiple checkboxes -->';
              //titre
              $titreQuestion.='<label class="col-sm-4 control-label" for="textInput">';
              $titreQuestion.=$intitule.'</label>';

              //options
              $optionQuestion.='<div class="col-sm-7" >';
              #POUR CHAQUE_QUESTION
              #PERSONALISER LE NAME EN FONCTION DE LA QUESTION QUAND IL Y AURA PLUSIEURS QUESTIONS DE CE TYPE
              $ind=0;
              foreach($question[self::CHAMP_LABEL_REPONSES_COLLECTION_QUESTIONS] as $reponse){
                    $optionQuestion .= '<label class="checkbox" for="loc-douleur">';
                    $optionQuestion .= '<input name="'.$numero.'[]" id="checkboxes-'.$ind.'" value="'.$reponse[self::KEY_LABEL_REPONSES_COLLECTION_QUESTIONS];
                    $optionQuestion .= '" type="checkbox" />'.$reponse[self::KEY_LABEL_REPONSES_COLLECTION_QUESTIONS];
                    $optionQuestion .= '</label>';
                    $ind++;
                }
              //on ferme les options
              $optionQuestion.='</div>';
              break;
            case 'option':
              //entete
              $enteteQuestion.='<!-- Select Single -->';
              //titre
              $titreQuestion.='<label class="col-sm-4 control-label" for="selectSingle">';
              $titreQuestion.=$intitule.'</label>';
              //options
              $optionQuestion.='<div class="col-sm-7" >';

              #POUR CHAQUE_QUESTION
              #PERSONALISER LE NAME EN FONCTION DE LA QUESTION QUAN IL Y AURA PLUSIEURS QUESTIONS DE CE TYPE
              $optionQuestion.='<select id="selectSingle" name="'.$numero.'" class="form-control">';

              foreach($question[self::CHAMP_LABEL_REPONSES_COLLECTION_QUESTIONS] as $reponse){
                $optionQuestion .= '<option value="'.$reponse[self::KEY_LABEL_REPONSES_COLLECTION_QUESTIONS].'">';
                $optionQuestion .= $reponse[self::KEY_LABEL_REPONSES_COLLECTION_QUESTIONS].'</option>';
              }
              $optionQuestion.='</select>';

              //on ferme les options
              $optionQuestion.='</div>';
              break;
            case 'option-multiple':
              //entete
              $enteteQuestion.='<!-- Select Single -->';
              //titre
              $titreQuestion.='<label class="col-sm-4 control-label" for="selectMultiple">';
              $titreQuestion.=$intitule.'</label>';
              //options
              $optionQuestion.='<div class="col-sm-7" >';

              #POUR CHAQUE_QUESTION
              #PERSONALISER LE NAME EN FONCTION DE LA QUESTION QUAN IL Y AURA PLUSIEURS QUESTIONS DE CE TYPE
              $optionQuestion.='<select id="selectMultiple"  name="'.$numero.'[]" class="form-control" multiple>';

              //on récupère les réponses
              foreach($question[self::CHAMP_LABEL_REPONSES_COLLECTION_QUESTIONS] as $reponse){
                  $optionQuestion .= '<option value="'.$reponse[self::KEY_LABEL_REPONSES_COLLECTION_QUESTIONS].'">';
                  $optionQuestion .= $reponse[self::KEY_LABEL_REPONSES_COLLECTION_QUESTIONS].'</option>';
              }
              $optionQuestion.='</select>';

              //on ferme les options
              $optionQuestion.='</div>';
              break;
          }

          //on remplit la question dans le html
          $html.=$enteteQuestion.$titreQuestion.$optionQuestion;
          $html.='</div>';//et on ferme la div de la question
        }//END FOR EACH
      }//endif

      //on renvoit la chaine le questionnaire construit sous format html
      return $html;
    }

    private function calculScore(Array $reponses, ObjectId $id_etude, Database $database){

      $collection = $database->selectCollection(self::COLLECTION_QUESTIONS);
      $filter = array(self::CHAMP_ID_ETUDE_COLLECTION_QUESTIONS => $id_etude);
      $cursor = $collection->find($filter);

      $questions = array();
      while($cursor->hasNext()){
        $questions[] = $cursor->getNext();
      }

      $score = array( 'MS' => 0 , 'RACHIS' => 0);

      $reponse = $reponses[0]; // POUR FIXER A UN ET UN SEUL USER (cas d'exemple)
      foreach($reponse as $key => $value){ // pour chaque reponse on récupère l'id de la question et la/les reponses données
        if( $key != '_id' && $key != 'id_etude' && $key != 'id_utilisateur'){
          foreach($questions as $question){ // on cherche à quelle question de l'etude l'id récupéré dans la reponse fait référence
            if($key == $question[self::CHAMP_ID_COLLECTION_QUESTIONS]){
              foreach($question[self::CHAMP_LABEL_REPONSES_COLLECTION_QUESTIONS] as $label){ // On parcours les différents labels de la question
                if($label[self::KEY_LABEL_REPONSES_COLLECTION_QUESTIONS] == $value){ // Jusqu'à trouver celui qui match avec la réponse donnée par l'utilisateur
                  $score['RACHIS'] += $label['RACHIS'];
                  $score['MS'] += $label['MS'];
                }
              }
            }
          }
        }
      }

      return json_encode($score);
  }
}
