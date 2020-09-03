<?php

namespace App\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class CaiController extends AbstractController
{
    /**
     * @Route("/cai-upload", name="cai-upload")
     */
    public function index()
    {
        $form = $this->createForm(CaiType::class, null);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $brochureFile */
            $imageFiles = $form->get('images')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if (is_array($imageFile)) {
              $thia->uploadImagesAndMerge($imageFiles);
            }

            return $this->redirect($this->generateUrl('cai-merge'));
        }

        return $this->render('cai/upload.html.twig', [
            'controller_name' => 'CaiController',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/cai-merge", name="cai-merge")
     */
    public function mergeImages()
    {
        // TODO: Merge images and show result + download

        return $this->render('cai/merge.html.twig', [
            'controller_name' => 'CaiController',
        ]);
    }

    private function uploadImagesAndMerge($imageFiles)
    {
        // create unique folder for image merging
        $caiDirectory = $this->getParameter('cai_directory');
        $tempMergeDir = $caiDirectory."/cai_".uniqid();
        mkdir($tempConvDir, 0764);

        foreach ($imageFiles as $key => $image) {
            $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();

            // Move the file to the directory where brochures are stored
            try {
                $brochureFile->move(
                    $tempMergeDir,
                    $newFilename
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
                var_dump($e->getMessage());
            }
        }
    }
}
