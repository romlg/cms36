<?php

require_once (module('custom_tree'));

class TCustom_treeCheck extends TCustom_tree
{
    var $value;
    var $name = 'treecheck';
    var $table = 'tree';

    ###############################
    function TCustom_treeCheck() {
        TCustom_tree::TCustom_tree();
        global $str;
        $str[get_class_name($this)] = array_merge($str[get_class_name($this)], array(
            'title' => array(
                'Выберите разделы',
                'Select pages',
            ),
        ));
        if (isset($_GET['table'])) $this->table = get('table', '', 'g');
        if (!$this->table) echo 'Не указано название таблицы.';
    }

    ###############################
    function Show() {
        $id = (int)get('id');

        $ret = array();
        $this->AddStrings($ret);

        $ret['path'] = $this->GetPath($id);
        $ret['tree'] = $this->GetAllTree(array('id' => $id, 'checkboxtree' => true));

        $ret['name'] = $this->name;
        $ret['fieldname'] = get("fieldname", '', 'gp');
        $ret['target_ids'] = get("target_ids", '', 'gp');

        return Parse($ret, 'custom_tree/custom_treecheck/custom_treecheck.tmpl');
    }

    /**
     * Сабмит формы с выбранными с помощью чекбоксов разделами
     */
    function Select() {
        $frame = get("is_fancy", "");
        if (isset($_POST['item'])) {
            $ids = array_keys($_POST['item']);
        } else $ids = array();

        if ($ids) {
            $res = mysql_query('SELECT name FROM ' . $this->table . ' WHERE id IN (' . implode(',', $ids) . ')');
            $title = array();
            while ($row = mysql_fetch_assoc($res)) {
                $title[] = $row['name'];
            }
            $titles = implode(', ', $title);
            $_ids = implode(',', $ids);
        } else {
            $titles = '';
            $_ids = '';
        }

        if ($frame) {
            echo "<script>
    		try {
    			var win = window.parent.top.opener;
    			win.document.getElementById('" . $_POST['fieldname'] . "').value = '" . $titles . "'
    			win.document.getElementById('" . $_POST['target_ids'] . "').value = '" . $_ids . "'
    			window.close();
    		} catch (e) {
    		}
    		</script>";
        } else {
            $name = $this->getQuoteJqueryString(get("fieldname", '', 'gp'));
            echo "
    		<script type='text/javascript' src='js/jquery-1.5.2.min.js'></script>
    		<script>
            $('#treecheck_" . $name . "', window.parent.document).val('" . $titles . "');
            $('#" . $name . "', window.parent.document).val('" . $_ids . "');
            window.parent.$.fancybox.close();
            </script>";
            exit;
        }
    }

    //***********************************
    function getQuoteJqueryString($string) {
        $search = array("!", '"', "#", "$", "%", "&", "'", "(", ")", "*", "+",
            ",", ".", "/", ":", ";", "<", "=", ">", "?", "@", "[", "]", "^",
            "`", "{", "|", "}", "~");

        $replace = array("\\\!", '\\\"', "\\\#", "\\\$", "\\\%", "\\\&", "\\\'", "\\\(", "\\\)", "\\\*",
            "\\\+", "\\\,", "\\\.", "\\\/", "\\\:", "\\\;", "\\\<", "\\\=", "\\\>", "\\\?", "\\\@", "\\\[", "\\\]",
            "\\\^", "\\\`", "\\\{", "\\\|", "\\\}", "\\\~");

        return str_replace($search, $replace, $string);
    }
}

$GLOBALS['custom_tree__custom_treecheck'] = & Registry::get('TCustom_treeCheck');