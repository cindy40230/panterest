<?php

namespace App\Controller;

use App\Form\UserFormType;
use App\Form\ChangePasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

/**
 * @Route("/account")
 */
class AccountController extends AbstractController
{
    /**
     * @Route("", name="app_account",methods="GET")
     */
    public function show(): Response
    {
        return $this->render('account/show.html.twig');
    }

    /**
     * @Route("/edit", name="app_account_edit",methods={"GET","POST"})
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

    /**
     * @Route("/change-password", name="app_account_change_password",methods={"GET","POST"})
     */
    public function changePassword(Request $request,EntityManagerInterface $em,UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user=$this->getUser();
        $form = $this->createForm(ChangePasswordFormType::class,null,[
            'current_password_is_required'=> true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) { 
            
            $user->setPassword($passwordEncoder->encodePassword($user,$form['plainPassword']->getData()));
            $em->flush();
            //ajout d un message flash modifier avec success
            $this->addFlash('success','Password Udapted Sucessfully!');
            //redirection vers le profile
            return $this->redirectToRoute('app_account');
            }
    

        return $this->render('account/change_password.html.twig',[
            'form'=>$form->createView()
        ]);
    }
}
