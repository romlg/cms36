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
 * @package    Zend_Form
 * @subpackage Decorator
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Form_Decorator_Abstract */
require_once 'Zend/Form/Decorator/Abstract.php';

/** Zend_Form_Decorator_Marker_File_Interface */
require_once 'Zend/Form/Decorator/Marker/File/Interface.php';

/** Zend_File_Transfer_Adapter_Http */
require_once 'Zend/File/Transfer/Adapter/Http.php';

/**
 * Zend_Form_Decorator_File
 *
 * Fixes the rendering for all subform and multi file elements
 *
 * @category   Zend
 * @package    Zend_Form
 * @subpackage Decorator
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: File.php 24594 2012-01-05 21:27:01Z matthew $
 */
class Zend_Form_Decorator_File
    extends Zend_Form_Decorator_Abstract
    implements Zend_Form_Decorator_Marker_File_Interface
{
    /**
     * Attributes that should not be passed to helper
     * @var array
     */
    protected $_attribBlacklist = array('helper', 'placement', 'separator', 'value');

    /**
     * Default placement: append
     * @var string
     */
    protected $_placement = 'APPEND';

    /**
     * Get attributes to pass to file helper
     *
     * @return array
     */
    public function getAttribs()
    {
        $attribs = $this->getOptions();

        if (null !== ($element = $this->getElement())) {
            $attribs = array_merge($attribs, $element->getAttribs());
        }

        foreach ($this->_attribBlacklist as $key) {
            if (array_key_exists($key, $attribs)) {
                unset($attribs[$key]);
            }
        }

        return $attribs;
    }

    /**
     * Render a form file
     *
     * @param  string $content
     * @return string
     */
    public function render($content)
    {
        $element = $this->getElement();
        if (!$element instanceof Zend_Form_Element) {
            return $content;
        }

        $view = $element->getView();
        if (!$view instanceof Zend_View_Interface) {
            return $content;
        }

        $name = $element->getName();
        $attribs = $this->getAttribs();
        if (!array_key_exists('id', $attribs)) {
            $attribs['id'] = $name;
        }

        $separator = $this->getSeparator();
        $placement = $this->getPlacement();
        $markup = array();
        $size = $element->getMaxFileSize();
        if ($size > 0) {
            $element->setMaxFileSize(0);
            $markup[] = $view->formHidden('MAX_FILE_SIZE', $size);
        }

        if (Zend_File_Transfer_Adapter_Http::isApcAvailable()) {
            $markup[] = $view->formHidden(ini_get('apc.rfc1867_name'), uniqid(), array('id' => 'progress_key'));
        } else if (Zend_File_Transfer_Adapter_Http::isUploadProgressAvailable()) {
            $markup[] = $view->formHidden('UPLOAD_IDENTIFIER', uniqid(), array('id' => 'progress_key'));
        }

        if ($element->isArray()) {
            $name .= "[]";
            $count = $element->getMultiFile();
            for ($i = 0; $i < $count; ++$i) {
                $htmlAttribs = $attribs;
                $htmlAttribs['id'] .= '-' . $i;
                $markup[] = $view->formFile($name, $htmlAttribs);
            }
        } else {
            $markup[] = $view->formFile($name, $attribs);
        }

        $markup = implode($separator, $markup);

        $html = '';
        switch ($placement) {
            case self::PREPEND:
                $html = $markup . $separator;
            case self::APPEND:
            default:
                $html = $separator . $markup;
        }

        $file = '';
        $default_value = $element->getDefaultValue();
        if ($default_value) {
            $file = '<a href="' . $default_value . '" target="_blank" class="view">Просмотр</a>';
        }

        $button_title = (isset($attribs['button_title'])) ? $attribs['button_title'] : 'Обзор...';
        $ret = '
        <div class="elemBox file">
            '.$file.'
            <a class="button" title="выберите файл">' . $html . '<span>' . $button_title . '</span></a>
            <div class="inputBox">
                <input class="text" type="text" value="' . $default_value . '" name="' . $name . '" readonly="readonly">
            </div>
        </div>';
        return $ret;
    }
}
