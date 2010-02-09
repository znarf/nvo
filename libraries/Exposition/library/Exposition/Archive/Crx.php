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

//
// See details on http://code.google.com/chrome/extensions/getstarted.html
//
// A Ruby Implementation
// http://github.com/Constellation/crxmake crx
//
// Google Chome Source code:
// http://src.chromium.org/svn/trunk/src/base/crypto/signature_verifier_unittest.cc
// http://src.chromium.org/viewvc/chrome/trunk/src/chrome/browser/extensions/sandboxed_extension_unpacker.cc
//
// Current results: Signature verification initialization failed. This is most likely caused by a public key in the wrong format (should encode algorithm).

class Exposition_Archive_Crx extends Exposition_Archive_Zip
{
    /**
     * thx masover
     */
    const MAGIC = 'Cr24';

    /**
     * This is chromium extension version
     */
    const EXT_VERSION = 2;

    /**
     * This is private key lenght
     */
    const KEY_SIZE = 1024;

    /**
     * This is private key digest algorithm
     */
    const KEY_DIGEST_ALG = 'sha1';

    /**
     * Private Key ressource
     */
    private $_privateKey = null;

    /**
     * Private Key string
     */
    private $_privateKeyString = null;

    /**
     * Public Key details
     */
    private $_publicKeyDetails = array();

    /**
     * Archive Signature
     */
    private $_archiveSignature = null;

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
        //$this->setPrivateKey('/home/hthetiot/projects/netvibes.org/tmp/test/MySampleWidget.pem');

        // create original zip archive
        parent::_buildArchive();

        // get ssl signature
        $this->_signArchive();

        // get current archive data
        $archiveData = $this->_getArchiveData();

        // reset archive content
        $this->_resetArchiveData();

        // export public key to string with export format
        $publicKeyDer = $this->_getPublicKeyToDer();

        $this->_addArchiveData(self::MAGIC);
        $this->_addArchiveData(self::_sizePack(self::EXT_VERSION));
        $this->_addArchiveData(self::_sizePack(strlen($publicKeyDer)));
        $this->_addArchiveData(self::_sizePack(strlen($this->_archiveSignature)));
        $this->_addArchiveData($publicKeyDer);
        $this->_addArchiveData($this->_archiveSignature);
        $this->_addArchiveData($archiveData);

        // get current archive data
        $archiveData = $this->_getArchiveData();

        return true;
    }

    /**
     * Extract archive for current format
     */
    public function _extractArchive($outputDir)
    {
        throw new Exposition_Archive_Exception('No yet implemented');

        return parent::_extractArchive();
    }

    /**
     * Get current private key
     *
     * @return object OpenSSL private key ressource
     */
    public function getPrivateKey()
    {
        if (is_null($this->_privateKey)) {
            $this->generatePrivateKey();
        }

        return $this->_privateKey;
    }

    /**
     * Get OpenSSL PHP config
     *
     * @return array on OpenSSL config
     */
    static protected function _getCsrConfig()
    {
        return array(
            'config'            => '/etc/ssl/openssl.cnf',
            'digest_alg'        => self::KEY_DIGEST_ALG,
            'x509_extensions'   => 'v3_ca',
            'req_extensions'    => 'v3_req',
            'private_key_bits'  => self::KEY_SIZE,
            'private_key_type'  => OPENSSL_KEYTYPE_RSA,
            'encrypt_key'       => true,
        );
    }

    /**
     * Generate a new  private key and set has current private key
     *
     * @return object current archive instance
     */
    public function generatePrivateKey()
    {
        $csrConfig = self::_getCsrConfig();

        // Generate new key ressource
        $this->_privateKey = openssl_pkey_new($csrConfig);

        // Get private key has string
        openssl_pkey_export($this->_privateKey, $this->_privateKeyString);

        // Get public key details
        $this->_publicKeyDetails = openssl_pkey_get_details($this->_privateKey);

        return $this;
    }

    /**
     * Generate a new  private key and set has current private key
     *
     * @return object current archive instance
     */
    public function setPrivateKey($privateKeyFile)
    {
        if (!is_readable($privateKeyFile)) {
            throw new Exposition_Archive_Exception(sprintf('Unable to read Private key file on path <%s>', $privateKeyFile));
        }

        // read private key file
        $fp = fopen($privateKeyFile, 'r');
        $privateKey = fread($fp, 8192);
        fclose($fp);

        $csrConfig = self::_getCsrConfig();

        $this->_privateKey = openssl_get_privatekey($privateKey);

        // Get private key has string
        openssl_pkey_export($this->_privateKey, $this->_privateKeyString);

        // Get public key details
        $this->_publicKeyDetails = openssl_pkey_get_details($this->_privateKey);

        return $this;
    }

    /**
     * Get current public key
     *
     * @return array current public key details
     */
    public function getPublicKeyDetails()
    {
        if (is_null($this->_privateKey)) {
            $this->generatePrivateKey();
        }

        return $this->_publicKeyDetails;
    }

    /**
     * Retreive archive signature from current Private key
     *
     * @return string archive signature
     */
    public function _signArchive()
    {
        $data = $this->_getArchiveData();

        $publicKeyDetails = $this->getPublicKeyDetails();

        // free signature
        $this->_archiveSignature = null;

        // generate Signature
        openssl_sign($data, $this->_archiveSignature, $this->_privateKeyString, OPENSSL_ALGO_SHA1);

        // check signature
        $valid = openssl_verify($data, $this->_archiveSignature, $publicKeyDetails['key'], OPENSSL_ALGO_SHA1);

        if($valid !== 1) {
            throw new Exposition_Archive_Exception('Unable to sign archive');
        }

        return $this->_archiveSignature;
    }

    /**
     * Get public key to DER format
     *
     * @return string binary DER public key format
     */
    protected function _getPublicKeyToDer()
    {
        // get public key has DER format
        $publicKeyDetails = $this->getPublicKeyDetails();
        $pemData = $publicKeyDetails['key'];

        $matches = array();
        if (!preg_match('~^-----BEGIN ([A-Z ]+)-----\s*?([A-Za-z0-9+=/\r\n]+)\s*?-----END \1-----\s*$~D', $pemData, $matches)) {
            throw new Exposition_Archive_Exception('Invalid PEM format encountered when parsing public key');
        }

        $derData = str_replace(array("\r", "\n"), array('', ''), $matches[2]);
        $derData = base64_decode($derData, true);

	    return $derData;
    }

    /**
     * Encode size for CRX
     *
     * @return mixed
     */
    protected static function _sizePack($value)
    {
        return pack('L', $value);
    }

    public function __destruct()
    {
        if (is_resource($this->_privateKey)) {
            openssl_free_key($this->_privateKey);
        }
    }
}

