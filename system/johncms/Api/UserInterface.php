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

namespace Johncms\Api;

/**
 * Interface UserInterface
 *
 * @package Johncms\Api
 *
 * @property $id
 * @property $name
 * @property $name_lat
 * @property $password
 * @property $rights
 * @property $failed_login
 * @property $imname
 * @property $sex
 * @property $postforum
 * @property $postguest
 * @property $yearofbirth
 * @property $lastdate
 * @property $mail
 * @property $icq
 * @property $skype
 * @property $jabber
 * @property $www
 * @property $about
 * @property $live
 * @property $mibile
 * @property $status
 * @property $ip
 * @property $ip_via_proxy
 * @property $browser
 * @property $sestime
 * @property $total_on_site
 * @property $movings
 * @property $place
 * @property $set_user
 * @property $set_forum
 * @property $set_mail
 * @property $comm_count
 * @property $comm_old
 * @property $smileys
 * @property $ban
 */
interface UserInterface
{
    public function isValid();

    public function getConfig();
}
