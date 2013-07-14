<?php

namespace Symfony\Cmf\Bundle\CoreBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Cmf\Bundle\CoreBundle\PublishWorkflow\PublishWorkflowChecker;

use Symfony\Cmf\Bundle\RoutingBundle\Routing\DynamicRouter;

/**
 * A request listener that makes sure only published routes and content can be
 * accessed.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
class PublishWorkflowListener implements EventSubscriberInterface
{
    /**
     * @var PublishWorkflowChecker
     */
    protected $publishWorkflowChecker;

    /**
     * The attribute to check with the workflow checker, typically VIEW or VIEW_ANONYMOUS
     *
     * @var string
     */
    private $attribute;

    /**
     * @param PublishWorkflowChecker $publishWorkflowChecker
     * @param string                 $attribute              the attribute name to check
     */
    public function __construct(PublishWorkflowChecker $publishWorkflowChecker, $attribute = 'VIEW')
    {
        $this->publishWorkflowChecker = $publishWorkflowChecker;
        $this->attribute = $attribute;
    }

    public function getAttribute()
    {
        return $this->attribute;
    }
    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;
    }

    /**
     * Handling the request event
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $route = $request->attributes->get(DynamicRouter::ROUTE_KEY);
        if ($route && !$this->publishWorkflowChecker->isGranted($this->getAttribute(), $route)) {
            throw new NotFoundHttpException('Route not found at: ' . $request->getPathInfo());
        }

        $content = $request->attributes->get(DynamicRouter::CONTENT_KEY);
        if ($content && !$this->publishWorkflowChecker->isGranted($this->getAttribute(), $content)) {
            throw new NotFoundHttpException('Content not found for: ' . $request->getPathInfo());
        }
    }

    /**
     * We are only interested in request events.
     *
     * @return array
     */
    static public function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 1)),
        );
    }
}
