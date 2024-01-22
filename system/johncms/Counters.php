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

class Counters
{
    /**
     * @var \PDO
     */
    private $db2;

    /**
     * @var Api\UserInterface::class
     */
    private $systemUser;

    /**
     * @var \Johncms\Tools
     */
    private $tools;

    private $homeurl;

    public function __invoke(ContainerInterface $container)
    {

        $this->db = $container->get(\PDO::class);
        $this->systemUser = $container->get(Api\UserInterface::class);
        $this->tools = $container->get(Api\ToolsInterface::class);
        $this->homeurl = $container->get('config')['johncms']['homeurl'];

        return $this;
    }
}
