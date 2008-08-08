<?php

require_once 'Compiler.php';
require_once 'Zend/Http/Client.php';

/**
 * Desktop Compiler.
 */
abstract class Compiler_Desktop extends Compiler
{
    /**
     * ZIP Archive.
     *
     * @var ZipArchive
     */
    protected $_zip = null;

    /**
     * Constructor.
     *
     * @param string  $url
     */
    public function __construct($parser)
    {
        parent::__construct($parser);
        $this->_zip = new ZipArchive();
    }

    /**
     * Retrieves the content of the ZIP archive.
     *
     * @return string
     */
    public function getFileContent()
    {
        // Temporary file path to build the archive
        $tmpFile = Zend_Registry::get('tmpDir') . 'compile' . time() . rand(1, 1000) . '.zip';

        // Archive creation
        if (!$this->_zip->open($tmpFile, ZIPARCHIVE::CREATE)) {
            throw new Exception('Error while creating the ZIP archive');
        }

        // Archive filling according to the selected compiler
        $this->buildArchive();

        // Archive closing
        if (!$this->_zip->close()) {
            throw new Exception('Error while writing the ZIP archive');
        }

        // Archive content retrieval and file deletion
        $content = file_get_contents($tmpFile);
        unlink($tmpFile);
        return $content;
    }

    /**
     * Adds a directory to the ZIP archive.
     *
     * @param string $directory The directory name to add
     * @param string $root The root destination within the archive
     */
    protected function addDirToZip($directory, $root = '')
    {
        if ($dir = opendir($directory)) {
            while (($file = readdir($dir)) !== false) {
                if (($file != '.') && ($file != '..') && ($file != '.svn')) {
                    $entry = $directory . '/' . $file;
                    if (is_dir($entry)) {
                        $this->addDirToZip($entry, $root . $file . '/');
                    } else if (!$this->addFileToZip($entry, $root . $file)) {
                        throw new Exception('Error while trying to compress a file.');
                    }
                }
            }
            closedir($dir);
        }
    }

    /**
     * Adds a file to the ZIP archive.
     *
     * @param  string  $path The path of the file to add
     * @param  string  $zipEntryName The ZIP archive name
     * @return boolean True if the file has been successfully added, otherwise false
     */
    protected function addFileToZip($path, $zipEntryName)
    {
        $result = false;
        $contents = @file_get_contents($path);
        if ($contents !== false) {
            $result = $this->_zip->addFromString($zipEntryName, $contents);
        }
        return $result;
    }

    /**
     * Adds a distant file to the ZIP archive.
     *
     * @param  string $url
     * @param  string $filePath
     * @param  string $dirPath
     * @return string The file path into the archive.
     */
    protected function addDistantFileToZip($url, $filePath = '', $dirPath = '')
    {
        $fileInfo = parse_url($url);
        if(!isset($fileInfo['host'])) {
            $urlInfo = parse_url($this->_url);
            $url = $urlInfo['scheme'] . '://' . $urlInfo['host'] . dirname( $urlInfo['path'] ) . '/' . $url;
        }
        if (empty($filePath) && empty($dirPath)) {
            $filePath = 'widget/' . basename($fileInfo['path']);
        } else if (!empty($dirPath)) {
            $filePath = $dirPath . basename($fileInfo['path']);
        }
        if ($this->addFileToZip($url, $filePath)) {
            return $filePath;
        } else {
            return '';
        }
    }

    /**
     * Renders the compiled widget with the correct mime type and file name.
     */
    public function render()
    {
        header('Pragma: public');
        header('Content-type: ' . $this->getFileMimeType());
        header('Content-Disposition: attachment; filename="' . $this->getFileName() . '"');
        echo $this->getFileContent();
    }

    /*** ABSTRACT FUNCTIONS ***/

    abstract protected function buildArchive();

    abstract public function getFileName();

    abstract public function getFileMimeType();
}
