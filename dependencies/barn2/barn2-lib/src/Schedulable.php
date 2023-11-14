<?php

namespace Barn2\Plugin\Better_Recent_Comments\Dependencies\Lib;

/**
 * Something that can be scheduled.
 *
 * @package   Barn2\barn2-lib
 * @author    Barn2 Plugins <support@barn2.com>
 * @license   GPL-3.0
 * @copyright Barn2 Media Ltd
 * @version   1.0
 */
interface Schedulable
{
    public function schedule();
    public function unschedule();
}
