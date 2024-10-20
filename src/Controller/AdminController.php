<?php

namespace App\Controller;

use App\Controller\Base\AbstractAppController;
use App\Controller\Base\NotificationType;
use App\Entity\Mod;
use App\Entity\News;
use App\Entity\NewsLayout;
use App\Entity\NewsText;
use App\Entity\User;
use App\Form\EditModFormType;
use App\Form\ModFormType;
use App\Form\NewsFormType;
use App\Form\SkinFormType;
use Doctrine\ORM\EntityManagerInterface;
use ErrorException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use function Symfony\Component\String\u;
use function Webmozart\Assert\Tests\StaticAnalysis\null;

class AdminController extends AbstractAppController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {

    }

    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/admin.html.twig', [

        ]);
    }

    #[Route('/admin/mods', name: 'app_admin_mods')]
    public function mods(Request $request): Response
    {
        $this->decodeNotifications($request);

        $mod = new Mod();
        $form = $this->createForm(ModFormType::class, $mod);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('modFile')->getData();

            if ($file) {
                if ($file->getClientOriginalExtension() === 'jar') {
                    $uploadDir = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'website_data' . DIRECTORY_SEPARATOR . 'mods' . DIRECTORY_SEPARATOR;

                        try {
                            $newFilename = $mod->getName().".jar";
                            $file->move($uploadDir, $newFilename);

                            $mod->setSize(filesize($uploadDir.$newFilename));
                            $mod->setSha1(sha1_file($uploadDir.$newFilename));

                            $this->entityManager->persist($mod);
                            $this->entityManager->flush();

                            $this->addNotification(NotificationType::SUCCESS, "Le mod a bien été ajouté");
                        } catch (FileException $e) {
                            $this->addNotification(NotificationType::ERROR, "Le mod n'a pas pu être ajouté");
                        }
                } else {
                    $this->addNotification(NotificationType::ERROR, "Le mod doit être en .jar");
                }
            }
        }

        return $this->render('admin/mods.html.twig', [
            "notifications" => $this->getNotifications(),
            "form" => $form,
            "mods" => $this->entityManager->getRepository(Mod::class)->findAll()
        ]);
    }

    #[Route('/admin/mods/delete/{name}', name: 'app_admin_mods_delete')]
    public function delete_mod(string $name): Response
    {
        $mod = $this->entityManager->getRepository(Mod::class)->findOneBy(["name" => $name]);
        if ($mod == null) {
            $this->addNotification(NotificationType::ERROR, "Aucun mod n'est enregistré avec ce nom.");
        } else {
            $path = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'website_data' . DIRECTORY_SEPARATOR . 'mods' . DIRECTORY_SEPARATOR . $mod->getName() . ".jar";
            try {
                unlink($path);
            } catch (ErrorException $e) {
                $this->addNotification(NotificationType::ERROR, "Le fichier n'a pas pu être supprimé: \n".$e->getMessage());
            }

            $this->entityManager->remove($mod);
            $this->entityManager->flush();
            $this->addNotification(NotificationType::SUCCESS, "Le mod ".$mod->getName()." a bien été supprimé.");
        }

        return $this->redirectToRoute("app_admin_mods", [
            "notifications" => $this->encodeNotifications()
        ]);
    }

    #[Route('/admin/mods/edit/{name}', name: 'app_admin_mods_edit')]
    public function edit_mod(string $name, Request $request): Response
    {
        $mod = $this->entityManager->getRepository(Mod::class)->findOneBy(["name" => $name]);
        if ($mod == null)
            $this->createNotFoundException();

        $form = $this->createForm(EditModFormType::class, $mod);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('modFile')->getData();

            if ($mod->getName() != $name and $this->entityManager->getRepository(Mod::class)->findOneBy(["name" => $mod->getName()]) != null) {
                $this->addNotification(NotificationType::ERROR, "Un mod est déjà enregistré avec ce nom.");
            } else {
                $uploadDir = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'website_data' . DIRECTORY_SEPARATOR . 'mods' . DIRECTORY_SEPARATOR;
                $newFilename = $mod->getName() . ".jar";
                $prevFilename = $name . '.jar';

                if ($file) {
                    if ($file->getClientOriginalExtension() === 'jar') {
                        try {
                            unlink($uploadDir . $prevFilename);
                            $file->move($uploadDir, $newFilename);

                            $mod->setSize(filesize($uploadDir . $newFilename));
                            $mod->setSha1(sha1_file($uploadDir . $newFilename));

                            $this->entityManager->flush();

                            $this->addNotification(NotificationType::SUCCESS, "Le mod a bien été modifié");
                            return $this->redirectToRoute("app_admin_mods", [
                                "notifications" => $this->encodeNotifications()
                            ]);
                        } catch (FileException|ErrorException $e) {
                            $this->addNotification(NotificationType::ERROR, "Le mod n'a pas pu être modifié");
                        }
                    } else {
                        $this->addNotification(NotificationType::ERROR, "Le mod doit être en .jar");
                    }
                } else {
                    rename($uploadDir . $prevFilename, $uploadDir . $newFilename);
                    $mod->setSize(filesize($uploadDir . $newFilename));
                    $mod->setSha1(sha1_file($uploadDir . $newFilename));

                    $this->entityManager->flush();
                    $this->addNotification(NotificationType::SUCCESS, "Le nom du mod a bien été modifié");
                    return $this->redirectToRoute("app_admin_mods", [
                        "notifications" => $this->encodeNotifications()
                    ]);
                }
            }
        }

        return $this->render("admin/edit_mod.html.twig", [
            "mod" => $mod,
            "form" => $form,
            "notifications" => $this->getNotifications()
        ]);
    }

    #[Route('/admin/news', name: 'app_admin_news')]
    public function news(Request $request): Response
    {
        $this->decodeNotifications($request);

        $news = new News();
        $news->setLayout(new NewsLayout());
        $news->setTitle(new NewsText());
        $news->setDescription(new NewsText());
        $form = $this->createForm(NewsFormType::class, $news);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get("layout")->get("image_temp")->getData();
            $baseName = time();
            $uploadDir = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'website_data' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'news' . DIRECTORY_SEPARATOR;

            if (file_exists($uploadDir.$baseName.".png")) {
                $number = 1;
                while (file_exists($uploadDir.$baseName."-".$number.".png")) {
                    $number++;
                }

                $baseName = $baseName."-".$number;
            }

            $fileName = $baseName;
            $file->move($uploadDir, $fileName.'.png');

            $news->getLayout()->setImage($fileName);
            $this->entityManager->persist($news);
            $this->entityManager->flush();

            $news = new News();
            $news->setLayout(new NewsLayout());
            $news->setTitle(new NewsText());
            $news->setDescription(new NewsText());
            $form = $this->createForm(NewsFormType::class, $news);
            $this->addNotification(NotificationType::SUCCESS, "Cette news a bien été ajouté.");
        }

        return $this->render('admin/news.html.twig', [
            "notifications" => $this->getNotifications(),
            "news" => $this->entityManager->getRepository(News::class)->findAll(),
            "form" => $form
        ]);
    }

    #[Route('/admin/news/delete/{id}', name: 'app_admin_news_delete')]
    public function delete_news(int $id, Request $request): Response
    {
        $news = $this->entityManager->getRepository(News::class)->findOneBy([
            "id" => $id
        ]);

        if (!$news)
            $this->createNotFoundException();

        $uploadDir = $this->getParameter('kernel.project_dir') . DIRECTORY_SEPARATOR . 'website_data' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'news' . DIRECTORY_SEPARATOR;
        try {
            unlink($uploadDir.$news->getLayout()->getImage().'.png');
        } catch (Exception $e) {
            $this->addNotification(NotificationType::ERROR, "Impossible de supprimer l'image de cette news.");
        }

        $this->entityManager->remove($news);
        $this->entityManager->flush();
        $this->addNotification(NotificationType::SUCCESS, "Cette news a bien été supprimée.");

        return $this->redirectToRoute("app_admin_news", [
            "notifications" => $this->encodeNotifications()
        ]);
    }
}
