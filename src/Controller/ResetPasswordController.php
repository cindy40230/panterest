<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

/**
 * @Route("/reset-password")
 */
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    private $resetPasswordHelper;
    protected $userRepository;


    public function __construct(ResetPasswordHelperInterface $resetPasswordHelper,UserRepository $userRepository )
    {
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->userRepository= $userRepository;
    }

    /**
     * Display & process form to request a password reset.
     *
     * @Route("", name="app_forgot_password_request")
     */
    public function request(Request $request, MailerInterface $mailer): Response
    {
        //creation du formulaire avec un seul champ email
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        //le formuliare gere la requete de type post
        $form->handleRequest($request);

       // si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            //on essai de proceder a l envoie de reinitialisation du mot de passe
            return $this->processSendingPasswordResetEmail(
                $form['email']->getData(),
                $mailer
            );
        }

        //on revoie la vue du formulaire 
        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    /**
     * Confirmation page after a user has requested a password reset.
     *
     * @Route("/check-email", name="app_check_email")
     */
    public function checkEmail(): Response
    {
        // We prevent users from directly accessing this page
        //si on est pas passer par la page de reinitialisation on est rediriger sur celle ci
        if (!$this->canCheckEmail()) {
            return $this->redirectToRoute('app_forgot_password_request');
        }

        //sinon on est rediriger vers  chech email qui passe un tokenLifetime 
        return $this->render('reset_password/check_email.html.twig', [
            'tokenLifetime' => $this->resetPasswordHelper->getTokenLifetime(),
        ]);
    }

    /**
     * Validates and process the reset URL that the user clicked in their email.
     *
     * @Route("/reset/{token}", name="app_reset_password")
     */
    public function reset(Request $request, UserPasswordEncoderInterface $passwordEncoder, string $token = null): Response
    {
        //si on a un tocken 
        if ($token) {
            // We store the token in session and remove it from the URL, to avoid the URL being
            // loaded in a browser and potentially leaking the token to 3rd party JavaScript.
           //on le sauvegarde en session
            $this->storeTokenInSession($token);
           //on redirige vers la page de reinitialisation
            return $this->redirectToRoute('app_reset_password');
        }

        //on recupere le token de la session
        $token = $this->getTokenFromSession();
        //si le token est egale a null alors on leve une exeption pour dire que aucun token n'a été fourni
        if (null === $token) {
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }


        //dans le cas contraire on valide le tocken et on verifie qu il est valide
        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
            //sinon on releve une exeption 
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('reset_password_error', sprintf(
                'There was a problem validating your reset request - %s',
                $e->getReason()
            ));
            //et on le redirige vers la page ou il peut redemander un mail de reinitialisation
            return $this->redirectToRoute('app_forgot_password_request');
        }
         //le tocken est valide on se retrouve sur le formulaire pour redefinir le mot de passe
        // The token is valid; allow the user to change their password.
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // A password reset token should be used only once, remove it.
            //on supprime le token de la base de données car il est utilisable une seule fois
            $this->resetPasswordHelper->removeResetRequest($token);

            // Encode the plain password, and set it.
            //on recupere et encode le mot de passe 
            $encodedPassword = $passwordEncoder->encodePassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            //on l enregistre en bdd
            $user->setPassword($encodedPassword);
            $this->getDoctrine()->getManager()->flush();

            // The session is cleaned up after the password has been changed.
            //on efface tout de la session
            $this->cleanSessionAfterReset();

            //et on redirige l utilisateur vers la page d accueil
            return $this->redirectToRoute('app_home');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }

    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer): RedirectResponse
    {
        //recuperation de l utilisateur via son email
        $user = $this->userRepository->findOneByEmail($emailFormData);
        //dd($user);
        // Marks that you are allowed to see the app_check_email page.
        
        $this->setCanCheckEmailInSession();

        // Do not reveal whether a user account was found or not.
        // si l'utilisateur n'existe pas en bdd on le redirige quand meme vers la page check email pour ne pas reveler que cette address n existe pas et l email ne sera pas envoyer
        //pour des question de sécurité
        if (!$user) {
            return $this->redirectToRoute('app_check_email');
        }

        try {
            //si il y a un utilisateur on va generer un token de reinitialisation de l utilisateur
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            // If you want to tell the user why a reset email was not sent, uncomment
            // the lines below and change the redirect to 'app_forgot_password_request'.
            // Caution: This may reveal if a user is registered or not.
            //
            // $this->addFlash('reset_password_error', sprintf(
            //     'There was a problem handling your password reset request - %s',
            //     $e->getReason()
            // ));
            //siono on le redirige vers check_email
            return $this->redirectToRoute('app_check_email');
        }

        //on procède a l envoie d email
        $email = (new TemplatedEmail())
            ->from(new Address(
                $this->getParameter('app.mail_from_address'),//recuperation de parametre rentrer dans service.yaml
                $this->getParameter('app.mail_from_name')
            ))
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
                'tokenLifetime' => $this->resetPasswordHelper->getTokenLifetime(),
            ])
        ;

        $mailer->send($email);

        return $this->redirectToRoute('app_check_email');
    }
}
