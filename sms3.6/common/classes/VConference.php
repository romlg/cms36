<?php

$zend_dir = array(dirname(__FILE__));
set_include_path(get_include_path() . PATH_SEPARATOR . implode(DIRECTORY_SEPARATOR, $zend_dir));
include_once 'Zend/XmlRpc/Client.php';

/**
 * Класс для работы с API видеоконференций
 */
class VConference
{
    protected $_serverName = 'http://dev.video.oprf.ru/api/index';
    protected $_client;
    protected $_apiKey = '53b22118e9195378b8c577f4a844e9ab';

    /**
     * Создаёт новую конференцию
     *
     * @param array $params - массив параметров для конференции
     * @return unknown
     */

    function addNewConference($params) {
        $client = new Zend_XmlRpc_Client($this->_serverName);

        foreach ($params AS $k=>$v) {
            $params[$k] = stripslashes($v);
        }

        $data['title'] = iconv('windows-1251', 'utf-8', $params['title']);
        $data['public'] = isset($params['public']) ? (int)$params['public'] : (int)0;
        $data['description'] = isset($params['description']) ? iconv('windows-1251', 'utf-8', $params['description']) : '';
        $data['descriptionFull'] = isset($params['descriptionfull']) ? iconv('windows-1251', 'utf-8', $params['descriptionfull']) : '';
        $data['notifyBeforeTime'] = isset($params['notifybeforetime']) ? (int)$params['notifybeforetime'] : (int)0;
        if (isset($params['startDate'])) $data['startdate'] = $params['startdate'];
        if (isset($params['stopRecordDatetime'])) $data['stoprecorddatetime'] = $params['stoprecorddatetime'];
        if (isset ($params['imagebase64'])) {
            $data['imageBase64'] = base64_encode(file_get_contents($params['imagebase64']));
        }

        // параметры по умолчанию
        $data['autoRecord'] = (int)1;
        $data['autoRecordStop'] = (int)1;
        $data['notifyInvited'] = (int)1;
        $data['disableEdit'] = (int)0;
        $data['disableMemberList'] = (int)0;

        $e = null;
        $errors = array();
        $res = 0;
        try {
            $res = $client->call('conference.add', array($this->_apiKey, $data));
        } catch (Exception $e) {
            $code = $e->getCode();

            $errors = array(
                'error_code' => $code,
                'error_text' => iconv('utf-8', 'windows-1251', $e->getMessage()),
            );
        }

        return $res ? $res : $errors;
    }


    /**
     * Получаем id всех пользователей конференции
     *
     * @param array $conferenceId - id конференции в системе ВКС
     * @return array $serviceUserIds - массив с сервисными id-ми пользователей
     */

    function conferenceMembers ($conferenceId) {
        $client = new Zend_XmlRpc_Client($this->_serverName);

        $e = null;
        $errors = array();
        try {
            $res = $client->call('conference.getMembers', array($this->_apiKey, $conferenceId));
        } catch (Exception $e) {
            $code = $e->getCode();

            $errors = array(
                'error_code' => $code,
                'error_text' => iconv('utf-8', 'windows-1251', $e->getMessage()),
            );
        }

        return $res ? $res : $errors;
    }


    /**
     * Возвращает информацию о всех участниках конференции
     *
     * @param array $conferenceId - id конференции в системе ВКС
     */

    function conferenceMembersAll ($conferenceId) {
        $client = new Zend_XmlRpc_Client($this->_serverName);

        $e = null;
        $errors = array();
        try {
            $res = $client->call('conference.getMembersAll', array($this->_apiKey, $conferenceId));
        } catch (Exception $e) {
            $code = $e->getCode();

            $errors = array(
                'error_code' => $code,
                'error_text' => iconv('utf-8', 'windows-1251', $e->getMessage()),
            );
        }

        return $res ? $res : $errors;
    }


    /**
     * Создаёт нового пользователя
     * @param array $params - массив параметров для пользователя
     * return возвращает true если создание прошло успешно, если нет то массив код и текст ошибки
     */
    function createUser($params) {
        $client = new Zend_XmlRpc_Client($this->_serverName);

        foreach ($params AS $k=>$v) {
            $params[$k] = stripslashes($v);
        }

        $user_params[0] = $this->_apiKey;
        $user_params[1] = isset($params['id']) ? $params['id'] : '';
        $user_params[2] = isset($params['email']) ? $params['email'] : '';
        $user_params[3] = isset($params['name']) ? iconv( 'windows-1251', 'utf-8', $params['name']) : '';
        $user_params[4] = isset($params['position']) ? iconv( 'windows-1251', 'utf-8', $params['position']) : "";

        if (isset ($params['avatar']) && file_exists($params['avatar'])) {
            $user_params[5] = base64_encode(file_get_contents($params['avatar']));
        } else {
            $user_params[5] = "";
        }

        $user_params[6] = isset($params['sendmail']) ? $params['sendmail'] : false;
        $user_params[7] = isset($params['password']) ? $params['password'] : "";

        $e = null;
        $errors = array();
        try {
            $ret = $client->call('user.add', $user_params);
        } catch (Exception $e) {
            $code = $e->getCode();

            $errors = array(
                'error_code' => $code,
                'error_text' => iconv('utf-8', 'windows-1251', $e->getMessage()),
            );
        }

        return empty($errors) ? $ret : $errors;
    }

    /**
     * Проверяет существует ли пользователь в системе ВКС
     *
     * @param integer $serviceUserId - сервисный id пользователя
     * @return array
     */
    function existUser($serviceUserId){
        $client = new Zend_XmlRpc_Client($this->_serverName);
        $users[1] = $serviceUserId;

        $e = null;
        $errors = array();
        try {
            $check = $client->call('user.is_exists', array($this->_apiKey, $users));
        } catch (Exception $e) {
            $code = $e->getCode();

            $errors = array(
                'error_code' => $code,
                'error_text' => iconv('utf-8', 'windows-1251', $e->getMessage()),
            );
        }

        return $check ? $check : $errors;
    }

    /**
     * Обновляет данные о пользователе
     *
     * @param array $params - массив параметров для пользователя
     */
    function updateUser($params) {
        $client = new Zend_XmlRpc_Client($this->_serverName);

        $user_params[0] = $this->_apiKey;
        $user_params[2] = isset($params['email']) ? $params['email'] : '';
        $user_params[3] = isset($params['name']) ? $params['name'] : '';
        $user_params[4] = isset($params['position']) ? $params['position'] : '';
        if (isset ($params['avatar']) && file_exists($params['avatar'])) {
            $user_params[5] = base64_encode(file_get_contents($params['avatar']));
        }
        $user_params[7] = isset($params['password']) ? $params['password'] : '';

        $res = $client->call('user.update', $user_params);

        /**
         * TO DO обработка возникших ошибок
         */
    }

    /**
     * Получить информацию в ВКС о пользователе
     *
     * @param string $serviceUserId - сервисный id пользователя
     */
    function getUser($serviceUserId){
        $client = new Zend_XmlRpc_Client($this->_serverName);

        $e = null;
        $errors = array();
        try {
            $res = $client->call('user.get', array($this->_apiKey, $serviceUserId));
        } catch (Exception $e) {
            $code = $e->getCode();

            $errors = array(
                'error_code' => $code,
                'error_text' => iconv('utf-8', 'windows-1251', $e->getMessage()),
            );
        }
        return $res ? $res : $errors;
    }

    /**
     * Добавление пользователя(ей) к конференции
     *
     * @param integer $conferenceid
     * @param array   $serviceUserIds - массив с сервисными id-ми пользователей
     * @param array   $adminRights - массив с сервисными id-ми пользователей
     */
    function addUserToConference($conferenceid, $serviceUserIds, $adminRights, $ownerIds) {
        $adminRights = empty($adminRights) ? array() : $adminRights;
        $client = new Zend_XmlRpc_Client($this->_serverName);

        $e = null;
        $errors = array();
        try {
            $ret = $client->call('conference.addMember', array($this->_apiKey, $conferenceid, $serviceUserIds, $adminRights, $ownerIds));
        } catch (Exception $e) {
            $code = $e->getCode();

            $errors = array(
                'error_code' => $code,
                'error_text' => iconv('utf-8', 'windows-1251', $e->getMessage()),
            );
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Удаление пользователя(ей) из конференции
     *
     * @param integer $conferenceid
     * @param array   $serviceUserIds - массив с сервисными id-ми пользователей
     */
    function delUserToConference($conferenceid, $serviceUserIds) {
        $client = new Zend_XmlRpc_Client($this->_serverName);

        $e = null;
        $errors = array();
        try {
            $ret = $client->call('conference.removeMember', array($this->_apiKey, $conferenceid, $serviceUserIds));
        } catch (Exception $e) {
            $code = $e->getCode();

            $errors = array(
                'error_code' => $code,
                'error_text' => iconv('utf-8', 'windows-1251', $e->getMessage()),
            );
        }

        return empty($errors) ? $ret : $errors;
    }


    /**
     * Создание видео-обращения
     *
     * @param integer $serviceUserId - id пользователя в  системе
     * @return array(
     *              requestId (int) - идентификатор видео-обращения на стороне ВКС
     *              uri (string) - ссылка для показа флешки для веб-камеры для записи
     *         );
     */
    function create_record($serviceUserId) {
        $client = new Zend_XmlRpc_Client($this->_serverName);

        $e = null;
        $errors = array();
        try {
            $res = $client->call('request.create', array($this->_apiKey, $serviceUserId));
        } catch (Exception $e) {
            $code = $e->getCode();

            $errors = array(
                'error_code' => $code,
                'error_text' => iconv('utf-8', 'windows-1251', $e->getMessage()),
            );
        }
        return $res ? $res : $errors;
    }

    /**
     * Создание видео-обращения и начало записи
     *
     * @param integer $serviceUserId - id пользователя в  системе
     * @param (int|null) $requestId - идентификатор видео-обращения на стороне ВКС. Если null - то будет создано новое обращение.
     * @return int - 0/1 - включилась ли запись
     */
    function start_record($serviceUserId, $requestId) {
        $client = new Zend_XmlRpc_Client($this->_serverName);

        $e = null;
        $errors = array();
        try {
            $res = $client->call('request.start_record', array($this->_apiKey, $serviceUserId, $requestId));
        } catch (Exception $e) {
            $code = $e->getCode();

            $errors = array(
                'error_code' => $code,
                'error_text' => iconv('utf-8', 'windows-1251', $e->getMessage()),
            );
        }
        return $res ? $res : $errors;
    }

    /**
     * Остановка записи видео-обращения
     *
     * @param integer $serviceUserId - id пользователя в  системе
     * @param (int|null) $requestId - идентификатор видео-обращения на стороне ВКС. Если null - то будет создано новое обращение.
     * @return array(
     *            requestId (int) - идентификатор видео-обращения на стороне ВКС
     *            done (int) - 0/1 - включилась ли запись
     *            uri (string) - ссылка для показа флешки с проигрывателем для записи
     *         );
     */
    function stop_record($serviceUserId, $requestId) {
        $client = new Zend_XmlRpc_Client($this->_serverName);

        $e = null;
        $errors = array();
        try {
            $res = $client->call('request.stop_record', array($this->_apiKey, $serviceUserId, $requestId));
        } catch (Exception $e) {
            $code = $e->getCode();

            $errors = array(
                'error_code' => $code,
                'error_text' => iconv('utf-8', 'windows-1251', $e->getMessage()),
            );
        }
        return $res ? $res : $errors;
    }

    /**
     * Удаление обращения, вместе с записанными файлами
     *
     * @param integer $serviceUserId - id пользователя в  системе
     * @param integer $requestId - идентификатор видео-обращения на стороне ВКС.
     * @param bool $force - принудительное удаление записи, даже если она сейчас записывается или опубликована
     * @return (int) - были ли удалены записи
     */
    function delete_record($serviceUserId, $requestId, $force) {
        if (empty($force)) $force = true;

        $client = new Zend_XmlRpc_Client($this->_serverName);

        $e = null;
        $errors = array();
        try {
            $res = $client->call('request.remove', array($this->_apiKey, $serviceUserId, $requestId, $force));
        } catch (Exception $e) {
            $code = $e->getCode();

            $errors = array(
                'error_code' => $code,
                'error_text' => iconv('utf-8', 'windows-1251', $e->getMessage()),
            );
        }
        return $res ? $res : $errors;
    }

    /**
     * Установка флага «опубликовано». Для защиты записей.
     *
     * @param integer $serviceUserId - id пользователя в  системе
     * @param integer $requestId - идентификатор видео-обращения на стороне ВКС.

     * @return bool - была ли обновлена запись
     */
    function publish_record($serviceUserId, $requestId) {
        $client = new Zend_XmlRpc_Client($this->_serverName);

        $e = null;
        $errors = array();
        try {
            $res = $client->call('request.publish', array($this->_apiKey, $serviceUserId, $requestId));
        } catch (Exception $e) {
            $code = $e->getCode();

            $errors = array(
                'error_code' => $code,
                'error_text' => iconv('utf-8', 'windows-1251', $e->getMessage()),
            );
        }
        return $res ? $res : $errors;
    }

    /**
     * Возвращает URL авторизированного входа в конференцию для пользователей
     *
     * @param integer $conferenceId
     * @param array $serviceUserIds
     * @return array - была ли обновлена запись
     */
    function conferenceHashAccess($conferenceId, $serviceUserIds) {
        $client = new Zend_XmlRpc_Client($this->_serverName);

        $e = null;
        $errors = array();
        try {
            $res = $client->call('conference.getHashAccess', array($this->_apiKey, $conferenceId, $serviceUserIds));
        } catch (Exception $e) {
            $code = $e->getCode();

            $errors = array(
                'error_code' => $code,
                'error_text' => iconv('utf-8', 'windows-1251', $e->getMessage()),
            );
        }
        return $res ? $res : $errors;
    }

    /**
     * Возвращает информацию о конференции
     *
     * @param int $conferenceId - id конференции
     * @param boolean $fullInfo - Подробная информация, по умолчанию вернуться только ID конференций
     */
    function getConference($conferenceId, $fullInfo) {
        $fullInfo = !empty($fullInfo) ? $fullInfo : true;
        $client = new Zend_XmlRpc_Client($this->_serverName);

        $e = null;
        $errors = array();
        try {
            $res = $client->call('conference.get', array($this->_apiKey, $conferenceId, $fullInfo));
        } catch (Exception $e) {
            $code = $e->getCode();

            $errors = array(
                'error_code' => $code,
                'error_text' => iconv('utf-8', 'windows-1251', $e->getMessage()),
            );
        }
        return $res ? $res : $errors;
    }


    /**
     * изменить настройки существующей конференции
     *
     * @param integer $conferenceId - id конференции
     * @param array $params - массив параметров для конференции
     */
    function updateConference($conferenceId, $params) {
        $client = new Zend_XmlRpc_Client($this->_serverName);

        foreach ($params AS $k=>$v) {
            $params[$k] = stripslashes($v);
        }

        $data['title'] = iconv('windows-1251', 'utf-8', $params['title']);
        $data['public'] = isset($params['public']) ? (int)$params['public'] : (int)0;
        $data['description'] = isset($params['description']) ? iconv('windows-1251', 'utf-8', $params['description']) : '';
        $data['descriptionFull'] = isset($params['descriptionfull']) ? iconv('windows-1251', 'utf-8', $params['descriptionfull']) : '';
        $data['notifyBeforeTime'] = isset($params['notifybeforetime']) ? (int)$params['notifybeforetime'] : (int)0;
        if (isset($params['startdate'])) $data['startDate'] = $params['startdate'];
        if (isset($params['stoprecorddatetime'])) $data['stopRecordDatetime'] = $params['stoprecorddatetime'];
        if (isset ($params['imagebase64'])) {
            $data['imageBase64'] = base64_encode(file_get_contents($params['imagebase64']));
        }

        // параметры по умолчанию
        $data['autoRecord'] = (int)1;
        $data['autoRecordStop'] = (int)1;
        $data['notifyInvited'] = (int)1;
        $data['disableEdit'] = (int)0;
        $data['disableMemberList'] = (int)0;

        $e = null;
        $errors = array();
        $res = 0;
        try {
            $res = $client->call('conference.update', array($this->_apiKey, $conferenceId, $data));
        } catch (Exception $e) {
            $code = $e->getCode();

            $errors = array(
                'error_code' => $code,
                'error_text' => iconv('utf-8', 'windows-1251', $e->getMessage()),
            );
        }

        return $res ? $res : $errors;
    }


    /**
     * Получении ссылки iframe для просмотра
     *
     * @param int $conferenceId - id конференции
     * @param int $userId - id пользователя в системе ВКС
     */
    function getConferenceMovieLink ($conferenceId, $userId) {
        $client = new Zend_XmlRpc_Client($this->_serverName);

        $e = null;
        $errors = array();
        try {
            $res = $client->call('conference.getFileMovieLink', array($this->_apiKey, $conferenceId, $userId, 0));
        } catch (Exception $e) {
            $code = $e->getCode();

            $errors = array(
                'error_code' => $code,
                'error_text' => iconv('utf-8', 'windows-1251', $e->getMessage()),
            );
        }
        return $res ? $res : $errors;
    }


    /**
     * Возвращает список идущих конференций
     *
     * @param int $serviceUserId - id пользователя
     */
    function getOnline($serviceUserId) {
        $client = new Zend_XmlRpc_Client($this->_serverName);

        $e = null;
        $errors = array();
        try {
            $res = $client->call('conference.getOnline', array($this->_apiKey, $serviceUserId));
        } catch (Exception $e) {
            $code = $e->getCode();

            $errors = array(
                'error_code' => $code,
                'error_text' => iconv('utf-8', 'windows-1251', $e->getMessage()),
            );
        }
        return $res ? $res : $errors;
    }


    /**
     * Возвращает список конференций в архиве
     */
    function getArchive() {
        $client = new Zend_XmlRpc_Client($this->_serverName);

        $e = null;
        $errors = array();
        try {
            $res = $client->call('conference.getArchive', array($this->_apiKey));
        } catch (Exception $e) {
            $code = $e->getCode();

            $errors = array(
                'error_code' => $code,
                'error_text' => iconv('utf-8', 'windows-1251', $e->getMessage()),
            );
        }
        return $res ? $res : $errors;
    }


    /**
     * Получении ссылки iframe для просмотра
     */
    function getFileMovie($conferenceId, $userId, $fileIndex) {
        $userId = $userId ? $userId : 0;
        $fileIndex = $fileIndex ? $fileIndex : 0;
        $client = new Zend_XmlRpc_Client($this->_serverName);

        $e = null;
        $errors = array();
        try {
            $res = $client->call('conference.getFileMovieLink', array($this->_apiKey, $conferenceId, $userId, $fileIndex));
        } catch (Exception $e) {
            $code = $e->getCode();

            $errors = array(
                'error_code' => $code,
                'error_text' => iconv('utf-8', 'windows-1251', $e->getMessage()),
            );
        }
        return $res ? $res : $errors;
    }



}