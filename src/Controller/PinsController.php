<?php

namespace App\Controller;

use App\Repository\PinRepository;
use App\Entity\Pin;
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
       // $pins= $pinRepository->findAll(); // on stock dans une variable le tableau qui recupère tous les éléments grace à la méthode findAll()
       $pins = $pinRepository->findBy([],['createdAt'=>'DESC']);//pour recuperer tous les pins (1er argument)en ordre du plus recent au plus ancien DESC deuxième argument
        return $this->render('pins/index.html.twig',compact('pins'));//compact('pins') est l'équivalent de ['pins'=>$pins]
    }

    /**
     * @Route("/pins/{id<[0-9]>}", name="app_pins_show")
     */
    public function show(Pin $pin):Response
    {
       
       //dd($pin);
       return $this->render('pins/show.html.twig',compact('pin'));
    }
}
