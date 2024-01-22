<?php
/*
 * JohnCMS NEXT Mobile Content Management System (http://johncms.com)
 *
 * For copyright and license information, please see the LICENSE.md
 * Installing the system or redistributions of files must retain the above copyright notice.
 *
 * @link        http://johncms.com JohnCMS Project
 * @copyright   Copyright (C) JohnCMS Community
 * @license     GPL-3
 */

namespace Johncms;

use Psr\Container\ContainerInterface;

class Tools implements Api\ToolsInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var \PDO
     */
    private $db2;

    /**
     * @var Api\UserInterface::class
     */
    private $user;

    /**
     * @var UserConfig
     */
    private $userConfig;

    /**
     * @var Api\ConfigInterface
     */
    private $config;

    public function __invoke(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(Api\ConfigInterface::class);
        $this->db = $container->get(\PDO::class);
        $this->user = $container->get(Api\UserInterface::class);
        $this->userConfig = $this->user->getConfig();

        return $this;
    }

    /**
     * Сообщения об ошибках
     *
     * @param string|array $error Сообщение об ошибке (или массив с сообщениями)
     * @param string       $link  Необязательная ссылка перехода
     * @return string
     */
    public function displayError($error = '', $link = '')
    {
        return '<div class="rmenu"><p><b>' . _t('ERROR', 'system') . '!</b><br>'
            . (is_array($error) ? implode('<br>', $error) : $error) . '</p>'
            . (!empty($link) ? '<p>' . $link . '</p>' : '') . '</div>';
    }

    /**
     * Получаем данные пользователя
     *
     * @param int $id Идентификатор пользователя
     * @return array|bool
     */
    public function getUser($id = 0)
    {
        if ($id && $id != $this->user->id) {
            $req = $this->db->query("SELECT * FROM `users` WHERE `id` = '$id'");

            if ($req->rowCount()) {
                return $req->fetch();
            } else {
                return false;
            }
        } else {
            return $this->user;
        }
    }
}
