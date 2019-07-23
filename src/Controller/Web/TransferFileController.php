<?php

namespace App\Controller\Web;

use App\Entity\TransferFile;
use App\Form\TransferFileType;
use App\Repository\TransferFileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/transfer/file")
 */
class TransferFileController extends AbstractController
{
    /**
     * @Route("/", name="transfer_file_index", methods={"GET"})
     * @param TransferFileRepository $transferFileRepository
     * @return Response
     */
    public function index(TransferFileRepository $transferFileRepository): Response
    {
        return $this->render('transfer_file/index.html.twig', [
            'transfer_files' => $transferFileRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="transfer_file_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $transferFile = new TransferFile();
        $form = $this->createForm(TransferFileType::class, $transferFile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($transferFile);
            $entityManager->flush();

            return $this->redirectToRoute('transfer_file_index');
        }

        return $this->render('transfer_file/new.html.twig', [
            'transfer_file' => $transferFile,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="transfer_file_show", methods={"GET"})
     * @param TransferFile $transferFile
     * @return Response
     */
    public function show(TransferFile $transferFile): Response
    {
        return $this->render('transfer_file/show.html.twig', [
            'transfer_file' => $transferFile,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="transfer_file_edit", methods={"GET","POST"})
     * @param Request $request
     * @param TransferFile $transferFile
     * @return Response
     */
    public function edit(Request $request, TransferFile $transferFile): Response
    {
        $form = $this->createForm(TransferFileType::class, $transferFile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('transfer_file_index');
        }

        return $this->render('transfer_file/edit.html.twig', [
            'transfer_file' => $transferFile,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="transfer_file_delete", methods={"DELETE"})
     * @param Request $request
     * @param TransferFile $transferFile
     * @return Response
     */
    public function delete(Request $request, TransferFile $transferFile): Response
    {
        if ($this->isCsrfTokenValid('delete'.$transferFile->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($transferFile);
            $entityManager->flush();
        }

        return $this->redirectToRoute('transfer_file_index');
    }
}
