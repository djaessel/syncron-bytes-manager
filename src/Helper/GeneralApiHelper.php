<?php

namespace App\Helper;

use App\Entity\TransferData;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;

/**
 * Class GeneralApiHelper
 * @package App\Helper
 */
class GeneralApiHelper
{
    /**
     * @param User $user
     * @param array $transferData
     * @param EntityManagerInterface $manager
     * @return bool
     */
    public function addTransferDataIfNew(User $user, array $transferData, EntityManagerInterface $manager)
    {
        if (!is_array($transferData) || empty($transferData)) {
            return false;
        }

        $existingData = $manager->getRepository('App\Entity\TransferData')->findBy(
            array(
                'link' => $transferData["link"],
            )
        );

        if (!empty($existingData)) {
            return false;
        }

        try {
            $newTransferData = new TransferData();
            $newTransferData->setUser($user);
            $newTransferData->setDataInfo($transferData["dataInfo"]);
            $newTransferData->setLink($transferData["link"]);
            $newTransferData->setIsUsed(false);

            $manager->persist($newTransferData);
            $manager->flush();

            $success = true;
        } catch (Throwable $exception) {
            $success = false;
        }

        return $success;
    }
}
