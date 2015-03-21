<?php
/*
 * This file is part of the Brain Monkey package.
 *
 * (c) Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brain\Monkey\WP;

use Mockery;
use InvalidArgumentException;
use LogicException;

/**
 * @author  Giuseppe Mazzapica <giuseppe.mazzapica@gmail.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @package BrainMonkey
 */
class Actions extends Hooks
{
    /**
     * Retrieves an Mockery object that allows to set expectations on specific hook fired, and even
     * run a specific callback as response of hook firing.
     *
     * @param  string                             $action Action name, e.g. 'init'
     * @return \Brain\Monkey\WP\ActionExpectation
     */
    public static function expectFired($action)
    {
        $type = self::ACTION;
        $mock = Mockery::mock("do_{$action}");
        $expectation = $mock->shouldReceive("do_{$type}_{$action}");
        parent::instance($type)->mocks[$action]['run'] = $mock;

        return new ActionExpectation($expectation, true);
    }

    /**
     * Retrieves an Mockery object that allows to set expectations on specific hook added.
     *
     * @param  string                             $action Action name, e.g. 'init'
     * @return \Brain\Monkey\WP\ActionExpectation
     */
    public static function expectAdded($action)
    {
        $type = self::ACTION;
        $mock = Mockery::mock("add_{$action}");
        $expectation = $mock->shouldReceive("add_{$type}_{$action}");
        parent::instance($type)->mocks[$action]['add'] = $mock;

        return new ActionExpectation($expectation, false);
    }

    /**
     * Adds an action hook.
     *
     * @return bool Always true, because so do WordPress.
     */
    public function add()
    {
        $args = func_get_args();
        array_unshift($args, self::ACTION);

        return call_user_func_array([$this, 'addHook'], $args);
    }

    /**
     * Removes an action hook.
     *
     * @return bool True when the hook exists and is been removed.
     */
    public function remove()
    {
        $args = func_get_args();
        array_unshift($args, self::ACTION);

        return call_user_func_array([$this, 'removeHook'], $args);
    }

    /**
     * Fires an action.
     */
    public function run()
    {
        $args = func_get_args();
        array_unshift($args, self::ACTION);
        call_user_func_array([$this, 'runHook'], $args);
    }

    /**
     * Fires an action using an  array for arguments.
     */
    public function runRef()
    {
        if (func_num_args() < 2 || ! is_array(func_get_arg(1))) {
            throw new LogicException('do_action_ref_array() needs an array as second argument.');
        }
        $args = func_get_arg(1);
        array_unshift($args, func_get_arg(0));

        call_user_func_array([$this, 'run'], $args);
    }

    /**
     * Checks if an action has been added.
     *
     * @return bool
     */
    public function has()
    {
        $args = func_get_args();
        array_unshift($args, self::ACTION);

        return call_user_func_array([$this, 'hasHook'], $args);
    }

    /**
     * Checks if a specific action has been triggered,
     *
     * @param  string $action
     * @return bool
     */
    public function did($action)
    {
        if (empty($action) || ! is_string($action)) {
            throw new InvalidArgumentException("Action name must be in a string.");
        }

        return in_array($action, $this->done, true) || $action === self::current();
    }

    /**
     * Cleanup.
     */
    public function clean()
    {
        $this->cleanInstance($this);
    }
}
