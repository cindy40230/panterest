<?php

namespace App\Controller;

use App\Form\UserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    /**
     * @Route("/account", name="app_account",methods="GET")
     */
    public function show(): Response
    {
        return $this->render('account/show.html.twig');
    }

    /**
     * @Route("/account/edit", name="app_account_edit",methods={"GET","POST"})
     */
    public function edit(Request $request,EntityManagerInterface $em): Response
    {
        //recuperation de l utilisateur connecter
        $user=$this->getUser();
        //on le passe dans le formulaire de facon a ce qu il soit prérempli
        $form=$this->createForm(UserFormType::class,$user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) { 
        //aplliquer les modifications
            $em->flush();

             //ajout d un message flash modifier avec success
             $this->addFlash('success','account udapted sucessfully!');
             //redirection vers le profile
             return $this->redirectToRoute('app_account');

        }

        return $this->render('account/edit.html.twig',[
            'form'=>$form->createView()
        ]);
    }
}
