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
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Regex.php 24594 2012-01-05 21:27:01Z matthew $
 */

/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * @category   Zend
 * @package    Zend_Validate
 * @copyright  Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_PhoneNumber extends Zend_Validate_Abstract
{
    const INVALID   = 'IncorrectPhoneNumber';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID   => "Incorrect Phone Number",
    );

    public function isValid($value)
    {
        // Благодаря этому методу значение будет автоматически подставлено в текст ошибки при необходимости
        $this->_setValue($value);

        $pattern = '/^([\+][0-9]{1,3}([ \.\-])?)?([\(]{1}[0-9]{3}[\)])?([0-9A-Z \.\-]{1,32})((x|ext|extension)?[0-9]{1,4}?)$/';

        // Проверка на допустимые символы
        $status = @preg_match($pattern, $value, $matches);

        if (!$status) {
            // С помощью этого метода мы указываем какая именно ошибка произошла
            $this->_error(self::INVALID);
            return false;
        }

        return true;
    }
}
