<?php

namespace App\Controller\Rest;

use App\Entity\TransferFile;
use App\Entity\User;
use App\Helper\GeneralApiHelper;
use App\Helper\JwtApiManager;
use App\Helper\UserHelper;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Throwable;

/**
 * Class WeTransferController
 * @package App\Controller\Rest
 */
class WeTransferController extends BaseController
{
    /**
     * @Rest\Post("/user/register")
     *
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     */
    public function registerUser(UserPasswordEncoderInterface $encoder)
    {
        $jsonData = $this->getJsonData();

        if (!is_array($jsonData)) {
            throw new BadRequestHttpException();
        }

        if (empty($jsonData["email"]) || empty($jsonData["pass"])) {
            throw new BadRequestHttpException(implode(",", array_keys($jsonData)));
        }

        $userHelper = new UserHelper($this->container);
        if (!$userHelper->registerNewUser($encoder, $jsonData)) {
            throw new BadRequestHttpException(false);
        }

        $view = $this->view(true, 200);

        return $this->handleView($view);
    }

    /**
     * @Rest\Post("user/activate")
     *
     * @param JWTTokenManagerInterface $jwtManager
     * @param JWTEncoderInterface $jwtEncoder
     * @return Response
     */
    public function activateUser(JWTTokenManagerInterface $jwtManager, JWTEncoderInterface $jwtEncoder)
    {
        $jsonData = $this->getJsonData();

        if (!is_array($jsonData)) {
            throw new BadRequestHttpException();
        }

        if (empty($jsonData["email"]) || empty($jsonData["activation_code"])) {
            throw new BadRequestHttpException(implode(",", array_keys($jsonData)));
        }

        $userHelper = new UserHelper($this->container);
        $user = $userHelper->findUserByEmail($jsonData["email"]);

        if (empty($user)) {
            $view = $this->view("EMAIL_ERROR", 400);
            return $this->handleView($view);
        }

        $jwtApiManager = new JwtApiManager($this->container, $jwtEncoder);

        $view = $this->view(null, 401);

        $activationCode = $jsonData["activation_code"];
        $userActivation = $jwtApiManager->getUserActivation($user, $activationCode);
        if (!empty($userActivation)) {
            $userActivated = $jwtApiManager->activateUser($user, $userActivation);
            if ($userActivated) {
                $token = $jwtManager->create($user);
                $view = $this->view(array('token' => $token), 200);
            }
        }

        return $this->handleView($view);
    }

    /**
     * @Rest\Post("/user/login")
     *
     * @param UserPasswordEncoderInterface $encoder
     * @param JWTTokenManagerInterface $jwtManager
     * @return Response
     */
    public function loginUser(UserPasswordEncoderInterface $encoder, JWTTokenManagerInterface $jwtManager)
    {
        $jsonData = $this->getJsonData();

        if (!is_array($jsonData)) {
            throw new BadRequestHttpException();
        }

        if (empty($jsonData["email"]) || empty($jsonData["pass"])) {
            throw new BadRequestHttpException(implode(",", array_keys($jsonData)));
        }

        $userHelper = new UserHelper($this->container);

        $email = $jsonData["email"];
        $user = $userHelper->findUserByEmail($email);

        $view = $this->view(null, 401);

        $password = $jsonData["pass"];
        if ($encoder->isPasswordValid($user, $password)) {
            $token = $jwtManager->create($user);
            $view = $this->view(array('token' => $token), 200);
        }

        return $this->handleView($view);
    }

    /**
     * @Rest\Post("/user/add/link")
     *
     * @param JWTEncoderInterface $jwtEncoder
     * @return Response
     */
    public function addTransferLink(JWTEncoderInterface $jwtEncoder)
    {
        $jsonData = $this->getJsonData();

        $user = null;
        if (!empty($jsonData["json_web_token"])) {
            $jwtApiManager = new JwtApiManager($this->container, $jwtEncoder);
            $user = $jwtApiManager->retrieveAuthenticatedUser($jsonData["json_web_token"]);
        }

        if (empty($user)) {
            throw new AuthenticationException();
        }

        $transferData = null;
        if (!empty($jsonData["transferData"])) {
            $transferData = $jsonData["transferData"];
        }

        /** @var EntityManager $manager */
        $manager = $this->getDoctrine()->getManager();

        $generalHelper = new GeneralApiHelper();
        $success = $generalHelper->addTransferDataIfNew($user, $transferData, $manager);

        $view = $this->view($success, 200);

        return $this->handleView($view);
    }

    /**
     * @Rest\Post("/user/account/info")
     *
     * @param JWTEncoderInterface $jwtEncoder
     * @return Response
     */
    public function retrieveAccountInfo(JWTEncoderInterface $jwtEncoder)
    {
        $jsonData = $this->getJsonData();

        $user = $this->checkForValidJwtToken($jwtEncoder, $jsonData);
        if (empty($user)) {
            $view = $this->view("Invalid Token", 401);
            return $this->handleView($view);
        }

        $userHelper = new UserHelper($this->container);
        $accountInfo = $userHelper->buildAccountInfo($user);

        $jsonData = array('info' => $accountInfo);

        $view = $this->view($jsonData, 200);

        return $this->handleView($view);
    }

    /**
     * @Rest\Post("/user/account/settings")
     *
     * @param JWTEncoderInterface $jwtEncoder
     * @return Response
     */
    public function retrieveAccountSettings(JWTEncoderInterface $jwtEncoder)
    {
        $jsonData = $this->getJsonData();

        $user = $this->checkForValidJwtToken($jwtEncoder, $jsonData);
        if (empty($user)) {
            $view = $this->view("Invalid Token", 401);
            return $this->handleView($view);
        }

        $userHelper = new UserHelper($this->container);
        $accountSettings = $userHelper->buildAccountSettings($user);

        $jsonData = array('settings' => $accountSettings);

        $view = $this->view($jsonData, 200);

        return $this->handleView($view);
    }

    /**
     * @Rest\Post("/file/upload")
     *
     * @param JWTEncoderInterface $jwtEncoder
     * @return Response
     */
    public function fileUpload(JWTEncoderInterface $jwtEncoder)
    {
        $request = $this->get('request_stack')->getCurrentRequest();

        $jsonData = json_decode($request->request->get("jsonData"), true);

        $user = $this->checkForValidJwtToken($jwtEncoder, $jsonData);
        if (empty($user)) {
            $view = $this->view("Invalid Token", 401);
            return $this->handleView($view);
        }

        try {
            $this->uploadAndSaveTransferFile($request, $user);
            $view = $this->view(true, 200);
        } catch (Throwable $exception) {
            $view = $this->view(false, 500);
        }

        return $this->handleView($view);
    }

    /**
     * @Rest\Post("/user/use/link")
     *
     * @param JWTEncoderInterface $jwtEncoder
     * @return Response
     */
    public function useLink(JWTEncoderInterface $jwtEncoder)
    {
        $jsonData = $this->getJsonData();

        $user = $this->checkForValidJwtToken($jwtEncoder, $jsonData);
        if (empty($user)) {
            throw new AuthenticationException();
        }

        try {
            $manager = $this->getDoctrine()->getManager();

            $transferData = $manager->getRepository('App\Entity\TransferData')
                ->findOneBy(
                    array(
                        'link' => $jsonData["link"]
                    )
                );

            if (!empty($transferData)) {
                $transferData->setIsUsed(true);

                $manager->persist($transferData);
                $manager->flush();
            }

            $success = true;
        } catch (Throwable $exception) {
            $success = false;
        }

        $view = $this->view($success, 200);

        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @param User $user
     */
    private function uploadAndSaveTransferFile(Request $request, User $user)
    {
        /** @var UploadedFile[] $fileParts */
        $fileParts = $request->files->all();
        $uploadFolder = $this->getParameter("upload_folder");

        $transferFile = new TransferFile();

        if (count($fileParts) > 0) {
            $firstPart = $fileParts["file-part-0"];
            $manager = $this->getDoctrine()->getManager();

            $transferFile->setFilename($firstPart->getClientOriginalName());
            $transferFile->setFileSize($firstPart->getSize());
            $transferFile->setFileType($firstPart->getMimeType());
            $transferFile->setUploadDate(date_create("now"));
            $transferFile->setOwner($user);

            $manager->persist($transferFile);
            $manager->flush();
        }

        $index = 0;
        foreach ($fileParts as $key => $filePart) {
            $filePart->move($uploadFolder, $transferFile->getId() . "_" . $filePart->getClientOriginalName() . "_" . $index . ".jsys");
            $index++;
        }
    }
}
