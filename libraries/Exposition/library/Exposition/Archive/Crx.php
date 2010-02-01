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
// http://github.com/Constellation/crxmake crx scripts
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


class Exposition_Archive_Crx extends Exposition_Archive_Zip
{
    /**
     * thx masover
     */
    const MAGIC = 'Cr24';

    /**
     * Current version
     */
    const VERSION = '2.0.2';

    /**
     * This is chromium extension version
     */
    const EXT_VERSION = 2;

    /**
     * CERT_PUBLIC_KEY_INFO struct
     */
    const CERT_PUBLIC_KEY_INFO = '30 81 9F 30 0D 06 09 2A 86 48 86 F7 0D 01 01 01 05 00 03 81 8D 00';

    /**
     * This is key lenght
     */
    const KEY_SIZE = 1024;

    /**
     * This is key lenght
     */
    const KEY_DIGEST_ALG = 'RSA-SHA1';


    private $_privateKey = null;

    private $_privateKeyString = null;

    private $_publicKeyDetails = array();

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
        // create original zip archive
        parent::_buildArchive();

        // get ssl signature
        $this->_signArchive();

        // get current archive data
        $archiveData = $this->_getArchiveData();

        // reset archive content
        $this->_resetArchiveData();

        // export public key to string with export format
        $publicKeyToExportFormat = $this->_getPublicKeyToExportFormat();

        $this->_addArchiveData(self::MAGIC);
        $this->_addArchiveData(self::_sizePack(self::EXT_VERSION));
        $this->_addArchiveData(self::_sizePack(mb_strlen($publicKeyToExportFormat)));
        $this->_addArchiveData(self::_sizePack(mb_strlen($this->_archiveSignature)));
        $this->_addArchiveData($publicKeyToExportFormat);
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
        /// @todo
        return parent::_extractArchive();
    }

    /**
     * Get current private key
     *
     * @return object open_ssl ressource
     */
    public function getPrivateKey()
    {
        if (is_null($this->_privateKey)) {
            $this->generatePrivateKey();
        }

        return $this->_privateKey;
    }

    /**
     * Generate a new  private key and set has current private key
     *
     * @return object current archive instance
     */
    public function generatePrivateKey()
    {
        $csrConfig = array(
            'digest_alg'        => self::KEY_DIGEST_ALG,
            'private_key_bits'  => self::KEY_SIZE,
        );

        // Generate new key
        $this->_privateKey = openssl_pkey_new($csrConfig);

        // Get private key has string
        openssl_pkey_export($this->_privateKey, $this->_privateKeyString);

        // Get public key details has array
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
            throw new Exposition_Archive_Exception(sprintf('Unable to read Private key file on path <%>', $privateKeyFile));
        }

        // read private key file
        $fp = fopen($privateKeyFile, 'r');
        $privateKey = fread($fp, 8192);
        fclose($fp);

        $this->_privateKey = openssl_get_privatekey($privateKey);

        // Get private key has string
        openssl_pkey_export($this->_privateKey, $this->_privateKeyString);

        // Get public key details has array
        $this->_publicKeyDetails = openssl_pkey_get_details($this->_privateKey);

        return $this;
    }

    /**
     * Get current public key
     *
     * @return string  current public key
     */
    public function getPublicKeyDetails()
    {
        if (is_null($this->_privateKey)) {
            $this->generatePrivateKey();
        }

        return $this->_publicKeyDetails;
    }

    /**
     * Get public key info for CRX
     *
     * @return string CERT_PUBLIC_KEY_INFO struct concatenate with public key into DER format
     */
    protected function _getPublicKeyToExportFormat()
    {
        // get public key structure
        $publicKeyStruct = explode(' ', self::CERT_PUBLIC_KEY_INFO);
        foreach ($publicKeyStruct as $structIndex => $structValue) {
            $publicKeyStruct[$structIndex] = pack('C*', hexdec($structValue));
        }

        $publicKeyStruct = implode('', $publicKeyStruct);

        // get public key has DER format
        $publicKeyDetails = $this->getPublicKeyDetails();
        $publicKeyDer = self::_convertPemToDer($publicKeyDetails['key']);

        return  $publicKeyStruct . $publicKeyDer;
    }

    /**
     * Retreive archive signature from current Private key
     */
    public function _signArchive()
    {
        $data = $this->_getArchiveData();

        $publicKeyDetails = $this->getPublicKeyDetails();

        // free signature
        $this->_archiveSignature = null;

        // generate
        openssl_sign($data, $archiveSignature, $this->_privateKeyString, OPENSSL_ALGO_SHA1);

        // check signature
        $valid = openssl_verify($data, $archiveSignature, $publicKeyDetails['key'], OPENSSL_ALGO_SHA1);

        if($valid !== 1) {
            throw new Exposition_Archive_Exception('Unable to sign archive');
        }

        // save signature
        $this->_archiveSignature = $archiveSignature;

        return $this->_archiveSignature;
    }

    //
    // Tools
    //

    /**
     * Convert PEM key to DER format
     *
     * @return string binary
     */
    protected static function _convertPemToDer($pemData) {

       $begin = 'KEY-----';
       $end   = '-----END';

       $pemData = mb_substr($pemData, mb_strpos($pemData, $begin) + mb_strlen($begin));
       $pemData = mb_substr($pemData, 0, mb_strpos($pemData, $end));
       $derData = base64_decode($pemData);

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
        if (!is_null($this->_privateKey)) {
            openssl_free_key($this->_privateKey);
        }
    }
}

