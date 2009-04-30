<?php
/**
 * Copyright Netvibes 2006-2009.
 * This file is part of Exposition PHP Server.
 * 
 * Exposition PHP Server is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * Exposition PHP Server is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with Exposition PHP Server. If not, see <http://www.gnu.org/licenses/>.
 */


require_once 'Zend/Controller/Action.php';

/**
 * Error controller
 */
class ErrorController extends Zend_Controller_Action
{
    /**
     * Error action.
     */
    public function errorAction()
    {
        $this->view->host = $_SERVER['HTTP_HOST'];

        $this->view->details = '';

        foreach ($this->getResponse()->getException() as $e) {

            $log_message = 'Error ' . $e->getCode() . ': ' . $e->getMessage() .
                ' (file ' .  $e->getFile() . ') (line ' . $e->getLine() . ')';

            if (DEBUG) {
                $this->view->details .= '<li>' . $log_message . '<pre>' . $e->getTraceAsString() . '</pre>' . '</li>';
            } else {
                error_log($log_message);
            }

            switch ($e->getCode()) {
                case '404':
                    $this->view->status = 'Not found';
                    $this->view->message = 'The requested document was not found on this server.';
                    break;
                default:
                    $this->view->status = 'An error occured';
                    $this->view->message = 'You can contact the server administrator about this problem.';
            }
        }
    }
}
