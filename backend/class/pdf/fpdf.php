<?php
namespace codename\core\pdf;

require CORE_VENDORDIR . 'setasign/fpdf/fpdf.php';

define('CHARACTER_EURO', CHR(128));

/**
 * Using FPDF for generating PDF files
 * @package core
 * @since 2016-02-04
 */
class fpdf extends \fpdf {

    /**
     * Holds the document title of this file
     * @var string $doctitle
     */
    protected $doctitle = null;
    
    /**
     * Where will the file be created?
     * @var string
     */
    protected $directory = '/tmp/';
    
    /**
     * Contains the filename that will be written to
     * @var string $filename
     */
    protected $filename = '';
    
    /**
     * Contains the temporary file name to avoid duplicate file names
     * @var string
     */
    protected $tempname = '';
    
    /**
     * Stores the document title for this PDF file
     * @param string $doctitle
     * @return void
     */
    public function setDoctitle(string $doctitle) {
        $this->doctitle = $doctitle;
        return;
    }
    
    /**
     * Returns the doctitle of the document
     * @return string
     */
    public function getDoctitle() : string {
        return $this->doctitle;
    }
    
    /**
     * Using the custom generate-method, the file will be written to the given path
     * @param string $filename
     * @return void
     */
    public function setFilename(string $filename) {
        $this->filename = $filename;
        return;
    }
    
    /**
     * Returns the file path where the PDF will be saved. 
     * @return string
     */
    public function getFilename() : string {
        return $this->filename;
    }
    
    /**
     * Sets this instance's directory
     * @param string $directory
     */
    public function setDirectory(string $directory) {
        $this->directory = $directory;
        return;
    }
    
    /**
     * Returns the directory of this instance
     * @return string
     */
    public function getDirectory() : string {
        return $this->directory;
    }
    
    /**
     * Sets the temporary name for the file
     * @param string $tempname
     */
    public function setTempname(string $tempname) {
        $this->tempname = $tempname;
        return;
    }
    
    /**
     * Returns the temporary file name for this instance
     * @return string
     */
    public function getTempname() : string {
        return $this->tempname;
    }
    
    /**
     * Returns the absolute file path of the generated PDF file
     * @return string
     */
    public function getAbsolutepath() : string {
        return $this->getDirectory() . $this->getTempname();
    }

    /**
     * overridden to make possible to show a different header on the first page
     * {@inheritDoc}
     * @see vendor_fpdf_fpdf::Header()
     */
    public function header() {
        if($this->PageNo() == 1) {
            $this->firstHeader();
            $this->ln(10);
            return;
        }
        $this->followingHeader();
        $this->ln(10);
        return;
    }

    /**
     * Will be used only on the first page
     * @return void
     */
    function firstHeader() {
        return;
    }
    
    /**
     * Will will be used on every page but page one
     * @return void
     */
    function followingHeader() {
        return;
    }
    
    /**
     * Actualls generates the PDF file to the temp directory
     * @return void
     */
    public function generate() {
        $tempname = $this->getFilename();
        $tempname = $tempname . md5(time());
        $this->setTempname($tempname);
        $this->output($this->getAbsolutepath(), 'F');
        return;
    }
    
    /**
     * Ensures that the given $string can be displayed in the PDF
     * @param $string
     * @return string
     */
    public function convertString($string = '') : string {
        return utf8_decode($string);
    }
    
}
