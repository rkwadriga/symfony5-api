<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ApiResource(
        collectionOperations: [
            "get",
            "post" => [
                "security" => "is_granted('IS_AUTHENTICATED_ANONYMOUSLY')"
            ]
        ],
        itemOperations: [
            "get",
            "put" => [
                "security" => "is_granted('ROLE_USER') and object === user"
            ],
            "delete" => [
                "security" => "is_granted('ROLE_ADMIN')"
            ]
        ],
        denormalizationContext: [
            "groups" => [
                "user:write"
            ]
        ],
        normalizationContext: [
            "groups" => [
                "user:read"
            ]
        ],
        security: "is_granted('ROLE_USER')" // Default access control for all methods
    ),
    UniqueEntity(fields: ["username"]),
    UniqueEntity(fields: ["email"]),
    ApiFilter(PropertyFilter::class) // Needed for get from API only certain attributes (just add to request uri something like "?properties[]=username&properties[cheeseListings][]=title")
]
/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups({"user:read", "user:write"})
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private string $email;

    /**
     * @ORM\Column(type="json")
     */
    private array $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private ?string $password;

    /**
     * @Groups({"user:write"})
     * @SerializedName("password") // Allows get this value from "password" request param
     */
    private ?string $plainPassword;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Groups({"user:read", "user:write", "cheese_listing:item:get", "cheese_listing:write"})
     * @Assert\NotBlank()
     */
    private string $username;

    /**
     * @Assert\Valid()
     * @ORM\OneToMany(targetEntity=CheeseListing::class, mappedBy="owner", cascade={"persist"}, orphanRemoval=true)
     * @Groups({"user:read", "user:write"})
     * @ApiSubresource() // Needed to make possible to get user's cheeses collection by uri "/api/users/<user ID>/cheese_listings"
     */
    private $cheeseListings;

    public function __construct()
    {
        $this->cheeseListings = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        $this->plainPassword = null;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return Collection|CheeseListing[]
     */
    public function getCheeseListings(): Collection
    {
        return $this->cheeseListings;
    }

    public function addCheeseListing(CheeseListing $cheeseListing): self
    {
        if (!$this->cheeseListings->contains($cheeseListing)) {
            $this->cheeseListings[] = $cheeseListing;
            $cheeseListing->setOwner($this);
        }

        return $this;
    }

    public function removeCheeseListing(CheeseListing $cheeseListing): self
    {
        $this->cheeseListings->removeElement($cheeseListing);
        /*if ($this->cheeseListings->removeElement($cheeseListing)) {
            // set the owning side to null (unless already changed)
            if ($cheeseListing->getOwner() === $this) {
                $cheeseListing->setOwner(null);
            }
        }*/

        return $this;
    }
}
