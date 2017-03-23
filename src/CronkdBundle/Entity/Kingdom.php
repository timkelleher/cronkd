<?php
namespace CronkdBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Jms;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Kingdom
 *
 * @ORM\Table(name="kingdom")
 * @ORM\Entity(repositoryClass="CronkdBundle\Repository\KingdomRepository")
 * @UniqueEntity("name")
 *
 * @Jms\ExclusionPolicy("all")
 */
class Kingdom
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     *
     * @Assert\NotBlank()
     * @Jms\Expose()
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="net_worth", type="integer")
     *
     * @Jms\Expose()
     */
    private $netWorth;

    /**
     * @var World
     *
     * @ORM\ManyToOne(targetEntity="World", inversedBy="kingdoms")
     */
    private $world;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="kingdoms")
     */
    private $user;

    /**
     * @var KingdomResource[]
     *
     * @ORM\OneToMany(targetEntity="KingdomResource", mappedBy="kingdom", fetch="EAGER", cascade={"persist"})
     */
    private $resources;

    /**
     * @var Queue[]
     *
     * @ORM\OneToMany(targetEntity="Queue", mappedBy="kingdom", fetch="EAGER")
     */
    private $queues;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->queues    = new ArrayCollection();
        $this->resources = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Kingdom
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set netWorth
     *
     * @param integer $netWorth
     *
     * @return Kingdom
     */
    public function setNetWorth($netWorth)
    {
        $this->netWorth = $netWorth;

        return $this;
    }

    /**
     * Get netWorth
     *
     * @return integer
     */
    public function getNetWorth()
    {
        return $this->netWorth;
    }

    /**
     * Set world
     *
     * @param World $world
     *
     * @return Kingdom
     */
    public function setWorld(World $world = null)
    {
        $this->world = $world;

        return $this;
    }

    /**
     * Get world
     *
     * @return World
     */
    public function getWorld()
    {
        return $this->world;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return Kingdom
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add resource
     *
     * @param KingdomResource $resource
     *
     * @return Kingdom
     */
    public function addResource(KingdomResource $resource)
    {
        $this->resources[] = $resource;

        return $this;
    }

    /**
     * Remove resource
     *
     * @param KingdomResource $resource
     */
    public function removeResource(KingdomResource $resource)
    {
        $this->resources->removeElement($resource);
    }

    /**
     * Get resources
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Add queue
     *
     * @param Queue $queue
     *
     * @return Kingdom
     */
    public function addQueue(Queue $queue)
    {
        $this->queues[] = $queue;

        return $this;
    }

    /**
     * Remove queue
     *
     * @param Queue $queue
     */
    public function removeQueue(Queue $queue)
    {
        $this->queues->removeElement($queue);
    }

    /**
     * Get queues
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQueues()
    {
        return $this->queues;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }
}