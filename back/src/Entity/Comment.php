<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\PersistentCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     attributes={
 *         "force_eager"=false,
 *         "normalization_context"={"groups"={"read_comment"}},
 *         "denormalization_context"={"groups"={"write_comment"}},
 *         "order"={"createdAt": "DESC"},
 *         "short_name"=Comment::SHORT_NAME
 *     }
 * )
 * @ORM\Entity
 * @ORM\Table(name="comment")
 */
class Comment
{

    const SHORT_NAME = 'c';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"read_comment","read_article"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(normalizer="trim")
     * @Groups({"read_comment", "write_comment","read_article"})
     */
    private $authorName;


    /**
     * @ORM\Column(type="integer")
     * @Groups({"read_comment", "write_comment","read_article"})
     */
    private $note;

    /**
     * @ORM\Column(type="text", name="content", nullable=false)
     * @Groups({"read_comment", "write_comment","read_article"})
     * @Assert\NotBlank(normalizer="trim")
     */
    private $content;

    /**
     * @var Comment|null Associated comment
     *
     * @ORM\ManyToOne(targetEntity="Comment", inversedBy="children")
     * @ORM\JoinColumn(name="parend_id", referencedColumnName="id")
     * @Groups({"read_comment", "write_comment","read_article"})
     */
    private $parent;

    /**
     * One MenuItem has Many MenuItems.
     * @ORM\OneToMany(targetEntity="Comment", mappedBy="parent")
     */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Article", inversedBy="comments", cascade={"all"})
     * @ORM\JoinColumn(name="article_id", nullable=false)
     * @Groups({"read_comment", "write_comment"})
     */
    private $article;

    /**
     * @var \DateTime The video creation date
     *
     * @Groups({"read_live", "read_video", "write_video"})
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @return mixed
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();

    }


    public function getId()
    {
        return $this->id;
    }

    public function getAuthorName(): ?string
    {
        return $this->authorName;
    }

    public function setAuthorName(string $authorName): self
    {
        $this->authorName = $authorName;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getParent(): ?Comment
    {
        return $this->parent;
    }

    public function setParent(?Comment $comment): self
    {
        $this->parent = $comment;

        return $this;
    }

    public function getNote()
    {
        return $this->note;
    }

    public function setNote($note): void
    {
        $this->note = $note;
    }

    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param mixed $children
     */
    public function setChildren($children): self
    {
        $this->children = $children;

        $this->children->clear();

        if ($children) {
            foreach ($children as $item) {
                $this->addChildren($item);
            }
        }

        return $this;
    }


    public function addChildren(Comment $comment): self
    {
        $this->children->add($comment);

        return $this;
    }

    public function getArticle(): ?Article
    {
        return $this->article;
    }
    public function setArticle(?Article $article): self
    {
        $this->article = $article;
        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
