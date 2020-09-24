<?php

namespace App\Controller\Web;

use App\Form\CaiType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Helper\ProcessManager;

class CaiController extends AbstractController
{
    /**
     * @var SessionInterface $session
     */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * @Route("/cai-upload", name="cai-upload")
     */
    public function index(Request $request)
    {
        $form = $this->createForm(CaiType::class, null);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $brochureFile */
            $imageFiles = $form->get('images')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if (is_array($imageFiles)) {
              $tempMergeDirId = $this->uploadImagesAndMerge($imageFiles);
            }

            $this->session->remove("merge-pid");

            return $this->redirect($this->generateUrl('cai-merge', array('mergeId' => $tempMergeDirId)));
        }

        return $this->render('cai/upload.html.twig', [
            'controller_name' => 'CaiController',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/cai-merge/{mergeId}", name="cai-merge")
     */
    public function mergeImages(Request $request, $mergeId)
    {
        $processManager = new ProcessManager();
        $pid = $this->session->get("merge-pid");

        $done = false;

        if (empty($pid))
        {
            $caiDirectory = $this->getParameter('cai_directory');
            $tempMergeDir = $caiDirectory."/cai_".$mergeId;

            $cmd = "CaiQtCLI"; // correct pathinfo
            $cmd .= " ".realpath($tempMergeDir);
            $logFile = $caiDirectory."/".$mergeId.".log";

            $pid = $processManager->runCommand($cmd, $logFile);
            $this->session->set("merge-pid", $pid);
        }
        else if (!$processManager->isRunning($pid))
        {
            $done = true;
        }

        return $this->render('cai/merge.html.twig', [
            'controller_name' => 'CaiController',
            'pid' => $pid,
            'done' => $done,
        ]);
    }

    /**
     * @param array $imageFiles
     * @return string
     */
    private function uploadImagesAndMerge($imageFiles)
    {
        // create unique folder for image merging
        $caiDirectory = $this->getParameter('cai_directory');
        $tempMergeDirId = uniqid();
        $tempMergeDir = $caiDirectory."/cai_".$tempMergeDirId;
        mkdir($tempMergeDir, 0764);

        foreach ($imageFiles as $key => $imageFile) {
            // FIXME: TODO: validate image files

            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            // this is needed to safely include the file name as part of the URL
            $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            // Move the file to the directory where brochures are stored
            try {
                $imageFile->move(
                    $tempMergeDir,
                    $newFilename
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
                var_dump($e->getMessage()); // FIXME: write into log?!
            }
        }

        return $tempMergeDirId;
    }
}
