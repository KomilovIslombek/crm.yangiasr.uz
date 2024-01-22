<?php
/**
 * JohnCMS Content Management System (https://johncms.com)
 *
 * For copyright and license information, please see the LICENSE
 * Installing the system or redistributions of files must retain the above copyright notice.
 *
 * @link        https://johncms.com JohnCMS Project
 * @copyright   Copyright (C) JohnCMS Community
 * @license     GPL-3
 */

namespace Johncms;

class CleanUser
{
    /**
     * @var \PDO
     */
    private $db2;

    public function __construct()
    {
        $this->db = \App::getContainer()->get(\PDO::class);
    }
}
