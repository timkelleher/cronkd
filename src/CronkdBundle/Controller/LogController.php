<?php
namespace CronkdBundle\Controller;

use CronkdBundle\Entity\Kingdom;
use CronkdBundle\Entity\Log;
use CronkdBundle\Entity\World;
use CronkdBundle\Event\ViewLogEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @Route("/log")
 */
class LogController extends Controller
{
    /**
     * @Route("/{id}", name="log_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Kingdom $kingdom)
    {
        $currentUser = $this->getUser();
        if ($kingdom->getUser() != $currentUser) {
            throw $this->createAccessDeniedException('Cannot view other Kingdom logs!');
        }
        
        $em = $this->getDoctrine()->getManager();
        $logs = $em->getRepository(Log::class)->findBy([
            'kingdom' => $kingdom,
        ], [
            'createdAt' => 'DESC',
        ]);

        $event = new ViewLogEvent($kingdom);
        $this->get('event_dispatcher')->dispatch('event.view_log', $event);

        return [
            'kingdom' => $kingdom,
            'logs'    => $logs,
        ];
    }
}
