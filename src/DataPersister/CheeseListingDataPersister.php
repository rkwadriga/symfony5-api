<?php declare(strict_types=1);
/**
 * Created 2021-10-21
 * Author Dmitry Kushneriov
 */

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\CheeseListing;
use App\Entity\CheeseNotification;
use Doctrine\ORM\EntityManagerInterface;

class CheeseListingDataPersister implements DataPersisterInterface
{
    public function __construct(
        private DataPersisterInterface $decoratedDataPersister,
        private EntityManagerInterface $em
    ) {}

    public function supports($data): bool
    {
        return $data instanceof CheeseListing;
    }

    public function persist($data): void
    {
        /** @var CheeseListing $data */

        $originalData = $this->em->getUnitOfWork()->getOriginalEntityData($data);
        if (!($originalData['isPublished'] ?? false) && $data->getIsPublished()) {
            $notification = new CheeseNotification($data, sprintf('Cheese listing "%s" was created', $data->getTitle()));
            $this->em->persist($notification);
            $this->em->flush();
        }

        $this->decoratedDataPersister->persist($data);
    }

    public function remove($data): void
    {
        $this->decoratedDataPersister->remove($data);
    }
}