<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\EmailVerifier;
use App\Form\RegistrationFormType;
use Symfony\Component\Mime\Address;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $authenticator,EntityManagerInterface $em): Response
    {
       

        $user = new User;//instantciation de l entity user
        $form = $this->createForm(RegistrationFormType::class, $user); // on creer un formulaire du type RegistrationFormType contenant 3 champs
        $form->handleRequest($request);//gerer la requete

        if ($form->isSubmitted() && $form->isValid()) { //si le formulaire et soumis et valide
            //dd($user);
            // on recupere le password encoder avec en argumet l'utilisateur et le mot de passe en clair
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()// equivalent  $form['plainPassword']->getData()
                    
                )
            );
            
            //on persist et execute
            $em->persist($user);
            $em->flush();


            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                   // ->from(new Address($_ENV['MAIL_FROM_ADDRESS'], $_ENV['MAIL_FROM_NAME'])) avec variable d environnement
                    ->from(new Address(
                        $this->getParameter('app.mail_from_address'),//recuperation de parametre rentrer dans service.yaml
                        $this->getParameter('app.mail_from_name')
                        ))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('emails/registration/confirmation_email.html.twig')
            );
            // do anything else you need here, like send an email

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }
        //sinon retourne le template et associe la vue de notre formulaire
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),//il faut toujours retourner un objet de type form
        ]);
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }
}
