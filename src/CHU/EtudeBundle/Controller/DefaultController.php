<?php

namespace CHU\EtudeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

//include des entités nécessaires.
use CHU\EtudeBundle\Entity\Question;
use CHU\EtudeBundle\Entity\Etude;
use CHU\EtudeBundle\Entity\Answer;



/****
LOUIS MARCHAND
date : 11 12 17


PETITES INSTRUCTIONS POUR LA GENERATION DE QUESTIONNAIRE. V.2

#1 Le formbuilder, (disponible ici : /build_form ) génère des formulaires sous format html.
    Le questionnaire sous format HTML est récupérer et parser pour en faire une chaine JSON.
    Cette chaine JSON est stockée dans l'entite Etude.

#2 Quand on affiche un questionnaire pour y répondre ou pour le voir simplement,
    on va récupérer la chaine JSON du questionnaire dans l'entité Etude, et on parse
    la chaine JSON pour reconstruire le questionnaire sous format HTML.

#3 Quand on répond à un questionnaire, on récupère les réponses dans que l'on parse
    et convertit en chaine JSON. Cette chaine JSON est stocké dans l'entité Answer.

#4 Quand on affiche les réponses à un questionnaire, on va chercher toutes les réponses à
    celui-ci. On convertit toutes les chaines JSON des réponses et on les insert dans un
    tableau HTML qui est construit automatiquement à partir du tableau des chaines JSON des
    réponses.
    (voir : ARRAY_JSON_REPONSES_To_HTML_TAB($JSON_array,bool $option_titre) )

#REMARQUES:
  Toutes fonctions de 'parsing' et de conversion de format sont à la fin du fichier dans
    la section :  FONCTIONS PRIVÉES

**/




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

  const ROUTE_QUEST_DONE = 'questionnaire_repondu';


    /**
      FUNCTION DE LA PAGE D'ACCUEIL
    **/
    public function indexAction()
    {
        return $this->render(self::VUE_HOMEPAGE);
    }


    /**
      AFFICHER LE FORMBUILDER POUR CONSTRUIRE UN QUESTIONNAIRE.
    **/
    public function build_formAction(){
      /**
      * Ici on afficher le form_builder à l'utilisateur
      */
      $content = $this->get('templating')->render(self::VUE_FORM_BUILDER);
      return new Response($content);
    }

    /**
      FONCTION QUI GÈRE LA VALIDATION DE LA CRÉATION D'UN QUESTIONNAIRE.
    **/
    public function submit_builded_formAction(Request $request){
      /**
      * Gestionnaire de validation du questionnaire créé.
      */
      //on vérifie qu'il s'agit d'une méthode POST
      if(! $request->isMethod('POST'))
        return new Response('Vous ne devriez pas être là.');
      //si methode POST

      //on vérifie qu'on reçoit bien quelque chose
      if($request->request->get('form_builded')==null || sizeof($request->request->get('form_builded'))==0)
        return new Response('Erreur lors de la réception des élements.');

      //onrécupère le formulaire et on le transcrit en une chaîne JSON.
      $JSONQuest = $this->HTML_Form_To_JSON(
        array('form_title'=>$request->request->get('form_title'),
              'form_builded'=>$request->request->get('form_builded')
            )
          );

      //si non vide alors
      $em = $this->getDoctrine()->getManager(); //le entity manager
      //on créer une nouvelle Etude avec le bon arguments.
      $new_Etude = new Etude(); //nouvelle Etude
      $new_Etude->setName($request->request->get('form_title')); //on renseigne son nom
      $new_Etude->setContent($JSONQuest); //on renseigne son contenu

      $em->persist($new_Etude);
      $em->flush();

      //mettre une annonce flash pour signaler l'ajout
      return new Response("questionnaire enregistré. <a href='\'>retour</a>");
    }

    /**
      AFFICHER LA LISTE DES QUESTIONNAIRES
    **/
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

    /**
      AAFFICHER LE QUESTIONNAIRE EN MODE DÉSACTIVÉ
    **/
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
      switch($request->request->get('mode')){
        case 'reponses':
            //si on veut voir les réponses
              //on récupère les Réponses à cette étude.
              $query = $em->createQuery("select a from CHUEtudeBundle:Answer a join a.etude e where e.id = :id");
              $query->setParameter('id', $idetude);
              $reponses = $query->getResult();
              foreach($reponses as $rep){
                $tab_reponses_JSON[]=$rep->getReponses();
              }
              //si il n'y a pas de réponses on le dit.
              if(sizeof($tab_reponses_JSON)==0)
                return new Response("Il n'y a pas de réponses à ce questionnaire pour le moment.");

              //on récupère la liste des réponses et on convertir en un beau tableau HTML prêt à l'emploi
              $tab_reponses_HTML = $this->ARRAY_JSON_REPONSES_To_HTML_TAB($tab_reponses_JSON,true);
              //on affiche la page avec le tableau de réponse
              return $this->render(self::VUE_SHOW_REPONSES,array('tab_reponse'=>$tab_reponses_HTML));
            # code...
            break;
        case 'repondre':
            //si on veut y répondre
              //on récupère le format html du questionnaire, et activé
              $HTMLQuest = $this->JSON_To_HTML_Form($letude->getContent(),true);
              //alors on ouvre le questionnaire afin de pouvoir y repondre
              return $this->render(self::VUE_DO_QUESTIONNAIRE,
                array('etude_name'=>$letude->getName(),'etude_form'=>$HTMLQuest));
            # code...
            break;
        case 'supprimer':
          //si on veut supprimer
          //alors on ouvre le questionnaire afin de pouvoir y repondre
              $em->remove($letude);
              $em->flush();
              return new Response("L'etude a bien été supprimée.");
            # code...
            break;
        case 'voir':
              //on récupère le format html du questionnaire, et désactivé
              $htmlQuest = $this->JSON_To_HTML_Form($letude->getContent(),false);
              //si on veut afficher le questionnaire simplement
              //on envoit le questionnaire dans la vue de questionnaire
              return $this->render(self::VUE_SHOW_QUESTIONNAIRE,
                array('etude_name'=>$letude->getName(),'etude_form'=>$htmlQuest));
          # code...
          break;
        default:
            return new Response('Vous ne devriez pas être là');
            break;
      }
    }//fin function



    /**
      GÉRER LA RÉPONSE À UN QUESTIONNAIRE
    **/
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
      $array_rep = $request->request->all();
      //on récupère le titre de la question
      $titre = $array_rep['form_title'];
      //on retire le titre du tableau. ne reste plus que les réponses
      unset($array_rep['form_title']);

      //on récupère l'étude correspondant au nom.
      //on rapelle que les noms d'étude sont uniques.
      $letude = $em->getRepository(self::ENTITY_ETUDE)->findByName($titre)[0];

      //on récupère les réponses du formulaire et on les transcrit sous format JSON
      $reponses = $this->ARRAY_Rep_To_JSON($array_rep);


      //on créer une entité Answer pour stocker la Réponse
      $answer = new Answer();
      $answer->setEtude($letude);//on renseigne l'étude concernée.
      $answer->setReponses($reponses);//on renseigne la chaine JSON des réponses.

      //on rensigne l'id du participant,
      //POUR LE MOMENT CE SERA TOUJOURS LE MÊME, CAR IL N'Y A PAS DE LIEN AVEC LA GESTION UTILISATEUR.
      $answer->setParticipant(666);

      /**
      ON VÉRIFIE QUE LE PARTICIPANT N'AIE PAS DÉJÀ RÉPONDU.
      */
      /*
      $query = $em->createQuery("select a from CHUEtudeBundle:Answer a join a.etude e where e.id = :id_etude and a.participant = :id_participant");
      $query->setParameter('id_etude', $letude->getId());
      $query->setParameter('id_participant',666);
      //on stocke le tableau de résultat.
      $answer_exist = $query->getResult();
      //si c'est le cas
      if(sizeof($answer_exist)!=0)//on lui dit
        return new Response('Vous avez déjà répondu à ce questionnaire.');
      //*/
      //sinon, on continu

      //on persiste et on sauvegarde.
      $em->persist($answer);
      $em->flush();

      return new Response('réponses enregistrés <a href="\Etude"> retour à la liste.</a>');
    }//fin function

    /**
        GÉRER LA SUPPRESSION D'UNE RÉPONSE
    **/
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
    }//fin function



/**
      FONCTIONS PRIVÉES.
**/

/**
      METHODES POUR LA TRANSCRIPTION DE RÉPONSES
        ARRAY_REPONSE -> JSON_REPONSE
        ARRAY_JSON_REPONSES -> HTML_TABLEAU_REPONSES

      METHODES POUR LA TRANSCRIPTION DE QUESTIONNAIRE
        HTML_QUESTIONNAIRE -> JSON_QUESTIONNAIRE
        JSON_QUESTIONNAIRE -> HTML_QUESTIONNAIRE
**/

    /**
      TRANSFORME UN TABLEAU, DE RÉPONSES À UN QUESTIONNAIRE, EN UNE CHAINE JSON.
    REMARQUE:
      -la réponse est construite sous format d'une chaine JSON
        exemple : { "nom_quest1": "reponse1", "nom_quest2": "reponse2", "nom_quest3":["reponse3.1","reponse3.2"], etc...}
    */
    private function ARRAY_Rep_To_JSON($array_rep){
      if(!isset($array_rep) || sizeof($array_rep)==0)
        return null;

      $JSONrep = "{";//début chaine JSON

      foreach($array_rep as $q_name=>$q_rep){
          //intitulé question
          $JSONrep.=' "'.$q_name.'" : ';
          if(is_array($q_rep) && sizeof($q_rep)!=0){
            $JSONrep.='[';
            foreach($q_rep as $rep){
              $JSONrep.=' "'.$rep.'",';
            }
            $JSONrep=substr($JSONrep,0,-1);//on retire la dernière virgule
            $JSONrep.=']';
          }
          else{//qu'une seule réponse
            $JSONrep.='"'.$q_rep.'"';
          }
          //fin de la réponse
          $JSONrep.=',';
      }//fin foreach
      //on enlève la dernière virgule
      $JSONrep=substr($JSONrep,0,-1);//on retire la dernière virgule
      //fin de la chaine JSON de la reponse
      $JSONrep.='}';

      return $JSONrep;
    }

    /**
      TRANSFORME UN TABLEAU DE PLUSIEURS RÉPONSES À UNE MÊME ÉTUDE EN UN TABLEAU HTML.
      REMARQUE:
        Chaque entrée dans le tableau $JSON_array représente les réponses d'une personne au questionnaire
    */
    private function ARRAY_JSON_REPONSES_To_HTML_TAB($JSON_array,bool $option_titre){
      if(!isset($JSON_array)){
        print 'on passe ici';
        return null;
      }

      //début du tableau HTML
      $HTML_tab='<table>';

      //En tete des colonnes du tableau html
      if($option_titre){//si l'option est activée
        $HTML_tab.='<tr class="row">';
        foreach(json_decode($JSON_array[0],true) as $q_title => $q_rep){
          $HTML_tab.='<th class="column">'.$q_title.'</th>';
        }
        $HTML_tab.='</tr>';
      }

      //pour chaque Réponses
      foreach($JSON_array as $JSON_rep){
        //var_dump($JSON_rep);
        //on transforme la chaine JSON en tableau.
        $reponses = json_decode($JSON_rep,true);
        //debut de ligne dans tableau
        $HTML_tab.='<tr class="row">';
        //pour chaque reponse dans la Réponse, (pour chaque case de la ligne)
        foreach($reponses as $q_title => $q_rep){
          $HTML_tab.='<td class="column">';
          //si la réponse est en fait plusieurs réponses (exemple: checkbox)
          if(is_array($q_rep) && sizeof($q_rep)!=0){
            $rep_multiple="";
            foreach($q_rep as $rep)//pour chaque sous_reponse
              $rep_multiple.=$rep.',';
            $rep_multiple=substr($rep_multiple,0,-1);//on enlève la dernière virgule
            $HTML_tab.=$rep_multiple;//on ajoute la reponse multiple dans la case
          }
          else//sinon, c'est une réponse simple
            $HTML_tab.=$q_rep;//on ajoute simplement la réponse dans la case
          //on ferme la case
          $HTML_tab.='</td>';
        }//foreach fin. fin de Case du tableau
        //on ferme la ligne du tableau
        $HTML_tab.='</tr>';//fin de ligne
      }//foreach fin, (pour chaque ligne)
      //fin du tableau HTML
      $HTML_tab.='</table>';

      return $HTML_tab;//on renvoie le tableau HTML, sous forme de chaine standard
    }

    /**
     TRANSFORME UN QUESTIONNAIRE SOUS FORMAT HTML EN JSON
    */
    private function HTML_Form_To_JSON($Array_param)
    {
      if(! isset($Array_param) || sizeof($Array_param) == 0 )
        return null;

      //CHARGEMENT DU QUESTIONNAIRE CRÉÉ
      //On récupère le questionnaire créé
      //et on ajoute l'entête xml
      $questionnaire ="<?xml version='1.0' standalone='yes'?>".$Array_param['form_builded'];

      //on charge le parser xml
      $xml = simplexml_load_string($questionnaire);
      //chaque question est représenter par une balise div directement sous la balise form
      //on récupère l'enseble des questions.
      $questions = null;
      foreach($xml->children() as $child){
        $questions[] = $child;
      }

      //début du questionnaire
      $JSONQuest='{';
      //titre de l'étude
      $JSONQuest.=' "titre" : "'.$Array_param['form_title'].'", ';
      //début des questions
      $JSONQuest.='"questionnaire" : [';
      //on créer une chaîne JSON qui va contenir l'ensemble des questions du questionnaire.
      foreach($questions as $ques){
        print($ques);
        //debut de la question
        $JSONQuest.='{';

        //on récupère l'intitulé de la question
        $JSONQuest.='"label" : "'.trim($ques->label).'",';
        //on récupère le type de réponse
        $JSONQuest.='"type" : "'.trim($ques->div->attributes()['type']).'"';

        //on teste la nature des reponses attendues
        // et on récupère laliste des réponses si nécessaire.
        switch($ques->div->attributes()['type']){
          case 'text'://on attend un champ texte.
            # code...   //rien à ajouter.
            break;
          case 'textarea':
            # code...   //rien à ajouter.
            break;
          case 'radio':
            # code...
            $JSONQuest.=', "reponses" : [';
            //on récupère toutes les réponses possibles.

            foreach($ques->div->children() as $reponse){
              $JSONQuest.='"'.trim($reponse->input->attributes()['value']).'",';
            }
            $JSONQuest = substr($JSONQuest, 0, -1);//on retire la dernière virgule
            $JSONQuest.=']';//on ferme les reponses
            break;
          case 'checkbox':
            # code...
            $JSONQuest.=', "reponses" : [';
            //on récupère toutes les réponses possibles.

            foreach($ques->div->children() as $reponse){
              $JSONQuest.='"'.trim($reponse->input->attributes()['value']).'",';
            }
            $JSONQuest = substr($JSONQuest, 0, -1);//on retire la dernière virgule
            $JSONQuest.=']';//on ferme les reponses
            break;
          case 'option':
            # code...
            $JSONQuest.=', "reponses" : [';
            //on récupère toutes les réponses possibles.
            foreach($ques->div->select->children() as $reponse){
              $JSONQuest.='"'.trim($reponse).'",';
            }
            $JSONQuest = substr($JSONQuest, 0, -1);//on retire la dernière virgule
            $JSONQuest.=']';//on ferme les reponses
            break;
        }
        //on ferme la question
        $JSONQuest.='},';
      }//fin de traitement des questions.
      $JSONQuest = substr($JSONQuest, 0, -1);//on retire la dernière virgule
      //fin du qustionnaire
      $JSONQuest.='] }';

      //debug
      //print($JSONQuest);

      return $JSONQuest;
    }



    /**
     TRANSFORME UN QUESTIONNAIRE SOUS FORMAT JSON EN HTML
    */
    private function JSON_To_HTML_Form(String $JSONQuest,bool $toActivate){
      //si la chaine est null ou vide, on renvoit null
      if(! isset($JSONQuest) || $JSONQuest == "")
        return null;
      //sinon, on continue le traitement

      //on transforme la chaine JSON en tableau.
      $ArrayQuest = json_decode($JSONQuest, true);

      //la variable string qui va contenir le code html du questionnaire construit
      $html="";
      //on récupère le titre du questionnaire
      $TitreQuest = $ArrayQuest['titre'];
      $html.="<legend>".$TitreQuest."</legend>";
      $html.='<input type="text" name="form_title" hidden value="'.$TitreQuest.'"/>';

      //si il y a des questions
      if(sizeof($ArrayQuest)!=0)
      {
        //on récupère les questions;
        foreach($ArrayQuest['questionnaire'] as $questionJSON){
          //la balise div qui entoure une question
          $enteteQuestion='<div class="form-group" style="cursor: pointer;">';
          //titre question
          $titreQuestion="";
          //option de la question
          $optionQuestion="";

          //l'intitulé de la question
          $intitule = $questionJSON['label'];
          //le type de la question
          $type = $questionJSON['type'];

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
                $optionQuestion.='<input id="textInput" name="'.$intitule.'" class="form-control" type="text" />';
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
              $optionQuestion.='<input id="textareaInput" name="'.$intitule.'" class="form-control" type="textarea" />';
              $optionQuestion.='</div>';
              break;
            case 'radio':
              # code...
              //on récupère les réponses
              $reponses = $questionJSON['reponses'];
              //entete
              $enteteQuestion.='<!-- Multiple radios -->';
              //titre
              $titreQuestion.='<label class="col-sm-4 control-label" for="textInput">';
              $titreQuestion.=$intitule.'</label>';
              //options
              $optionQuestion.='<div class="col-sm-7" >';

              #POUR CHAQUE_QUESTION
              $ind=0;
              foreach($reponses as $reponse)
                {
                  $optionQuestion.='<label class="radio" for="sexe">';
                  $optionQuestion.='<input name="'.$intitule.'" id="radios-'.$ind.'" value="'.$reponse.'" type="radio" />'.$reponse;
                  $optionQuestion.='</label>';
                  $ind++;
                }
                //on ferme les options
              $optionQuestion.='</div>';
              break;
            case 'option':
              # code...
              //on récupère les réponses
              $reponses = $questionJSON['reponses'];
              //entete
              $enteteQuestion.='<!-- Select Single -->';
              //titre
              $titreQuestion.='<label class="col-sm-4 control-label" for="selectSingle">';
              $titreQuestion.=$intitule.'</label>';
              //options
              $optionQuestion.='<div class="col-sm-7" >';

              #POUR CHAQUE_QUESTION
              #PERSONALISER LE NAME EN FONCTION DE LA QUESTION QUAN IL Y AURA PLUSIEURS QUESTIONS DE CE TYPE
              $optionQuestion.='<select id="selectSingle" name="'.$intitule.'" class="form-control">';
              foreach($reponses as $reponse)
                {
                  $optionQuestion.='<option value="'.$reponse.'">'.$reponse.'</option>';
                }
              $optionQuestion.='</select>';

              //on ferme les options
              $optionQuestion.='</div>';
              break;
            case 'checkbox':
              # code...
              //on récupère les réponses
              $reponses = $questionJSON['reponses'];
              //entete
              $enteteQuestion.='<!-- Multiple checkboxes -->';
              //titre
              $titreQuestion.='<label class="col-sm-4 control-label" for="textInput">';
              $titreQuestion.=$intitule.'</label>';

              //options
              $optionQuestion.='<div class="col-sm-7" >';
              #POUR CHAQUE_QUESTION
              #PERSONALISER LE NAME EN FONCTION DE LA QUESTION QUAN IL Y AURA PLUSIEURS QUESTIONS DE CE TYPE
              $ind=0;
              foreach($reponses as $reponse)
                {
                  $optionQuestion.='<label class="checkbox" for="loc-douleur">';
                  $optionQuestion.='<input name="'.$intitule.'[]" id="checkboxes-'.$ind.'" value="'.$reponse.'" type="checkbox" />'.$reponse;
                  $optionQuestion.='</label>';
                  $ind++;
                }
              //on ferme les options
              $optionQuestion.='</div>';
              break;
          }

          //on remplit la question dans le html
          $html.=$enteteQuestion.$titreQuestion.$optionQuestion;
          $html.='</div>';//et on ferme la div de la question
        }//END FOR EACH

        //on ajoute le bouton submit, si le formulaire doit être activé.
        if($toActivate){
          //on insert l'en-tête formulaire
          $html='<form class="form-horizontal area" method="post" action="'.self::ROUTE_QUEST_DONE.'">'.$html;
          $html.='<input type="submit"/>';
        }
        else{//juste pour afficher le formulaire; il est désactivé.
          $html='<form class="form-horizontal area">'.$html;
        }
        //on ferme le formulaire.
        $html.='</form>';
      }//endif

      //on renvoit la chaine le questionnaire construit sous format html
      return $html;
    }
  }
