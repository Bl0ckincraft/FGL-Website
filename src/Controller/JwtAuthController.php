<?php

namespace App\Controller;

use App\Entity\Mod;
use App\Entity\News;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use function Webmozart\Assert\Tests\StaticAnalysis\length;

class JwtAuthController extends AbstractController
{
    private $jwtManager;
    private $entityManager;

    public function __construct(JWTTokenManagerInterface $jwtManager, EntityManagerInterface $entityManager)
    {
        $this->jwtManager = $jwtManager;
        $this->entityManager = $entityManager;
    }

    public function genToken(User $user): string
    {
        $user->setLastJwt(time());
        $this->entityManager->flush();

        $token = $this->jwtManager->create($user);
        return $token;
    }

    #[Route(path: '/api/login', name: 'api_login', methods: ["POST"])]
    public function login(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            "email" => $data['email']
        ]);

        if ($user && password_verify($data['password'], $user->getPassword())) {
            return new JsonResponse([
                'token' => $this->genToken($user),
                "username" => $user->getUsername(),
                "uuid" => $user->getUuid(),
            ]);
        }

        return new JsonResponse(['error' => 'Invalid credentials'], 401);
    }

    #[Route(path: '/api/validate', name: 'api_validate', methods: ["GET"])]
    public function validate(Request $request)
    {
        $token = $request->headers->get('Authorization');
        $token = str_replace('Bearer ', '', $token);

        $arr = $this->jwtManager->parse($token);

        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => $arr["username"]
        ]);

        if (!$user)
            return new JsonResponse(['error' => 'Invalid token'], 401);

        if ($arr['iat'] < $user->getLastJwt() or $arr['iat'] > time() or $arr['exp'] <= time())
            return new JsonResponse(['error' => 'Expired token'], 401);

        return new JsonResponse([
            "username" => $user->getUsername(),
            "uuid" => $user->getUuid(),
        ], 200);
    }

    #[Route(path: '/api/refresh', name: 'api_refresh', methods: ["POST"])]
    public function refresh(Request $request)
    {
        $token = $request->headers->get('Authorization');
        $token = str_replace('Bearer ', '', $token);

        $arr = $this->jwtManager->parse($token);

        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            "username" => $arr["username"]
        ]);

        if (!$user)
            return new JsonResponse(['error' => 'Invalid token'], 401);

        if ($arr['iat'] < $user->getLastJwt() or $arr['iat'] > time() or $arr['exp'] <= time())
            return new JsonResponse(['error' => 'Expired token'], 401);

        return new JsonResponse([
            "username" => $user->getUsername(),
            "uuid" => $user->getUuid(),
            "token" => $this->genToken($user)
        ], 200);
    }

    #[Route(path: '/api/mods', name: 'api_mods', methods: ["GET"])]
    public function mods(Request $request): JsonResponse
    {
        $mods = [];
        /** @var string $baseUrl */
        $baseUrl = $request->server->get("SYMFONY_DEFAULT_ROUTE_URL");
        $baseUrl = substr($baseUrl, 0, strlen($baseUrl) - 1);

        foreach ($this->entityManager->getRepository(Mod::class)->findAll() as $mod) {
            $mods[] = [
                "name" => $mod->getName().'.jar',
                "sha1" => $mod->getSha1(),
                "size" => $mod->getSize(),
                "downloadURL" => $baseUrl.$this->generateUrl("api_get_mod", ["name" => $mod->getName()])
            ];
        }

        return new JsonResponse([
            "mods" => $mods
        ]);
    }

    #[Route(path: '/api/news', name: 'api_news', methods: ["GET"])]
    public function news(Request $request): JsonResponse
    {
        $news = [];
        /** @var string $baseUrl */
        $baseUrl = $request->server->get("SYMFONY_DEFAULT_ROUTE_URL");
        $baseUrl = substr($baseUrl, 0, strlen($baseUrl) - 1);

        /** @var News $newsIn */
        foreach ($this->entityManager->getRepository(News::class)->findAll() as $newsIn) {
            $news[] = [
                "title" => [
                    "text" => $newsIn->getTitle()->getText(),
                    "size" => $newsIn->getTitle()->getSize(),
                    "color" => $newsIn->getTitle()->getColor(),
                ],
                "description" => [
                    "text" => $newsIn->getDescription()->getText(),
                    "size" => $newsIn->getDescription()->getSize(),
                    "color" => $newsIn->getDescription()->getColor(),
                ],
                "layout" => [
                    "image" => $baseUrl.$this->generateUrl("api_get_news_image", ["id" => $newsIn->getId()]),
                    "min_height" => $newsIn->getLayout()->getMinHeight(),
                    "max_width" => $newsIn->getLayout()->getMaxWidth(),
                    "text_alignment" => $newsIn->getLayout()->getTextAlignment(),
                    "text_percentage" => $newsIn->getLayout()->getTextPercentage()
                ],
                "epoch_millis" => $newsIn->getEpochMillis() * 1000
            ];
        }

        return new JsonResponse($news);
    }

    #[Route(path: '/api/get/launcher', name: 'api_get_launcher', methods: ["GET"])]
    public function get_launcher(): BinaryFileResponse
    {
        $path = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'website_data'. DIRECTORY_SEPARATOR .'launcher'. DIRECTORY_SEPARATOR . 'FGL Launcher.jar';

        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'FGL Launcher.jar'
        );

        return $response;
    }

    #[Route(path: '/api/get/mod/{name}', name: 'api_get_mod', methods: ["GET"])]
    public function get_mod(string $name): BinaryFileResponse
    {
        $path = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'website_data' . DIRECTORY_SEPARATOR . 'mods' . DIRECTORY_SEPARATOR . $name . '.jar';

        $mod = $this->entityManager->getRepository(Mod::class)->findOneBy([
            "name" => $name
        ]);

        if (!$mod)
            $this->createNotFoundException();

        $response = new BinaryFileResponse($path);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $name.'.jar'
        );

        return $response;
    }

    #[Route(path: '/api/get/news/{id}', name: 'api_get_news_image', methods: ["GET"])]
    public function get_news_image(int $id): BinaryFileResponse
    {
        $news = $this->entityManager->getRepository(News::class)->findOneBy([
            "id" => $id
        ]);

        if (!$news)
            $this->createNotFoundException();

        $path = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'website_data' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'news' . DIRECTORY_SEPARATOR . $news->getLayout()->getImage() . '.png';

        $response = new BinaryFileResponse($path);
        $response->headers->add([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);

        return $response;
    }
}
