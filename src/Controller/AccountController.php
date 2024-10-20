<?php

namespace App\Controller;

use App\Controller\Base\AbstractAppController;
use App\Controller\Base\NotificationType;
use App\Entity\User;
use App\Form\RegisterFormType;
use App\Form\SkinFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

class AccountController extends AbstractAppController
{
    #[Route('/account', name: 'app_account')]
    public function index(Request $request): Response
    {
        $form = $this->createForm(SkinFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->getData()["skin"];

            if ($file) {
                if ($file->getClientOriginalExtension() === 'png') {
                    $imageSize = getimagesize($file->getPathname());
                    if ($imageSize === false || $imageSize[0] !== 64 || $imageSize[1] !== 64) {
                        $this->addNotification(NotificationType::ERROR, "Le skin doit faire 64 par 64 pixels");
                    } else {
                        $uploadDir = $this->getParameter('kernel.project_dir') . '/website_data/images/skins';

                        try {
                            /** @var User $user */
                            $user = $this->getUser();
                            $newFilename = $user->getUuid() . '.png';
                            $file->move($uploadDir, $newFilename);

                            $this->addNotification(NotificationType::SUCCESS, "Le skin a bien été mis à jour");
                        } catch (FileException $e) {
                            $this->addNotification(NotificationType::ERROR, "Le skin n'a pas pu être téléchargé");
                        }
                    }
                } else {
                    $this->addNotification(NotificationType::ERROR, "Le skin doit être en .png");
                }
            }
        }

        return $this->render('account/account.html.twig', [
            "notifications" => $this->getNotifications(),
            "form" => $form
        ]);
    }

    #[Route('/skin/{uuid}/full', name: 'app_skin')]
    public function skin(string $uuid): Response
    {
        $skinPath = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'website_data'. DIRECTORY_SEPARATOR .'images'. DIRECTORY_SEPARATOR .'skins'. DIRECTORY_SEPARATOR .''.$uuid.'.png';
        $defaultPath = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'website_data'. DIRECTORY_SEPARATOR .'images'. DIRECTORY_SEPARATOR .'skins'. DIRECTORY_SEPARATOR .'default.png';

        return $this->withNoCache(new BinaryFileResponse((Uuid::isValid($uuid) and file_exists($skinPath)) ? $skinPath : $defaultPath));
    }

    #[Route('/skin/{uuid}/head', name: 'app_skin_head')]
    public function skin_head(string $uuid): Response
    {
        $skinPath = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'website_data'. DIRECTORY_SEPARATOR .'images'. DIRECTORY_SEPARATOR .'skins'. DIRECTORY_SEPARATOR .''.$uuid.'.png';
        $defaultPath = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'website_data'. DIRECTORY_SEPARATOR .'images'. DIRECTORY_SEPARATOR .'skins'. DIRECTORY_SEPARATOR .'default.png';

        $sourceImagePath = (Uuid::isValid($uuid) and file_exists($skinPath)) ? $skinPath : $defaultPath;

        if (!file_exists($sourceImagePath)) {
            return new Response('L\'image source est introuvable.', Response::HTTP_NOT_FOUND);
        }

        $sourceImage = imagecreatefrompng($sourceImagePath);

        if (!$sourceImage) {
            return new Response('Erreur lors du chargement de l\'image.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $smallImage = imagecreatetruecolor(8, 8);

        imagecopy($smallImage, $sourceImage, 0, 0, 8, 8, 8, 8);

        ob_start();
        header('Content-Type: image/png');
        imagepng($smallImage);

        $imageData = ob_get_clean();

        imagedestroy($smallImage);
        imagedestroy($sourceImage);

        return $this->withNoCache(new Response($imageData, 200, ['Content-Type' => 'image/png']));
    }

    #[Route('/skin/{uuid}/head/{factor}', name: 'app_skin_head_factor')]
    public function skin_head_factor(string $uuid, int $factor): Response
    {
        $skinPath = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'website_data'. DIRECTORY_SEPARATOR .'images'. DIRECTORY_SEPARATOR .'skins'. DIRECTORY_SEPARATOR .''.$uuid.'.png';
        $defaultPath = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'website_data'. DIRECTORY_SEPARATOR .'images'. DIRECTORY_SEPARATOR .'skins'. DIRECTORY_SEPARATOR .'default.png';

        $sourceImagePath = (Uuid::isValid($uuid) and file_exists($skinPath)) ? $skinPath : $defaultPath;

        if (!file_exists($sourceImagePath)) {
            return new Response('L\'image source est introuvable.', Response::HTTP_NOT_FOUND);
        }

        $sourceImage = imagecreatefrompng($sourceImagePath);

        if (!$sourceImage) {
            return new Response('Erreur lors du chargement de l\'image.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $smallImage = imagecreatetruecolor(8, 8);

        imagecopy($smallImage, $sourceImage, 0, 0, 8, 8, 8, 8);

        ob_start();
        header('Content-Type: image/png');
        imagepng($smallImage);

        $imageData = ob_get_clean();

        imagedestroy($smallImage);
        imagedestroy($sourceImage);

        $srcImage = imagecreatefromstring($imageData);

        $srcWidth = imagesx($srcImage);
        $srcHeight = imagesy($srcImage);

        $targetWidth = 8*$factor;
        $targetHeight = 8*$factor;
        $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);

        // Désactiver l'antialiasing (lissage)
        imageantialias($targetImage, false);

        // Augmenter la taille tout en conservant l'aspect pixélisé
        imagecopyresized(
            $targetImage,        // Image destination
            $srcImage,           // Image source
            0, 0,                // Coordonnées de destination (x, y)
            0, 0,                // Coordonnées de source (x, y)
            $targetWidth,        // Largeur de la destination (48)
            $targetHeight,       // Hauteur de la destination (48)
            $srcWidth,           // Largeur de la source (8)
            $srcHeight           // Hauteur de la source (8)
        );

        // Définir les en-têtes de réponse pour envoyer une image PNG
        header('Content-Type: image/png');

        // Afficher l'image redimensionnée
        ob_start();
        imagepng($targetImage);
        $imageData = ob_get_clean();

        // Libérer la mémoire
        imagedestroy($srcImage);
        imagedestroy($targetImage);

        // Retourner l'image comme réponse Symfony
        return $this->withNoCache(new Response($imageData, 200, ['Content-Type' => 'image/png']));
    }

    public function withNoCache(Response $response): Response
    {
        $response->headers->add([
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0'
        ]);

        return $response;
    }
}
