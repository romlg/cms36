<?php
/**
 * Класс для генерации документации
 *
 */
class THelp_generator extends TTable
{

    var $name = 'help_generator';
    var $table = 'help';

    ######################
    function THelp_generator() {
        global $str, $actions;

        TTable::TTable();

        $actions[$this->name] = array();

        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array('Инструкция по системе администрирования', 'Help generator',),
        ));
    }

    function Show() {
        $data = $this->showGenerate();
        $data['print'] = (int)get('print', 0, 'g');
        return $this->Parse($data, 'help_generator.tmpl');
    }

    /**
     * Генерация документации
     *
     * @return array
     */
    function showGenerate() {
//        $res = mysql_query('SELECT * FROM ' . $this->table . ' ORDER BY module');
//        if (!$res) return $this->error('Таблица ' . $this->table . ' пустая!');

        $ret = array();

        // Запишем все в массив
        $help = array();
/*
        while ($row = mysql_fetch_assoc($res)) {
            list($module, $submodule) = explode('/', $row['module']);
            if (!$submodule) $submodule = $module;

            list($submodule, $method) = explode('.', $submodule);

            if ($module == $submodule . '.' . $method) {
                if (!isset($help[$submodule])) {
                    $help[$submodule] = array(
                        'module' => $submodule,
                    );
                }
                $help[$submodule]['methods'][$method] = $row;
            }
            else {
                if (!isset($help[$module])) {
                    $help[$module] = array(
                        'module' => $module,
                    );
                }
                $help[$module]['sub'][$submodule]['methods'][$method] = $row;
            }

        }
        if (!$help) return $this->error('Не могу сформировать массив!');

        foreach ($help as $key => $val) {
            $methods = $val['methods'];
            unset($help[$key]['methods']);
            if (isset($methods['show'])) $help[$key]['methods']['show'] = $methods['show'];
            if (isset($methods['editform'])) $help[$key]['methods']['editform'] = $methods['editform'];
            unset($methods['show'], $methods['editform']);
            if (!empty($methods)) $help[$key]['methods'] = array_merge($help[$key]['methods'], $methods);
        }
*/
        if (isset($help['strings'])) {
            $help['strings']['methods']['list'] = array(
                'module' => 'strings.list',
                'name' => 'Список строковых констант',
                'text' => '',
            );
            $site_modules = $this->getSiteModules();
            $strings = sql_getRows('SELECT * FROM strings WHERE root_id = ' . domainRootID() . ' ORDER BY module, name');
            $ret['strings'] = array();
            foreach ($strings as $k => $v) {
                if (!isset($ret['strings'][$v['module']])) {
                    $ret['strings'][$v['module']] = array(
                        'title' => isset($site_modules[$v['module']]) ? $site_modules[$v['module']] : $v['module'],
                        'items' => array(),
                    );
                }
                $ret['strings'][$v['module']]['items'][] = $v;
            }
        }

        // Получаем список модулей в таком порядке, как они отображаются в админке слева в меню
        $all_modules = $this->getAllModules();
        array_unshift($all_modules[0]['items'], array('name'=>'fck', 'title'=>'FCK Editor'));

        // Дополним пустые значения данными с ctool
        $empty_help = '<p>Документация по вашему запросу не найдена. Пожалуйста, поставьте задачу на <a href="http://help.rusoft.ru">http://help.rusoft.ru</a>.</p>';
        foreach ($all_modules as $key => $section) {
            foreach ($section['items'] as $k => $module) {
//                if (!isset($help[$module['name']])) {

                    $help[$module['name']]['module'] = $module['name'];

                    // сходим за данными в ctool
                    $url = "http://help.rusoft.ru/getmanual.php?engine=3.6&module=".$module['name'].".show"."&site=".$_SERVER['HTTP_HOST'];
                    $content = file_get_contents($url);
                    if ($content != $empty_help) {
                        if (preg_match('|<h1.*?>(.*)</h1>|sei', $content, $arr)) $title = $arr[1];
                        else $title = '';


                        $help[$module['name']]['methods']['show'] = array(
                            'module' => $module['name'].'.show',
                            'name' => $title,
                            'text' => str_replace("<h1>".$title."</h1>", "", $content),
                        );
                    }

                    $url = "http://help.rusoft.ru/getmanual.php?engine=3.6&module=".$module['name'].".editform"."&site=".$_SERVER['HTTP_HOST'];
                    $content = file_get_contents($url);
                    if ($content != $empty_help) {
                        if (preg_match('|<h1.*?>(.*)</h1>|sei', $content, $arr)) $title = $arr[1];
                        else $title = '';

                        $help[$module['name']]['methods']['editform'] = array(
                            'module' => $module['name'].'.editform',
                            'name' => $title,
                            'text' => str_replace("<h1>".$title."</h1>", "", $content),
                        );
                    }
                }

                if (!isset($help[$module['name']]['methods'])) unset($help[$module['name']]);
//            }
        }

//        $ret['fck_style'] = !isset($help['fck']) ? 'style="color: red"' : '';

        foreach ($all_modules as $key => $section) {
            foreach ($section['items'] as $k => $module) {
                $all_modules[$key]['items'][$k]['style'] = !isset($help[$module['name']]) ? 'style="color: red"' : '';
            }
        }

        $ret['sections'] = $all_modules;
        $ret['help'] = $help;

        $temp = @$help['footer']['sub']['footer']['methods'];
        if ($temp) {
            $temp = current($temp);
            $ret['footer'] = $temp['text'];
        }
        $temp = @$help['header']['sub']['header']['methods'];
        if ($temp) {
            $temp = current($temp);
            $ret['header'] = $temp['text'];
        }

        return $ret;
    }

    function getSiteModules() {
        global $cfg, $intlang;

        $function_modules = $cfg['function_modules'][getMainRootID()];

        $filter_modules = array('site' => 'Ядро сайта');
        foreach ($function_modules AS $key => $val) {
            $filter_modules[$key] = $val['name'][$intlang];
        }
        return $filter_modules;
    }

    /**
     * Вывод ошибки
     *
     * @param string $err
     */
    function error($err) {
        echo "<font color=red>$err</font>";
        return;
    }

    /**
     * Список всех доступных для данного пользователя модулей
     *
     * @return array
     */
    function getAllModules() {
        global $cfg, $sections, $hidden_sections, $_stat, $_sites;

        $row = array();
        $modules_in_row = array();
        $id = (int)get('id');

        if ($id) {
            $row = $this->getRow($id);
            $row['rights'] = unserialize($row['rights']);
        }

        // если указаны скрытые модули - надо их также вывести
        // для возможности задания прав группам пользователей.
        if (isset($hidden_sections)) {
            $sections = array_merge($sections, $hidden_sections);
        }

        $i = 0;
        foreach ($sections as $key => $section) {
            $row[$i] = array(
                'name' => $section[0],
                'items' => array()
            );
            foreach ($section['modules'] as $module_key => $module) {

                $module_name = $module_key;
                if (count(explode("/", $module_key)) > 1) {
                    $arr = explode("/", $module_key);
                    $module_name = $arr[0];
                }

                if (!is_module_auth($module_name)) {
                    continue;
                }

                // set the title
                unset($title);

                $title = $module[int_langId()];

                if (!isset($title)) {
                    switch ($module) {
                        case 'stat' :
                            $title = $_stat[$module_name][int_langId()];
                            break;
                        case 'sites' :
                            $title = $_sites[$module_name][int_langId()];
                            break;
                    }
                }
                if (!in_array($title, $modules_in_row)) {
                    $row[$i]['items'][] = array(
                        'name' => $module_name,
                        'title' => utf($title),
                    );
                    $modules_in_row[] = $title;
                }
            }
            $i++;
        }

        return $row;
    }
}

$GLOBALS['help_generator'] = & Registry::get('THelp_generator');