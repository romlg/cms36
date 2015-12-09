<?php

require_once 'modules/forms.php';

class TGuestbook
{
    function TGuestbook() {
        pr($_POST);
        if (isset($_POST['fld']['act']) AND $_POST['fld']['act'] == "order")
        {

            $ret = array();
            $page = Registry::get("TPage");
            // обязательные поля
            $errors = NULL;

            if (empty($_POST['name']))
            {

                pr('error1');
                $errors =$page->tpl->get_config_vars('guestbook_name');
                pr($errors);
            }
            else
            if (empty($_POST['message']))
            {
                pr('error2');
                $errors =$page->tpl->get_config_vars('guestbook_message');
            }
            else
            {
                pr('work');
            }


            /*
            $errors = array();
            if (empty($data['fld']['name'])) $errors[] = $page->tpl->get_config_vars('guestbook_name');
            if (empty($data['fld']['message'])) $errors[] = $page->tpl->get_config_vars('guestbook_message');
*/


        }


            return $this->mesOrder();
    pr('456');
    }

    function getParams(){
        $page = &TPage::create();
        $page->tpl->config_load(lang().'.conf', 'guestbook');
        $params = array();
        $params['offset'] = get('offset',0,'pg');
        $params['limit'] = $page->tpl->get_config_vars("guestbook_limit") ? $page->tpl->get_config_vars("guestbook_limit") : 5;
        return $params;
    }

    function mesOrder()
    {

        $ret = array();
        $page = Registry::get("TPage");
        // обязательные поля
        $errors = array();

        if (empty($_POST['fld']['name'])) $errors[] = $page->tpl->get_config_vars('error_name');
        if (empty($_POST['fld']['message'])) $errors[] = $page->tpl->get_config_vars('error_message');



    }

    var $response; // массив для вывода отзывов
    var $navigation; // массив для постраничной навигации
    var $form; //вывод формы

    function createresponse() { //Занесение в базу данных поступившего отзыва
        $page =& Registry::get('TPage');
        $url = $page->content['href'];
        if ($_POST['fld']['otzivname']=='' || $_POST['fld']['otzivtext']=='') redirect($url . "?message=guestbook_emp");
        $name = $_POST['fld']['otzivname'];
        $text = $_POST['fld']['otzivtext'];
        $elements = array('name' => $name, 'message' => $text, 'visible' => '0', 'date' => date('Y-m-d'));
        $create = sql_insert('guestbook', $elements);
        redirect($url . "?message=guestbook_newresponse");
    }

    function createresponse2($params) { //Занесение в базу данных поступившего отзыва
        $page =& Registry::get('TPage');
        $url = $page->content['href'];
        if ($_POST['fld']['name']=='' || $_POST['fld']['otzivtext']=='') redirect($url . "?message=guestbook_emp");
        $name = $_POST['fld']['otzivname'];
        $text = $_POST['fld']['otzivtext'];
        $elements = array('id' => $params, 'name' => $name, 'message' => $text, 'visible' => '0', 'date' => date('Y-m-d'));
        $create = sql_insert('guestbook', $elements);
        redirect($url . "?message=guestbook_newresponse");
    }

    function output() { //Формирование постраничного вывода отзывов
        $checkvar = & Registry::get('TPage');

        $lim = $checkvar->tpl->get_config_vars('guestbook_Numberofpage');
        if (isset($_GET['offset']))
            $offset = $_GET['offset']; else $offset = 0;
        $otziv = sql_getRows(" SELECT SQL_CALC_FOUND_ROWS id, name, message, date FROM guestbook WHERE visible=1 ORDER BY date DESC LIMIT " . ($offset) . ", " . $lim . " ");
        $kolvo = sql_getValue(" SELECT FOUND_ROWS()");
        $url = $checkvar->content['href'];

        /*
         * @var TContent $cont
         */
        $cont = & Registry::get('TContent');
        $nav = $cont->getNavigation($kolvo, $lim, $offset, $url);
        $this->response = $otziv;
        $this->navigation = $nav;
    }

    function output2() { //Формирование постраничного вывода отзывов
        $checkvar = & Registry::get('TPage');

        $lim = $checkvar->tpl->get_config_vars('guestbook_Numberofpage2');
        if (isset($_GET['offset']))
            $offset = $_GET['offset']; else $offset = 0;
        $otziv = sql_getRows(" SELECT SQL_CALC_FOUND_ROWS id, name, message, date FROM guestbook WHERE visible=1 ORDER BY date DESC LIMIT " . ($offset) . ", " . $lim . " ");
        $kolvo = sql_getValue(" SELECT FOUND_ROWS()");
        $url = $checkvar->content['href'];

        /*
         * @var TContent $cont
         */
        $cont = & Registry::get('TContent');
        $nav = $cont->getNavigation($kolvo, $lim, $offset, $url);
        $this->response = $otziv;
        $this->navigation = $nav;
    }

    function formgenerate() { //генерируем форму
        $page = &Registry::get('TPage');
        $form = new TForm(array('action' => $page->content['href']), $this);
        $form->form_name = 'guestbook';
        $form->elements = array(
            'name' => array(
                'name' => 'name',
                'type' => 'text',
                'req' => 1,
                'atrib' => 'style="width: 100%; height: 20px;"',
            ),
            'message' => array(
                'name' => 'message',
                'type' => 'textarea',
                'req' => 1,
                'atrib' => 'style="width: 100%; height: 120px;"',
            )
        );
        $fdata = $form->generate();
     /*   if (isset($_GET['ok'])||isset($_GET['fail'])) {
            $fdata['form']['result'] = isset($_GET['ok']) ? 'Успешно отправлено.' : (isset($_GET['fail']) ? 'Ошибка отправки.' : '');
            $fdata['form']['show_result'] = 1;
        }
*/
        /**
         * @var TRusoft_View $view
         */

        $view = &Registry::get('TRusoft_View');
        $view->assign(array('fdata' => $fdata));
        $this->form = $view->render('form.html');
    }

    function showOne($params) {
        $id=$params;

        pr('123');


    }

    function guestbook2() { // главный метод
        if (isset($_POST['fld']['otzivname'])) {
            $this->createresponse();
        }
        $this->output2();
        $otziv = $this->response;
       // $nav = $this->navigation;
        //$this->formgenerate();
        //$formoutput = $this->form;
        return array('outputresp' => $otziv, 'nav' => $nav, 'formoutput' => $formoutput);

    }


    function guestbook() { // главный метод
        if (isset($_POST['fld']['otzivname'])) {
            $this->createresponse();
        }
       $this->output();
       $otziv = $this->response;
       $nav = $this->navigation;
        /*if (!empty($GLOBALS['_POST'])) {
            $actions = get('actions', '', 'p');
            if ($actions) return $this->$actions();
        }*/
    //   $this->formgenerate();
     //  $formoutput = $this->form;
        return array('outputresp' => $otziv, 'nav' => $nav, 'formoutput' => $formoutput);

    }

}
?>
