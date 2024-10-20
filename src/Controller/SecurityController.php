<?php

namespace App\Controller;

use App\Controller\Base\AbstractAppController;
use App\Controller\Base\NotificationType;
use App\Entity\User;
use App\Form\RegisterFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Uid\Uuid;
use function Symfony\Component\Translation\t;

class SecurityController extends AbstractAppController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {

    }

    #[Route(path: '/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($this->getUser()) {
            if ($request->query->has('redirect_to')) {
                $redirection = $request->query->get('redirect_to');

                return $this->redirect($redirection);
            } else {
                return $this->redirectToRoute('app_account');
            }
        }

        $user = new User();
        $form = $this->createForm(RegisterFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $valid = true;

            if (!preg_match('/^[a-zA-Z0-9_]{3,16}$/', $user->getUsername())) {
                $this->addNotification(NotificationType::ERROR, "Le nom d'utilisateur doit uniquement contenir des lettres, des chiffres ou des underscores.");
                $valid = false;
            } else if ($this->entityManager->getRepository(User::class)
                ->createQueryBuilder('u')
                ->where('LOWER(u.username) = :username')
                ->setParameter('username', strtolower($user->getUsername()))
                ->getQuery()
                ->getOneOrNullResult()) {
                $this->addNotification(NotificationType::ERROR, "Ce nom d'utilisateur est déjà utilisé.");
                $valid = false;
            }

            if ($this->entityManager->getRepository(User::class)
                ->createQueryBuilder('u')
                ->where('LOWER(u.email) =:email')
                ->setParameter('email', strtolower($user->getEmail()))
                ->getQuery()
                ->getOneOrNullResult()) {
                $this->addNotification(NotificationType::ERROR, "Cette adresse email est déjà utilisée.");
                $valid = false;
            }

            if ($valid) {
                $password = $passwordHasher->hashPassword($user, $user->getPassword());
                $user->setPassword($password);

                do {
                    $uuid = Uuid::v4();
                    $existingUuid = $this->entityManager->getRepository(User::class)->findOneBy(['uuid' => $uuid]);
                } while ($existingUuid !== null);
                $user->setUuid($uuid);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return $this->redirectToRoute('app_login', $request->query->all());
            }
        }

        return $this->render('security/register.html.twig', [
            'form' => $form->createView(),
            'notifications' => $this->getNotifications()
        ]);
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, Request $request): Response
    {
        if ($this->getUser()) {
            if ($request->query->has('redirect_to')) {
                $redirection = $request->query->get('redirect_to');

                return $this->redirect($redirection);
            } else {
                return $this->redirectToRoute('app_account');
            }
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        if ($error) {
            $this->addNotification(NotificationType::ERROR, $error->getMessage());
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'notifications' => $this->getNotifications()
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
