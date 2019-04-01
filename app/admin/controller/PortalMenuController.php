<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\PortalMenuModel;
use cmf\controller\AdminBaseController;
use think\Db;
use tree\Tree;
use mindplay\annotations\Annotations;

class PortalMenuController extends AdminBaseController
{
    /**
     *前端菜单管理
     * @PortalMenu(
     *     'name'   => '前端菜单',
     *     'parent' => 'admin/Setting/default',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '前端菜单管理',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $content = hook_one('portal_menu_index_view');

        if (!empty($content)) {
            return $content;
        }

        session('portal_menu_index', 'PortalMenu/index');
        $result     = Db::name('PortalMenu')->order(["list_order" => "ASC"])->select()->toArray();
        $tree       = new Tree();
        $tree->icon = ['&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ '];
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';

        $newPortalMenus = [];
        foreach ($result as $m) {
            $newPortalMenus[$m['id']] = $m;
        }
        foreach ($result as $key => $value) {

            $result[$key]['parent_id_node'] = ($value['parent_id']) ? ' class="child-of-node-' . $value['parent_id'] . '"' : '';
            $result[$key]['style']          = empty($value['parent_id']) ? '' : 'display:none;';
            $result[$key]['str_manage']     = '<a href="' . url("PortalMenu/add", ["parent_id" => $value['id'], "PortalMenu_id" => $this->request->param("PortalMenu_id")])
                . '">' . lang('ADD_SUB_Menu') . '</a>  <a href="' . url("PortalMenu/edit", ["id" => $value['id'], "PortalMenu_id" => $this->request->param("PortalMenu_id")])
                . '">' . lang('EDIT') . '</a>  <a class="js-ajax-delete" href="' . url("PortalMenu/delete", ["id" => $value['id'], "PortalMenu_id" => $this->request->param("PortalMenu_id")]) . '">' . lang('DELETE') . '</a> ';
            $result[$key]['status']         = $value['status'] ? lang('DISPLAY') : lang('HIDDEN');
            if (APP_DEBUG) {
                $result[$key]['app'] = $value['app'] . "/" . $value['controller'] . "/" . $value['action'];
            }
        }

        $tree->init($result);
        $str      = "<tr id='node-\$id' \$parent_id_node style='\$style'>
                        <td style='padding-left:20px;'><input name='list_orders[\$id]' type='text' size='3' value='\$list_order' class='input input-order'></td>
                        <td>\$id</td>
                        <td>\$spacer\$name</td>
                        <td>\$app</td>
                        <td>\$status</td>
                        <td>\$str_manage</td>
                    </tr>";
        $category = $tree->getTree(0, $str);
        $this->assign("category", $category);
        return $this->fetch();
    }

    /**
     *前端所有菜单列表
     * @PortalMenu(
     *     'name'   => '所有菜单',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '前端所有菜单列表',
     *     'param'  => ''
     * )
     */
    public function lists()
    {
        session('portal_menu_index', 'PortalMenu/lists');
        $result = Db::name('PortalMenu')->order(["app" => "ASC", "controller" => "ASC", "action" => "ASC"])->select();
        $this->assign("menus", $result);
        return $this->fetch();
    }

    /**
     *前端菜单添加
     * @PortalMenu(
     *     'name'   => '前端菜单添加',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '前端菜单添加',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        $tree     = new Tree();
        $parentId = $this->request->param("parent_id", 0, 'intval');
        $result   = Db::name('PortalMenu')->order(["list_order" => "ASC"])->select();
        $array    = [];
        foreach ($result as $r) {
            $r['selected'] = $r['id'] == $parentId ? 'selected' : '';
            $array[]       = $r;
        }
        $str = "<option value='\$id' \$selected>\$spacer \$name</option>";
        $tree->init($array);
        $selectCategory = $tree->getTree(0, $str);
        $this->assign("select_category", $selectCategory);
        return $this->fetch();
    }

    /**
     *前端菜单添加提交保存
     * @PortalMenu(
     *     'name'   => '前端菜单添加提交保存',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '前端菜单添加提交保存',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            $result = $this->validate($this->request->param(), 'PortalMenu');
            if ($result !== true) {
                $this->error($result);
            } else {
                $data = $this->request->param();
                Db::name('PortalMenu')->strict(false)->field(true)->insert($data);

                $app          = $this->request->param("app");
                $controller   = $this->request->param("controller");
                $action       = $this->request->param("action");
                $param        = $this->request->param("param");
                $authRuleName = "$app/$controller/$action";
                $PortalMenuName     = $this->request->param("name");

                $findAuthRuleCount = Db::name('auth_rule')->where([
                    'app'  => 'portal',
                    'name' => $authRuleName,
                    'type' => 'portal_url'
                ])->count();
                if (empty($findAuthRuleCount)) {
                    Db::name('AuthRule')->insert([
                        "name"  => $authRuleName,
                        "app"   => 'portal',
                        "type"  => "portal_url", //type 1-admin rule;2-user rule
                        "title" => $PortalMenuName,
                        'param' => $param,
                    ]);
                }
                $sessionPortalMenuIndex = session('portal_menu_index');
                $to                    = empty($sessionPortalMenuIndex) ? "PortalMenu/index" : $sessionPortalMenuIndex;
                $this->_exportAppPortalMenuDefaultLang();
                cache(null, 'portal_menus');// 删除前端菜单缓存
                $this->success("添加成功！", url($to));
            }
        }
    }

    /**
     *前端菜单编辑
     * @PortalMenu(
     *     'name'   => '前端菜单编辑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '前端菜单编辑',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $tree   = new Tree();
        $id     = $this->request->param("id", 0, 'intval');
        $rs     = Db::name('PortalMenu')->where(["id" => $id])->find();
        $result = Db::name('PortalMenu')->order(["list_order" => "ASC"])->select();
        $array  = [];
        foreach ($result as $r) {
            $r['selected'] = $r['id'] == $rs['parent_id'] ? 'selected' : '';
            $array[]       = $r;
        }
        $str = "<option value='\$id' \$selected>\$spacer \$name</option>";
        $tree->init($array);
        $selectCategory = $tree->getTree(0, $str);
        $this->assign("data", $rs);
        $this->assign("select_category", $selectCategory);
        return $this->fetch();
    }

    /**
     *前端菜单编辑提交保存
     * @PortalMenu(
     *     'name'   => '前端菜单编辑提交保存',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '前端菜单编辑提交保存',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        if ($this->request->isPost()) {
            $id      = $this->request->param('id', 0, 'intval');
            $oldPortalMenu = Db::name('PortalMenu')->where(['id' => $id])->find();

            $result = $this->validate($this->request->param(), 'PortalMenu.edit');

            if ($result !== true) {
                $this->error($result);
            } else {
                Db::name('PortalMenu')->strict(false)->field(true)->update($this->request->param());
                $app          = $this->request->param("app");
                $controller   = $this->request->param("controller");
                $action       = $this->request->param("action");
                $param        = $this->request->param("param");
                $authRuleName = "$app/$controller/$action";
                $PortalMenuName     = $this->request->param("name");

                $findAuthRuleCount = Db::name('auth_rule')->where([
                    'app'  => $app,
                    'name' => $authRuleName,
                    'type' => 'admin_url'
                ])->count();
                if (empty($findAuthRuleCount)) {
                    $oldApp        = $oldPortalMenu['app'];
                    $oldController = $oldPortalMenu['controller'];
                    $oldAction     = $oldPortalMenu['action'];
                    $oldName       = "$oldApp/$oldController/$oldAction";
                    $findOldRuleId = Db::name('AuthRule')->where(["name" => $oldName])->value('id');
                    if (empty($findOldRuleId)) {
                        Db::name('AuthRule')->insert([
                            "name"  => $authRuleName,
                            "app"   => $app,
                            "type"  => "admin_url",
                            "title" => $PortalMenuName,
                            "param" => $param
                        ]);//type 1-admin rule;2-user rule
                    } else {
                        Db::name('AuthRule')->where(['id' => $findOldRuleId])->update([
                            "name"  => $authRuleName,
                            "app"   => $app,
                            "type"  => "admin_url",
                            "title" => $PortalMenuName,
                            "param" => $param]);//type 1-admin rule;2-user rule
                    }
                } else {
                    Db::name('AuthRule')->where([
                        'app'  => $app,
                        'name' => $authRuleName,
                        'type' => 'admin_url'
                    ])->update(["title" => $PortalMenuName, 'param' => $param]);//type 1-admin rule;2-user rule
                }
                $this->_exportAppPortalMenuDefaultLang();
                cache(null, 'portal_menus');// 删除前端菜单缓存
                $this->success("保存成功！");
            }
        }
    }

    /**
     *前端菜单删除
     * @PortalMenu(
     *     'name'   => '前端菜单删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '前端菜单删除',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $id    = $this->request->param("id", 0, 'intval');
        $count = Db::name('PortalMenu')->where(["parent_id" => $id])->count();
        if ($count > 0) {
            $this->error("该菜单下还有子菜单，无法删除！");
        }
        if (Db::name('PortalMenu')->delete($id) !== false) {
            $this->success("删除菜单成功！");
        } else {
            $this->error("删除失败！");
        }
    }

    /**
     *前端菜单排序
     * @PortalMenu(
     *     'name'   => '前端菜单排序',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '前端菜单排序',
     *     'param'  => ''
     * )
     */
    public function listOrder()
    {
        $PortalMenuModel = new PortalMenuModel();
        parent::listOrders($PortalMenuModel);
        $this->success("排序更新成功！");
    }

    /**
     * 导入新前端菜单
     * @PortalMenu(
     *     'name'   => '导入新前端菜单',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '导入新前端菜单',
     *     'param'  => ''
     * )
     */
    public function getActions()
    {
        Annotations::$config['cache']                 = false;
        $annotationManager                            = Annotations::getManager();
        $annotationManager->registry['PortalMenu']     = 'app\admin\annotation\PortalMenuAnnotation';
        $annotationManager->registry['PortalMenuRoot'] = 'app\admin\annotation\PortalMenuRootAnnotation';
        $newPortalMenus                                     = [];

        $apps = cmf_scan_dir(APP_PATH . '*', GLOB_ONLYDIR);

        $app = $this->request->param('app', '');
        if (empty($app)) {
            $app = $apps[0];
        }

        if (!in_array($app, $apps)) {
            $this->error('应用' . $app . '不存在!');
        }

        if ($app == 'admin') {
            $filePatten = APP_PATH . $app . '/controller/*Controller.php';
        } else {
            $filePatten = APP_PATH . $app . '/controller/Admin*Controller.php';
        }

        $controllers = cmf_scan_dir($filePatten);

        if (!empty($controllers)) {
            foreach ($controllers as $controller) {
                $controller      = preg_replace('/\.php$/', '', $controller);
                $controllerName  = preg_replace('/\Controller$/', '', $controller);
                $controllerClass = "app\\$app\\controller\\$controller";

                $PortalMenuAnnotations = Annotations::ofClass($controllerClass, '@PortalMenuRoot');

                if (!empty($PortalMenuAnnotations)) {
                    foreach ($PortalMenuAnnotations as $PortalMenuAnnotation) {

                        $name      = $PortalMenuAnnotation->name;
                        $icon      = $PortalMenuAnnotation->icon;
                        $type      = 0;//1:有界面可访问菜单,2:无界面可访问菜单,0:只作为菜单
                        $action    = $PortalMenuAnnotation->action;
                        $status    = empty($PortalMenuAnnotation->display) ? 0 : 1;
                        $listOrder = floatval($PortalMenuAnnotation->order);
                        $param     = $PortalMenuAnnotation->param;
                        $remark    = $PortalMenuAnnotation->remark;

                        if (empty($PortalMenuAnnotation->parent)) {
                            $parentId = 0;
                        } else {

                            $parent      = explode('/', $PortalMenuAnnotation->parent);
                            $countParent = count($parent);
                            if ($countParent > 3) {
                                throw new \Exception($controllerClass . ':' . $action . '  @PortalMenuRoot parent格式不正确!');
                            }

                            $parentApp        = $app;
                            $parentController = $controllerName;
                            $parentAction     = '';

                            switch ($countParent) {
                                case 1:
                                    $parentAction = $parent[0];
                                    break;
                                case 2:
                                    $parentController = $parent[0];
                                    $parentAction     = $parent[1];
                                    break;
                                case 3:
                                    $parentApp        = $parent[0];
                                    $parentController = $parent[1];
                                    $parentAction     = $parent[2];
                                    break;
                            }

                            $findParentPortalMenu = Db::name('portal_menu')->where([
                                'app'        => $parentApp,
                                'controller' => $parentController,
                                'action'     => $parentAction
                            ])->find();

                            if (empty($findParentPortalMenu)) {
                                $parentId = Db::name('portal_menu')->insertGetId([
                                    'app'        => $parentApp,
                                    'controller' => $parentController,
                                    'action'     => $parentAction,
                                    'name'       => '--new--'
                                ]);
                            } else {
                                $parentId = $findParentPortalMenu['id'];
                            }
                        }

                        $findPortalMenu = Db::name('portal_menu')->where([
                            'app'        => $app,
                            'controller' => $controllerName,
                            'action'     => $action
                        ])->find();

                        if (empty($findPortalMenu)) {

                            Db::name('portal_menu')->insert([
                                'parent_id'  => $parentId,
                                'type'       => $type,
                                'status'     => $status,
                                'list_order' => $listOrder,
                                'app'        => $app,
                                'controller' => $controllerName,
                                'action'     => $action,
                                'param'      => $param,
                                'name'       => $name,
                                'icon'       => $icon,
                                'remark'     => $remark
                            ]);

                            $PortalMenuName = $name;

                            array_push($newPortalMenus, "$app/$controllerName/$action 已导入");

                        } else {

                            if ($findPortalMenu['name'] == '--new--') {
                                Db::name('portal_menu')->where([
                                    'app'        => $app,
                                    'controller' => $controllerName,
                                    'action'     => $action
                                ])->update([
                                    'parent_id'  => $parentId,
                                    'type'       => $type,
                                    'status'     => $status,
                                    'list_order' => $listOrder,
                                    'param'      => $param,
                                    'name'       => $name,
                                    'icon'       => $icon,
                                    'remark'     => $remark
                                ]);
                                $PortalMenuName = $name;
                            } else {
                                // 只关注菜单层级关系,是否有视图
                                Db::name('portal_menu')->where([
                                    'app'        => $app,
                                    'controller' => $controllerName,
                                    'action'     => $action
                                ])->update([
                                    //'parent_id' => $parentId,
                                    'type'      => $type,
                                ]);
                                $PortalMenuName = $findPortalMenu['name'];
                            }

                            array_push($newPortalMenus, "$app/$controllerName/$action 层级关系已更新");
                        }

                        $authRuleName      = "{$app}/{$controllerName}/{$action}";
                        $findAuthRuleCount = Db::name('auth_rule')->where([
                            'app'  => $app,
                            'name' => $authRuleName,
                            'type' => 'admin_url'
                        ])->count();

                        if ($findAuthRuleCount == 0) {
                            Db::name('auth_rule')->insert([
                                'app'   => $app,
                                'name'  => $authRuleName,
                                'type'  => 'admin_url',
                                'param' => $param,
                                'title' => $PortalMenuName
                            ]);
                        } else {
                            Db::name('auth_rule')->where([
                                'app'  => $app,
                                'name' => $authRuleName,
                                'type' => 'admin_url',
                            ])->update([
                                'param' => $param,
                                'title' => $PortalMenuName
                            ]);
                        }

                    }
                }

                $reflect = new \ReflectionClass($controllerClass);
                $methods = $reflect->getMethods(\ReflectionMethod::IS_PUBLIC);

                if (!empty($methods)) {
                    foreach ($methods as $method) {

                        if ($method->class == $controllerClass && strpos($method->name, '_') !== 0) {
                            $PortalMenuAnnotations = Annotations::ofMethod($controllerClass, $method->name, '@PortalMenu');

                            if (!empty($PortalMenuAnnotations)) {

                                $PortalMenuAnnotation = $PortalMenuAnnotations[0];

                                $name      = $PortalMenuAnnotation->name;
                                $icon      = $PortalMenuAnnotation->icon;
                                $type      = $PortalMenuAnnotation->hasView ? 1 : 2;//1:有界面可访问菜单,2:无界面可访问菜单,0:只作为菜单
                                $action    = $method->name;
                                $status    = empty($PortalMenuAnnotation->display) ? 0 : 1;
                                $listOrder = floatval($PortalMenuAnnotation->order);
                                $param     = $PortalMenuAnnotation->param;
                                $remark    = $PortalMenuAnnotation->remark;

                                if (empty($PortalMenuAnnotation->parent)) {
                                    $parentId = 0;
                                } else {
                                    $parent      = explode('/', $PortalMenuAnnotation->parent);
                                    $countParent = count($parent);
                                    if ($countParent > 3) {
                                        throw new \Exception($controllerClass . ':' . $action . '  @PortalMenuRoot parent格式不正确!');
                                    }

                                    $parentApp        = $app;
                                    $parentController = $controllerName;
                                    $parentAction     = '';

                                    switch ($countParent) {
                                        case 1:
                                            $parentAction = $parent[0];
                                            break;
                                        case 2:
                                            $parentController = $parent[0];
                                            $parentAction     = $parent[1];
                                            break;
                                        case 3:
                                            $parentApp        = $parent[0];
                                            $parentController = $parent[1];
                                            $parentAction     = $parent[2];
                                            break;
                                    }

                                    $findParentPortalMenu = Db::name('portal_menu')->where([
                                        'app'        => $parentApp,
                                        'controller' => $parentController,
                                        'action'     => $parentAction
                                    ])->find();

                                    if (empty($findParentPortalMenu)) {
                                        $parentId = Db::name('portal_menu')->insertGetId([
                                            'app'        => $parentApp,
                                            'controller' => $parentController,
                                            'action'     => $parentAction,
                                            'name'       => '--new--'
                                        ]);
                                    } else {
                                        $parentId = $findParentPortalMenu['id'];
                                    }
                                }

                                $findPortalMenu = Db::name('portal_menu')->where([
                                    'app'        => $app,
                                    'controller' => $controllerName,
                                    'action'     => $action
                                ])->find();

                                if (empty($findPortalMenu)) {

                                    Db::name('admin_menu')->insert([
                                        'parent_id'  => $parentId,
                                        'type'       => $type,
                                        'status'     => $status,
                                        'list_order' => $listOrder,
                                        'app'        => $app,
                                        'controller' => $controllerName,
                                        'action'     => $action,
                                        'param'      => $param,
                                        'name'       => $name,
                                        'icon'       => $icon,
                                        'remark'     => $remark
                                    ]);

                                    $PortalMenuName = $name;

                                    array_push($newPortalMenus, "$app/$controllerName/$action 已导入");

                                } else {
                                    if ($findPortalMenu['name'] == '--new--') {
                                        Db::name('admin_menu')->where([
                                            'app'        => $app,
                                            'controller' => $controllerName,
                                            'action'     => $action
                                        ])->update([
                                            'parent_id'  => $parentId,
                                            'type'       => $type,
                                            'status'     => $status,
                                            'list_order' => $listOrder,
                                            'param'      => $param,
                                            'name'       => $name,
                                            'icon'       => $icon,
                                            'remark'     => $remark
                                        ]);
                                        $PortalMenuName = $name;
                                    } else {
                                        // 只关注菜单层级关系,是否有视图
                                        Db::name('admin_menu')->where([
                                            'app'        => $app,
                                            'controller' => $controllerName,
                                            'action'     => $action
                                        ])->update([
                                            //'parent_id' => $parentId,
                                            'type'      => $type,
                                        ]);
                                        $PortalMenuName = $findPortalMenu['name'];
                                    }


                                    array_push($newPortalMenus, "$app/$controllerName/$action 已更新");
                                }

                                $authRuleName      = "{$app}/{$controllerName}/{$action}";
                                $findAuthRuleCount = Db::name('auth_rule')->where([
                                    'app'  => $app,
                                    'name' => $authRuleName,
                                    'type' => 'admin_url'
                                ])->count();

                                if ($findAuthRuleCount == 0) {
                                    Db::name('auth_rule')->insert([
                                        'app'   => $app,
                                        'name'  => $authRuleName,
                                        'type'  => 'admin_url',
                                        'param' => $param,
                                        'title' => $PortalMenuName
                                    ]);
                                } else {
                                    Db::name('auth_rule')->where([
                                        'app'  => $app,
                                        'name' => $authRuleName,
                                        'type' => 'admin_url',
                                    ])->update([
                                        'param' => $param,
                                        'title' => $PortalMenuName
                                    ]);
                                }
                            }

                        }
                    }
                }

            }
        }

        $index     = array_search($app, $apps);
        $nextIndex = $index + 1;
        $nextIndex = $nextIndex >= count($apps) ? 0 : $nextIndex;
        if ($nextIndex) {
            $this->assign("next_app", $apps[$nextIndex]);
        }
        $this->assign("app", $app);
        $this->assign("new_PortalMenus", $newPortalMenus);

        cache(null, 'portal_menus');// 删除前端菜单缓存

        return $this->fetch();

    }

    /**
     *  导出前端菜单语言包
     */
    private function _exportAppPortalMenuDefaultLang()
    {
        $PortalMenus         = Db::name('PortalMenu')->order(["app" => "ASC", "controller" => "ASC", "action" => "ASC"])->select();
        $langDir       = config('DEFAULT_LANG');
        $PortalMenuLang = CMF_ROOT . "data/lang/" . $langDir . "/portal_menu.php";

        if (!empty($PortalMenuLang) && !file_exists_case($PortalMenuLang)) {
            mkdir(dirname($PortalMenuLang), 0777, true);
        }

        $lang = [];

        foreach ($PortalMenus as $PortalMenu) {
            $lang_key        = strtoupper($PortalMenu['app'] . '_' . $PortalMenu['controller'] . '_' . $PortalMenu['action']);
            $lang[$lang_key] = $PortalMenu['name'];
        }

        $langStr = var_export($lang, true);
        $langStr = preg_replace("/\s+\d+\s=>\s(\n|\r)/", "\n", $langStr);

        if (!empty($PortalMenuLang)) {
            file_put_contents($PortalMenuLang, "<?php\nreturn $langStr;");
        }
    }
}