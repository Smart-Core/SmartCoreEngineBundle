<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;
use SmartCore\Bundle\EngineBundle\Templater\View;

class Controller extends BaseController
{
    /**
     * View object
     * @var View
     */
    public $View; // @todo переделать в protected.

    /**
     * Constructor.
     * 
     * Вызывается как parent::__construct(); из дочерних классов.
     */
    public function __construct()
    {
        //$this->View = $this->Templating->getView();
        $this->View = new View();
        // По умолчанию устанавливается имя шаблона, как короткое имя контроллера.
        $reflector = new \ReflectionClass(get_class($this));
        
        if (substr($reflector->getShortName(), -10) == 'Controller') {
            $this->View->setTemplateName(substr($reflector->getShortName(), 0, strlen($reflector->getShortName()) - 10));
        } else {
            $this->View->setTemplateName($reflector->getShortName());
        }
        
        $this->View->setPaths(array(
//            $this->Env->dir_web_root . $this->Env->dir_themes . 'views/' . $namespace,
//            $this->Env->dir_app . 'views/' . $namespace,
            realpath(dirname($reflector->getFileName()) . '/../Resources/views'),
        ));        
    }    
    
    /**
     * Магическое обращение к сервисам.
     *
     * @param string $name
     */
    public function __get($name)
    {
        if (!is_object($this->container)) {
            throw new \Exception('SmartCore\EngineBundle: Container is not accesible. Service "engine.' . $name . '" fail.');
        }
        
        if ($this->container->has('engine.' . $name)) {
            return $this->container->get('engine.' . $name);
        } else {
            throw new \Exception('SmartCore\EngineBundle: Service "engine.' . strtolower($name) . '" does not register.');
        }
    }
    
    /**
     * Returns a RedirectResponse to the given URL.
     *
     * @param string  $url    The URL to redirect to
     * @param integer $status The status code to use for the Response
     *
     * @return RedirectResponse
     */
    public function redirect($url, $status = 302)
    {
        $str = (null == $url) ? $_SERVER['REQUEST_URI'] : $url;
        header('Location: ' . $str);
        exit;
        //return new RedirectResponse($url, $status);
    }
}