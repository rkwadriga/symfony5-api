<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\CheeseNotificationRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CheeseNotificationRepository::class)
 */
class CheeseNotification
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=CheeseListing::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private CheeseListing $cheeseListing;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $notificationText;

    public function __construct(CheeseListing $cheeseListing, string $notificationText)
    {
        $this->cheeseListing = $cheeseListing;
        $this->notificationText = $notificationText;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCheeseListing(): CheeseListing
    {
        return $this->cheeseListing;
    }

    public function getNotificationText(): string
    {
        return $this->notificationText;
    }
}
