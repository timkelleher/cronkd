<?php

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\Resource;
use CronkdBundle\Entity\World;
use CronkdBundle\Service\QueuePopulator;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

class QueuePopulatorTest extends KernelTestCase
{
    /** @var  ContainerInterface */
    private $container;
    /** @var  EntityManagerInterface */
    private $em;
    /** @var  QueuePopulator */
    private $queuePopulator;

    public function setUp()
    {
        self::bootKernel();
        $this->container = self::$kernel->getContainer();
        $this->em = $this->container->get('doctrine.orm.default_entity_manager');
        $this->queuePopulator = $this->container->get('cronkd.queue_populator');

        $purger = new ORMPurger($this->em);
        $executor = new ORMExecutor($this->em, $purger);
        $executor->execute([]);
    }

    public function testDependencyInjection()
    {
        $this->assertEquals(QueueBuilder::class, get_class($this->queuePopulator));
    }

    public function invalidQueueSizeDataProvider()
    {
        return [
            [0],
            [-1],
            [2.5],
            ['String'],
            [null],
        ];
    }

    /**
     * @dataProvider invalidQueueSizeDataProvider
     * @expectedException CronkdBundle\Exceptions\InvalidQueueIntervalException
     */
    public function testInvalidQueueSizes($expectedQueueSize)
    {
        $queueBuilder = $this->container->get('cronkd.queue_builder');
        $world = $this->createOrGetWorld('TestWorld');
        $kingdom = $this->createOrGetKingdom($world, 'TestKingdom');
        $resource = $this->createOrGetResource(Resource::MATERIAL);

        $this->queuePopulator->build($kingdom, $resource, $expectedQueueSize, 10);
    }

    public function validQueueSizeDataProvider()
    {
        return [
            [1],
            [2],
            [5],
            [10],
            [25],
            [100],
            [1000],
            [10000],
        ];
    }

    /**
     * @dataProvider validQueueSizeDataProvider
     */
    public function testValidQueueSizes($expectedQueueSize)
    {
        $queueBuilder = $this->container->get('cronkd.queue_builder');
        $world = $this->createOrGetWorld('TestWorld');
        $kingdom = $this->createOrGetKingdom($world, 'TestKingdom');
        $resource = $this->createOrGetResource(Resource::MATERIAL);

        $queues = $this->queuePopulator->build($kingdom, $resource, $expectedQueueSize, 10);

        $this->assertEquals($expectedQueueSize, count($queues));
    }

    public function bucketPlacementDataProvider()
    {
        return [
            [1, 1, [1]],
            [1, 10, [10]],
            [2, 1, [0,1]],
            [2, 7, [3,4]],
            [5, 29, [5,6,6,6,6]],
            [5, 25004, [5000,5001,5001,5001,5001]],
            [10, 75, [7,7,7,7,7,8,8,8,8,8]],
            [25, 1, [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,1]],
            //[1000, 100000, array_fill(0, 1000, 100)],
        ];
    }

    /**
     * @dataProvider bucketPlacementDataProvider
     */
    public function testBucketPlacement($queueSize, $quantity, array $expectedPlacement)
    {
        $queueBuilder = $this->container->get('cronkd.queue_builder');
        $world = $this->createOrGetWorld('TestWorld');
        $kingdom = $this->createOrGetKingdom($world, 'TestKingdom');
        $resource = $this->createOrGetResource(Resource::MATERIAL);

        $queues = $this->queuePopulator->build($kingdom, $resource, $queueSize, $quantity);

        $this->assertEquals(count($expectedPlacement), count($queues));
        for ($i = 0; $i < count($queues); $i++) {
            $this->assertEquals($expectedPlacement[$i], $queues[$i]->getQuantity());
        }
    }

    public function queueStructureDataProvider()
    {
        return [
            [1, 10, 10],
            [100, 200, 30],
            //[1000, 500, 3000],
        ];
    }

    /**
     * @dataProvider bucketPlacementDataProvider
     */
    public function testQueueStructure($startingTick, $queueSize, $quantity)
    {
        $queueBuilder = $this->container->get('cronkd.queue_builder');
        $world = $this->createOrGetWorld('TestWorld', $startingTick);
        $kingdom = $this->createOrGetKingdom($world, 'TestKingdom');
        $resource = $this->createOrGetResource(Resource::MATERIAL);

        $queues = $this->queuePopulator->build($kingdom, $resource, $queueSize, $quantity);

        for ($i = 0; $i < count($queues); $i++) {
            $this->assertEquals($world, $queues[$i]->getKingdom()->getWorld());
            $this->assertEquals($kingdom, $queues[$i]->getKingdom());
            $this->assertEquals($resource, $queues[$i]->getResource());
            $this->assertEquals($startingTick+$i, $queues[$i]->getTick());
        }
    }

    private function createOrGetWorld($name, $tick = 1)
    {
        $world = $this->em->getRepository(World::class)->findOneBy(['name' => $name]);
        if (!$world) {
            $world = new World();
            $world->setName($name);
            $world->setTick($tick);
            $this->em->persist($world);
            $this->em->flush();
        }

        return $world;
    }

    private function createOrGetKingdom(World $world, $name)
    {
        $kingdom = $this->em->getRepository(Kingdom::class)->findOneBy([
            'world' => $world,
            'name' => $name,
        ]);
        if (!$kingdom) {
            $kingdom = new Kingdom();
            $kingdom->setName($name);
            $kingdom->setWorld($world);
            $this->em->persist($kingdom);
            $this->em->flush();
        }

        return $kingdom;
    }

    private function createOrGetResource($name)
    {
        $resource = $this->em->getRepository(Resource::class)->findOneBy(['name' => $name]);
        if (!$resource) {
            $resource = new Resource();
            $resource->setName($name);
            $this->em->persist($resource);
            $this->em->flush();
        }

        return $resource;
    }
}