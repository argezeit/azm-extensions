<?php

namespace AzmTest\Mvc\View\Http;

use Azm\Mvc\View\Http\InjectTemplateListener;
use Zend\EventManager\EventManager;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use AzmTest\TestSubNamespace\TestController;
use Zend\Mvc\Router\Http\RouteMatch;

class InjectTemplateListenerTest extends \PHPUnit_Framework_TestCase
{
    private $listener;

    protected function setUp()
    {
        $this->listener = new InjectTemplateListener();
    }

    public function testAttach()
    {
        $em = new EventManager();
        $this->listener->attach($em);

        $callbackHandler = $em->getListeners(MvcEvent::EVENT_DISPATCH)->getIterator()->current();

        $callbackMetadata = $callbackHandler->getMetadata();
        $this->assertSame(-80, $callbackMetadata['priority']);
        $this->assertSame(MvcEvent::EVENT_DISPATCH, $callbackMetadata['event']);

        $callback = $callbackHandler->getCallback();

        $this->assertSame($this->listener, $callback[0]);
    }

    public function testInjectTemplateNotViewModelDoesntSetTemplate()
    {
        $event = new MvcEvent();
        $model = $this->getMock('Zend\\View\\Model\\ModelInterface');
        $model->expects($this->never())
              ->method('setTemplate');

        $event->setResult($model);
        $this->listener->injectTemplate($event);
    }

    public function testInjectTemplateAlreadySetDoesntSetTemplate()
    {
        $event = new MvcEvent();
        $model = $this->getMock('Zend\\View\\Model\\ViewModel', array('setTemplate', 'getTemplate'));
        $model->expects($this->once())
              ->method('getTemplate')
              ->will($this->returnValue('someTemplateName'));
        $model->expects($this->never())
              ->method('setTemplate');

        $event->setResult($model);
        $this->listener->injectTemplate($event);
    }

    public function testInjectTemplateControllerIsNotObjectDoesntSetTemplate()
    {
        $event = new MvcEvent();
        $model = $this->getMock('Zend\\View\\Model\\ViewModel', array('setTemplate'));
        $model->expects($this->never())
              ->method('setTemplate');

        $event->setResult($model);
        $event->setTarget('TestTarget');
        $this->listener->injectTemplate($event);
    }

    public function testInjectTemplate()
    {
        require_once __DIR__ . '/_files/TestController.php';

        $event = new MvcEvent();
        $routeMatch = new RouteMatch(array('action' => 'test'));
        $event->setRouteMatch($routeMatch);

        $model = $this->getMock('Zend\\View\\Model\\ViewModel', array('setTemplate'));
        $model->expects($this->once())
              ->method('setTemplate')
              ->with($this->equalTo('AzmTest/TestSubNamespace/test.phtml'));

        $event->setResult($model);
        $event->setTarget(new TestController());
        $this->listener->injectTemplate($event);
    }
}