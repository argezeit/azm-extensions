<?php
/**
 * AZM Extensions
 *
 * @link      https://github.com/jolicht/azm-extensions for the canonical source repository
 * @copyright Copyright (c) 2015 arge | zeit | media (http://argezeit.at)
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Azm\Mvc\View\Http;

use Zend\EventManager\AbstractListenerAggregate;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\EventManagerInterface as Events;
use Zend\View\Model\ViewModel;

class InjectTemplateListener extends AbstractListenerAggregate
{

    public function attach(Events $events)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'injectTemplate'], -80);
    }

    public function injectTemplate(MvcEvent $e)
    {
        $model = $e->getResult();
        if (!($model instanceof ViewModel)) {
            return;
        }

        if ('' !== $model->getTemplate()) {
            return;
        }

        $controller = $e->getTarget();
        if (!is_object($controller)) {
            return;
        }

        $parts = explode('\\', get_class($controller));
        array_pop($parts);

        $model->setTemplate(implode('/', $parts)  . '/' . $e->getRouteMatch()->getParam('action') . '.phtml');
    }
}