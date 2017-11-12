<?php

namespace CHU\EtudeBundle\Entity;


/**

N'EST PAS UTILISÉ POUR LE MOMENT.

*/

use Doctrine\ORM\Mapping as ORM;

/**
 * Question
 *
 * @ORM\Table(name="question")
 * @ORM\Entity(repositoryClass="CHU\EtudeBundle\Repository\QuestionRepository")
 */
class Question
{

    //constructeur
    public function __construct($question_name = null){
      $this->setContenu($question_name);
    }




    /**
     * @ORM\ManyToOne(targetEntity="CHU\EtudeBundle\Entity\Etude")
     */
    private $etude;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="Contenu", type="text")
     */
    private $contenu;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set contenu
     *
     * @param string $contenu
     *
     * @return Question
     */
    public function setContenu($contenu)
    {
        $this->contenu = $contenu;

        return $this;
    }

    /**
     * Get contenu
     *
     * @return string
     */
    public function getContenu()
    {
        return $this->contenu;
    }

    /**
     * Set questionType
     *
     * @param \CHU\EtudeBundle\Entity\Question_Type $questionType
     *
     * @return Question
     */
    public function setQuestionType(\CHU\EtudeBundle\Entity\Question_Type $questionType = null)
    {
        $this->question_type = $questionType;

        return $this;
    }

    /**
     * Get questionType
     *
     * @return \CHU\EtudeBundle\Entity\Question_Type
     */
    public function getQuestionType()
    {
        return $this->question_type;
    }

    /**
     * Set etude
     *
     * @param \CHU\EtudeBundle\Entity\Etude $etude
     *
     * @return Question
     */
    public function setEtude(\CHU\EtudeBundle\Entity\Etude $etude = null)
    {
        $this->etude = $etude;

        return $this;
    }

    /**
     * Get etude
     *
     * @return \CHU\EtudeBundle\Entity\Etude
     */
    public function getEtude()
    {
        return $this->etude;
    }
}
