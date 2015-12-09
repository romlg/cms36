<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: FormLabel.php 22290 2010-05-25 14:27:12Z matthew $
 */

/** Zend_View_Helper_FormElement **/
require_once 'Zend/View/Helper/FormElement.php';

/**
 * Form headline helper
 *
 * @category   Zend
 * @package    Zend_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_FormHeadline extends Zend_View_Helper_FormElement
{
    /**
     * Generates a 'headline' element.
     *
     * @param  string $name The form element name for which the headline is being generated
     * @param  string $value The headline text
     * @param  array $attribs Form element attributes (used to determine if disabled)
     * @return string The element XHTML.
     */
    public function formHeadline($name, $value = null, array $attribs = null)
    {
        $info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable, escape

        // build the element
        if ($disable) {
            // disabled; display nothing
            return  '';
        }

        $value = ($escape) ? $this->view->escape($value) : $value;
        if (!$value && $attribs['default_value']) $value = ($escape) ? $this->view->escape($attribs['default_value']) : $attribs['default_value'];

        foreach ($attribs as $k => $v) {
            if ($k == 'default_value') unset($attribs[$k]);
        }

        $xhtml = '<h2'
                . $this->_htmlAttribs($attribs)
                . '>' . $value . '</h2>';

        return $xhtml;
    }
}
