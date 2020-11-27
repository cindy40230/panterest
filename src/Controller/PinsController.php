<?php

namespace App\Controller;

use App\Repository\PinRepository;
use App\Entity\Pin;
use App\Form\PinType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PinsController extends AbstractController
{
    /**
     * @Route("/", name="app_home",methods={"GET"})
     */
    public function index(PinRepository $pinRepository): Response
    {
       // $pins= $pinRepository->findAll(); // on stock dans une variable le tableau qui recupère tous les éléments grace à la méthode findAll()
       $pins = $pinRepository->findBy([],['createdAt'=>'DESC']);//pour recuperer tous les pins (1er argument)en ordre du plus recent au plus ancien DESC deuxième argument
        return $this->render('pins/index.html.twig',compact('pins'));//compact('pins') est l'équivalent de ['pins'=>$pins]
    }
    /**
     * @Route("/pins/create", name="app_pins_create",methods={"GET","POST"})
     */
    public function create(Request  $request,EntityManagerInterface $em):Response
    {
        $pin=new Pin;

        $form = $this -> createForm(PinType::class,$pin) ;//on utilise formBuilder et en argument un objet
       
        //dd($form);
        $form->handleRequest($request);//mon fromulaire faut gerer la requete (ceci va nous permettre de recuperer les données passé dans le formulaire)
        
        if ($form->isSubmitted() && $form->isValid()) { 
           
            //dd($pin);
            $em->persist($pin);
            $em->flush();

            //redirection vers la page d accueil
            return $this->redirectToRoute('app_home');
        }

      
       return $this->render('pins/create.html.twig',['form'=>$form->createView()]);
    }


    /**
     * @Route("/pins/{id<[0-9]>}", name="app_pins_show",methods={"GET"})
     */
    public function show(Pin $pin):Response
    {
       
       //dd($pin);
       return $this->render('pins/show.html.twig',compact('pin'));
    }


    /**
     * @Route("/pins/{id<[0-9]>}/edit", name="app_pins_edit",methods={"GET","PUT"})
     */
    public function edit(Pin $pin,EntityManagerInterface $em,Request  $request):Response
    {
        $form = $this -> createForm(PinType::class,$pin,[
            'method'=>'PUT'
        ]);
        

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) { 
           
            //dd($pin);

            $em->flush();//pas besoin de persister puisque nous avons deja recuperer le pin

            //redirection vers la page d accueil
            return $this->redirectToRoute('app_home');
        }

       //dd($pin);
       return $this->render('pins/edit.html.twig',['form'=>$form->createView(),'pin'=>$pin]);
    }

    
}
