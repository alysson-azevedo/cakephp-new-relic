<?php
/**
 * CakePHP New Relic plugin.
 *
 * @author https://github.com/brunitto
 * @author https://github.com/voycey
 * @link https://github.com/brunitto/cakephp-new-relic
 */
namespace NewRelic\Routing\Filter;

use Cake\Event\Event;
use Cake\Routing\DispatcherFilter;
use Cake\Network\Request;
use Cake\Utility\Inflector;

/**
 * New Relic name transaction dispatcher filter.
 *
 * @uses DispatcherFilter
 */
class NameTransactionFilter extends DispatcherFilter
{
    /**
     * beforeDispatch() method.
     *
     * Call the newrelic_name_transaction function when the newrelic extension
     * is loaded.
     *
     * @param Cake\Event\Event $event The event.
     * @return void
     */
    public function beforeDispatch(Event $event)
    {
        if (extension_loaded('newrelic')) {
            $request = $event->data['request'];
            newrelic_name_transaction($this->nameTransaction($request));
        }
    }

    /**
     * nameTransaction() method.
     *
     * Name the transaction using request data.
     *
     * @param Cake\Network\Network $request The request.
     * @return string
     */
    public function nameTransaction(Request $request)
    {
        // CakePHP does not include the "prefix" key in request "params" array
        // when prefixes are not being used as it does for the "plugin" key, so
        // this code need custom handling
        if (array_key_exists('prefix', $request->params)) {
            $prefix = $request->params['prefix'];
        } else {
            $prefix = null;
        }

        $plugin = $request->params['plugin'];
        $controller = $request->params['controller'];
        $action = $request->params['action'];

        $transaction = Inflector::dasherize($controller) . '/' . $action;

        if ($prefix !== null) {
            $transaction = Inflector::dasherize($prefix) . '/' . $transaction;
        }

        if ($plugin !== null) {
            $transaction = Inflector::dasherize($plugin) . '/' . $transaction;
        }

        return $transaction;
    }
}
