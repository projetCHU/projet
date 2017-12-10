<?php

namespace CHU\PatientBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('CHUPatientBundle:Default:index.html.twig');
    }










}
