<?php
/**
 * Copyright (c) 2008 Netvibes (http://www.netvibes.org/).
 *
 * This file is part of Netvibes Widget Platform.
 *
 * Netvibes Widget Platform is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Netvibes Widget Platform is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Netvibes Widget Platform.  If not, see <http://www.gnu.org/licenses/>.
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
        $this->view->exceptions = $this->getResponse()->getException();
        
        foreach ($this->view->exceptions as $e) {
            echo '<li>';
            echo '<strong>', $e->getMessage(), '</strong> (', $e->getFile(), ') (line ', $e->getLine(), ')';
            echo '<pre>', $e->getTraceAsString(), '</pre>';
            echo '</li>'; 
        }
        
        $this->_helper->viewRenderer->setNoRender(true);
    }
}
