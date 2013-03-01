<?php

namespace SmartCore\Bundle\EngineBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use SmartCore\Bundle\EngineBundle\Engine\Theme;
use SmartCore\Bundle\EngineBundle\Templater\View;
use SmartCore\Bundle\EngineBundle\Container;

class NodeMapperController extends Controller
{
    public function indexAction(Request $request, $slug)
    {
//        ld($user = $this->container->get('security.context')->getToken()->getUser());
//        ld($this->container->getParameterBag());
//        ld($this->container->getParameter('security.role_hierarchy.roles'));

        // @todo вынести router в другое место... можно сделать в виде отдельного сервиса, например 'engine.folder_router'.
        $router_data = $this->engine('folder')->router($request->getPathInfo());
//        ld($router_data);

        foreach ($router_data['folders'] as $folder) {
            $this->engine('breadcrumbs')->add($folder['uri'], $folder['title'], $folder['descr']);
            if (isset($folder['router_response'])) {
                $mbc = $folder['router_response']->getBreadcrumbs();
                foreach ($mbc as $bc) {
                    $this->engine('breadcrumbs')->add($bc['uri'], $bc['title'], $bc['descr']);
                }
            }
        }

        $this->View->setOptions(array(
            'comment'   => 'Базовый шаблон',
            'template'  => $router_data['template'],
        ));

        $this->engine('html')->title('Smart Core CMS (based on Symfony2 Framework)');

        // @todo убрать в ini-шник шаблона.
        $this->engine('html')->meta('viewport', 'width=device-width, initial-scale=1.0');

        if ($this->get('security.context')->isGranted('ROLE_ADMIN') && !$request->isXmlHttpRequest()) {
            $cmf_front_controls = array(
                'toolbar' => array(
                    'right' => [
                        'eip_toggle' => ["Просмотр", "Редактирование"],
                        'user' => [
                            'title' => $this->container->get('security.context')->getToken()->getUser()->getUserName(),
                            'icon' => 'user',
                            'items' => [
                                'profile' => [
                                    'title' => 'Мой профиль',
                                    'uri' => $this->container->get('router')->generate('fos_user_profile_show'),
                                    'icon' => 'cog',
                                    'overalay' => true,
                                ],
                                'diviver_1' => 'diviver',
                                'logout' => [
                                    'title' => "Выход",
                                    'uri' => $this->container->get('router')->generate('fos_user_security_logout'),
                                    'icon' => "off",
                                    'overalay' => false,
                                ],
                            ],
                        ],
                    ],
                ),
                'node' => array(
                    '__node_3' => array(
                        'edit' => array(
                            'title' => 'Редактировать',
                            'descr' => 'Текстовый блок',
                            'uri' => $request->getBasePath() . '/admin/node/3/',
                            'default' => true,
                        ),
                        'cmf_node_properties' => array(
                            'title' => 'Свойства ноды',
                            'uri' => $request->getBaseUrl() . '/',
                        ),
                    ),
                    '__node_1' => array(
                        'edit' => array(
                            'title' => 'Редактировать',
                            'descr' => 'Текстовый блок',
                            'uri' => $request->getBasePath() . '/',
                            'default' => true,
                        ),
                        'cmf_node_properties' => array(
                            'title' => 'Свойства ноды',
                            'uri' => $request->getBaseUrl() . '/',
                        ),
                    ),
                    '__node_5' => array(
                        'edit' => array(
                            'title' => 'Редактировать',
                            'descr' => 'Меню',
                            'uri' => $request->getBasePath() . '/',
                            'default' => true,
                        ),
                        'add' => array(
                            'title' => 'Добавить пункт меню',
                            'uri' => $request->getBasePath() . '/',
                        ),
                        'cmf_node_properties' => array(
                            'title' => 'Свойства ноды',
                            'uri' => $request->getBaseUrl() . '/',
                        ),
                    ),
                    '__node_6' => array(
                        'edit' => array(
                            'title' => 'Редактировать',
                            'descr' => 'Хлебные крошки',
                            'uri' => $request->getBasePath() . '/',
                            'default' => true,
                        ),
                        'cmf_node_properties' => array(
                            'title' => 'Свойства ноды',
                            'uri' => $request->getBaseUrl() . '/',
                        ),
                    ),
                    '__node_2' => array(
                        'cmf_node_properties' => array(
                            'title' => 'Свойства ноды',
                            'uri' => $request->getBaseUrl() . '/',
                            'default' => true,
                        ),
                    ),
                ),
            );

            $this->engine('JsLib')->request('bootstrap');
            $this->engine('JsLib')->request('jquery-cookie');
            $this->engine('html')->css($this->engine('env')->global_assets . 'cmf/frontend.css');
            $this->engine('html')->js($this->engine('env')->global_assets . 'cmf/frontend.js');
            $this->engine('html')->js($this->engine('env')->global_assets . 'cmf/jquery.ba-hashchange.min.js');
            // @todo продумать как называть "general_data".
            $this->engine('html')->general_data = '<script type="text/javascript">var cmf_front_controls = ' . json_encode($cmf_front_controls, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) . ';</script>';
        }

        $theme_path = $this->engine('env')->base_path . $this->engine('env')->theme_path;
        $this->View->assets = array(
            'theme_path'        => $theme_path,
            'theme_css_path'    => $theme_path . 'css/',
            'theme_js_path'     => $theme_path . 'js/',
            'theme_img_path'    => $theme_path . 'images/',
            'vendor'            => $this->engine('env')->global_assets,
        );

        $this->engine('theme')->processConfig($this->View);

        foreach ($this->engine('JsLib')->all() as $res) {
            if (isset($res['js']) and is_array($res['js'])) {
                foreach ($res['js'] as $js) {
                    $this->engine('html')->js($js, 200);
                }
            }
            if (isset($res['css']) and is_array($res['css'])) {
                foreach ($res['css'] as $css) {
                    $this->engine('html')->css($css, 200);
                }
            }
        }

        $this->View->blocks = new View(array(
            'comment'   => 'Блоки',
            'engine'    => 'echo',
        ));

        $nodes_list = $this->engine('node')->buildNodesListByFolders($router_data['folders']);
//        ld($nodes_list);

        $this->buildModulesData($nodes_list);

//        sc_dump($this->View->blocks);

//        sc_dump($this->renderView("MenuModule::menu.html.twig", array()));
//        sc_dump($this->renderView("TexterModule::texter.html.twig", array('text' => 777)));
//        sc_dump($this->forward('TexterModule:Test:hello', array('text' => 'yahoo :)'))->getContent());
//        sc_dump($this->forward('2:Test:index')->getContent());

//        $tmp = $this->forward(8);
//        $tmp = $this->forward('MenuModule:Menu:index');
//        sc_dump(get_class($tmp));
//        sc_dump($tmp->getContentRaw());
//        echo $tmp->getContent();
        
        if ($this->container->has('smart_core_engine.active_theme')) {
            $activeTheme = $this->container->get('smart_core_engine.active_theme');
            $activeTheme->setThemes(array('web', 'tablet', 'phone'));
            //$activeTheme->setName('tablet');
        }

        return new Response($this->container->get('templating')->render("::{$this->View->getTemplateName()}.html.twig", array(
                'html'  => $this->engine('Html'),
                'block' => $this->View->blocks,
            )),
            $router_data['status']
        );
    }
    
    /**
     * Сборка "блоков" из подготовленного списка нод.
     * По мере прохождения, подключаются и запускаются нужные модули с нужными параметрами.
     */
    protected function buildModulesData($nodes_list)
    {
        $blocks = $this->engine('block')->all();
        
        // Каждый "блок" является объектом вида.
        foreach ($blocks as $block) {
            $this->View->blocks->$block['name'] = new View();
        }

        define('_IS_CACHE_NODES', false); // @todo remove

        foreach ($nodes_list as $node_id => $node_properties) {
            // Не собираем ноду, если она уже была отработала в механизе nodeAction()
            if ($node_id == $this->front_end_action_node_id) {
                continue;
            }

            $block_name = $blocks[$node_properties['block_id']]['name'];

            // Обнаружены параметры кеша.
            if (_IS_CACHE_NODES and $node_properties['is_cached'] and !empty($node_properties['cache_params']) and $this->engine('env')->cache_enable ) {
                $cache_params = unserialize($node_properties['cache_params']);
                if (isset($cache_params['id']) and is_array($cache_params['id'])) {
                    $cache_id = array();
                    foreach ($cache_params['id'] as $key => $dummy) {
                        switch ($key) {
                            case 'current_folder_id':
                                $cache_id['current_folder_id'] = $this->engine('env')->current_folder_id;
                                break;
                            case 'user_id':
                                $cache_id['user_id'] = $this->engine('env')->user_id;
                                break;
                            case 'parser_data': // @todo route_data
                                $cache_id['parser_data'] = $node_properties['parser_data'];
                                break;
                            case 'request_uri':
                                $cache_id['parser_data'] = $_SERVER['REQUEST_URI'];
                                break;
                            case 'user_groups':
                                $user_data = $this->User->getData();
                                $cache_id['user_groups'] = $user_data['groups'];
                                break;
                            default;
                        }
                    }
                    $cache_params['id'] = $cache_id;
                }
                $cache_params['id']['node_id'] = $node_id;
                $cache_params['nodes'][$node_id] = 1;
            } else {
                $cache_params = null;
            }

            // Попытка взять HTML кеш ноды.
            if (_IS_CACHE_NODES
                and !empty($cache_params)
                and $this->Cookie->sc_frontend_mode !== 'edit'
                and $html_cache = $this->Cache_Node->loadHtml($cache_params['id'])
            ) {
                // $this->EE->data[$block_name][$node_id]['html_cache'] = $html_cache; @todo !!!!!!!!
            }
            // Кеша нет.
            else { 
                // Если разрешены права на запись ноды, то создаётся объект с административными методами и запрашивается у него данные для фронтальных элементов управления.
                /*
                if ($this->Permissions->isAllowed('node', 'write', $node_properties['permissions']) and ($this->Permissions->isRoot() or $this->Permissions->isAdmin()) ) {
                    $Module = $Node->getModuleInstance($node_id, true);
                } else {
                    $Module = $Node->getModuleInstance($node_id, false);
                }
                */
                
                $Module = $this->forward($node_id, array(
                    '_eip' => true,
                ));

                // Указать шаблонизатору, что надо сохранить эту ноду как html.
                // @todo ПЕРЕДЕЛАТЬ!!! подумать где выполнять кеширование, внутри объекта View или где-то снаружи.
                // @todo ВАЖНО подумать как тут поступить т.к. эта кука может стоять у гостя!!!
                if (_IS_CACHE_NODES and !empty($cache_params) and $this->Cookie->sc_frontend_mode !== 'edit') {
//                    $this->EE->data[$block_name][$node_id]['store_html_cache'] = $Module->getCacheParams($cache_params);
                } 

                // Получение данных для фронт-админки ноды.
                // @todo сделать нормальную проверку на возможность управления нодой. сейчас пока считается, что юзер с ИД = 1 имеет право админить.
                // @todo также тут надо учитывать режим Фронт-Админки. если он выключен, то вытягивать фронт-контролсы нет смысла.
                
                //if ($this->Permissions->isAllowed('node', 'write', $node_properties['permissions']) and $this->Cookie->sc_frontend_mode == 'edit') {
                if ( false ) {
                    $front_controls = $Module->getFrontControls();
                    
                    // Для рута добавляется пунктик "свойства ноды"
                    if ($this->Permissions->isRoot()) {
                        $front_controls['_node_properties'] = array(
                            'popup_window_title' => 'Свойства ноды' . " ( $node_id )",
                            'title'              => 'Свойства',
                            'link'               => HTTP_ROOT . ADMIN . '/structure/node/' . $node_id . '/?popup',
                            'ico'                => 'edit',
                        );
                    }

                    if(is_array($front_controls)) {
                        // @todo сделать выбор типа фронт админки popup/built-in/ajax.
                        $this->View->admin['frontend'][$node_id] = array(
                            // 'type' => 'popup',
                            'node_action_mode'  => $node_properties['node_action_mode'],
                            'doubleclick'       => '@todo двойной щелчок по блоку НОДЫ.',
                            'default_action'    => $Module->getFrontControlsDefaultAction(),
                            // элементы управления, относящиеся ко всей ноде.
                            'controls'          => $front_controls,
                            // элементы управления блоков внутри ноды.
                            //'controls_inner_default_action' = $Module->getFrontControlsInnerDefaultAction(),
                            'controls_inner'    => $Module->getFrontControlsInner(),
                        );
                    }

                    $Module->View->setDecorators("<div class=\"cmf-frontadmin-node\" id=\"_node$node_id\">", "</div>");
                }

            }
            
            if (method_exists($Module, 'getContentRaw')) {
                $this->View->blocks->$block_name->$node_id = $Module->getContentRaw();
            } else {
                $this->View->blocks->$block_name->$node_id = $Module->getContent();
            }

            // @todo пока так выставляются декораторы обрамления ноды.
            if ($this->get('security.context')->isGranted('ROLE_ADMIN') && !$this->get('request')->isXmlHttpRequest()) {
                $this->View->blocks->$block_name->$node_id->setDecorators("<div class=\"cmf-frontadmin-node\" id=\"__node_{$node_id}\">", "</div>");
            }

            unset($Module);
        }
    }
}
