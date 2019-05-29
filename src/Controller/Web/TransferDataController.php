<?php

namespace App\Controller\Web;

use App\Entity\TransferData;
use App\Entity\User;
use App\Form\TransferDataType;
use App\Repository\TransferDataRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/transfer/data")
 */
class TransferDataController extends AbstractController
{
    /**
     * @Route("/", name="transfer_data_index", methods={"GET"})
     * @param TransferDataRepository $transferDataRepository
     * @return Response
     */
    public function index(TransferDataRepository $transferDataRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        $transferData = $transferDataRepository->findBy(
            array(
                'user' => $user,
            )
        );

        return $this->render('transfer_data/index.html.twig', [
            'transfer_data' => $transferData,
        ]);
    }

    /**
     * @Route("/new", name="transfer_data_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $transferDatum = new TransferData();
        $form = $this->createForm(TransferDataType::class, $transferDatum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $transferDatum->setUser($this->getUser());
            $entityManager->persist($transferDatum);
            $entityManager->flush();

            return $this->redirectToRoute('transfer_data_index');
        }

        return $this->render('transfer_data/new.html.twig', [
            'transfer_datum' => $transferDatum,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="transfer_data_show", methods={"GET"})
     * @param TransferData $transferDatum
     * @return Response
     */
    public function show(TransferData $transferDatum): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (empty($transferDatum->getUser())) {
            return $this->redirectToRoute("home");
        }

        /** @var User $userId */
        $userId = $this->getUser()->getId();
        if ($transferDatum->getUser()->getId() !== $userId) {
            return $this->redirectToRoute("home");
        }

        return $this->render('transfer_data/show.html.twig', [
            'transfer_datum' => $transferDatum,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="transfer_data_edit", methods={"GET","POST"})
     * @param Request $request
     * @param TransferData $transferDatum
     * @return Response
     */
    public function edit(Request $request, TransferData $transferDatum): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (empty($transferDatum->getUser())) {
            return $this->redirectToRoute("home");
        }

        /** @var User $userId */
        $userId = $this->getUser()->getId();
        if ($transferDatum->getUser()->getId() !== $userId) {
            return $this->redirectToRoute("home");
        }

        $form = $this->createForm(TransferDataType::class, $transferDatum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('transfer_data_index', [
                'id' => $transferDatum->getId(),
            ]);
        }

        return $this->render('transfer_data/edit.html.twig', [
            'transfer_datum' => $transferDatum,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="transfer_data_delete", methods={"DELETE"})
     * @param Request $request
     * @param TransferData $transferDatum
     * @return Response
     */
    public function delete(Request $request, TransferData $transferDatum): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        if (empty($transferDatum->getUser())) {
            return $this->redirectToRoute("home");
        }

        /** @var User $userId */
        $userId = $this->getUser()->getId();
        if ($transferDatum->getUser()->getId() !== $userId) {
            return $this->redirectToRoute("home");
        }

        if ($this->isCsrfTokenValid('delete'.$transferDatum->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($transferDatum);
            $entityManager->flush();
        }

        return $this->redirectToRoute('transfer_data_index');
    }
}
