<?php

namespace App\Controller;

use App\Repository\PinRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PinsController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     */
    public function index(PinRepository $pinRepository): Response
    {
        $pins= $pinRepository->findAll(); // on stock dans une variable le tableau qui recupère tous les éléments grace à la méthode findAll()
       
        return $this->render('pins/index.html.twig',compact('pins'));//compact('pins') est l'équivalent de ['pins'=>$pins]
    }
}
