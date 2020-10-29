<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

/**
 * @ORM\Entity(repositoryClass=PostRepository::class)
 */
class Post
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @OA\Property(description="The unique identifier of the Post.")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=10, max=255, groups={"create", "update"})
     * @Assert\NotBlank(groups={"create"})
     * @OA\Property(description="The title of the Post.")
     */
    private $title;
    
    /**
     * @ORM\Column(type="text")
     * @Assert\Length(min=20, groups={"create", "update"})
     * @Assert\NotBlank(groups={"create"})
     * @OA\Property(description="The description of the Post.")
     */
    private $description;
    
    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(groups={"create"})
     * @Assert\Choice(
     *     choices = {"website", "mobile"},
     *     message = "Invalid Channel.",
     *     groups={"create", "update"}
     * )
     * @OA\Property(description="The channel of the Post.")
     */
    private $channel;
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }
    
    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }
    
    public function getChannel(): ?string
    {
        return $this->channel;
    }
    
    public function setChannel(string $channel): self
    {
        $this->channel = $channel;
        
        return $this;
    }
}
