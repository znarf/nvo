<?php
/**
 * Copyright Netvibes 2006-2009.
 * This file is part of Exposition PHP Lib.
 *
 * Exposition PHP Lib is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Exposition PHP Lib is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Exposition PHP Lib.  If not, see <http://www.gnu.org/licenses/>.
 */

// se details on http://code.google.com/chrome/extensions/getstarted.html
// http://gist.github.com/142422 crx scripts

class Exposition_Archive_Crx extends Exposition_Archive_Zip
{
    private $_privateKey = null;

    private $_privateKeyString = null;

    private $_publicKey = null;

    /**
     * thx masover
     */
    //@@magic = 'Cr24'

    /**
     * Mime Type.
     *
     * @var string
     */
    protected static $_mimeType = 'application/chrome';

    /**
     * Build archive for current format
     */
    protected function _buildArchive()
    {
        // crx output dir: "/Users/hthetiot/Widget.crx"
        // ext dir: "/Users/hthetiot/Desktop/MySampleWidget"
        // generate pemkey to  "/Users/hthetiot/MySampleWidget.pem"
        // create zip
        // include file: "/Users/hthetiot/Desktop/MySampleWidget/widget.html"
        // include file: "/Users/hthetiot/Desktop/MySampleWidget/manifest.json"
        // include file: "/Users/hthetiot/Desktop/MySampleWidget/Icon.png"
        // create zip...done
        // zip file at "/Users/hthetiot/extension.zip"
        // sign zip
        // write crx...done at "/Users/hthetiot/Widget.crx"


        $this->generatePrivateKey();

        $this->addFileFromString('key.pem', $this->_privateKeyString):

        return parent::_buildArchive();
    }

    public function generatePrivateKey()
    {
        // Create the keypair
        $this->_privateKey = openssl_pkey_new();

        // Get private key
        openssl_pkey_export($res, $this->_privateKeyString);

        // Get public key
        $this->_publicKey = openssl_pkey_get_details($this->_privateKey);
    }

    public function

    /**
     * Extract archive for current format
     */
    public function _extractArchive($outputDir)
    {
        return parent::_extractArchive();
    }
}

