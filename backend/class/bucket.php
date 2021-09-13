<?php
namespace codename\core;

/**
 * abstract bucket class
 * @package core
 * @since 2016-04-21
 */
abstract class bucket implements \codename\core\bucket\bucketInterface {

    /**
     * The given config cannot be validated agains structure_config_bucket_local.
     * <br />See the validator for more info
     * @var string
     */
    const EXCEPTION_CONSTRUCT_CONFIGURATIONINVALID = 'EXCEPTION_CONSTRUCT_CONFIGURATIONINVALID';

    /**
     * Contains the base directory where all files in this bucket will be stored
     * @var string $basedir
     */
    protected $basedir = null;

    /**
     * Contains an instance of \codename\core\errorstack
     * @var \codename\core\errorstack
     */
    protected $errorstack = null;

    /**
     * Creates the instance, establishes the connection and authenticates
     * @param array $data
     * @return \codename\core\bucket
     */
    public function __construct(array $data) {
        $this->errorstack = new \codename\core\errorstack('BUCKET');

        return $this;
    }

    /**
     * This method normaliuzes the remote path by trying
     * <br />to prepend the basepath if it is not prepended yet.
     * @param string $path
     * @return string
     */
    public function normalizePath(string $path) : string {
        if(substr($path, 0, strlen($this->basedir)) == $this->basedir) {
            return $path;
        }
        return $this->basedir . $path;
    }

    /**
     * Normalizes a given path. By default, $strict is being used
     * which denies usage of any . or .. and throws an exception, if found.
     * @param  string $path
     * @param  bool   $strict [default: true]
     * @return string
     */
    protected function normalizeRelativePath(string $path, bool $strict = true): string {
      //
      // Make sure we also handle Windows-style backslash paths
      // Though bucket convention is slashes only
      //
      $path = str_replace('\\', '/', $path);
      $parts = [];
      foreach (explode('/', $path) as $part) {
        switch ($part) {
          case '.':
            //
            // NOTE: we might make this thing more tolerant
            // '.' might be just discarded w/o throwing an exception
            // This just enforces a strict programming style and data handling.
            //
            if($strict) {
              throw new exception(static::BUCKET_EXCEPTION_BAD_PATH, exception::$ERRORLEVEL_FATAL);
            }
          case '': // initial/starting slash or //
            break;

          case '..':
            if($strict) {
              throw new exception(static::BUCKET_EXCEPTION_BAD_PATH, exception::$ERRORLEVEL_FATAL);
            }
            if (empty($parts)) {
              throw new exception(static::BUCKET_EXCEPTION_FORBIDDEN_PATH_TRAVERSAL, exception::$ERRORLEVEL_FATAL);
            }
            array_pop($parts);
            break;

          default:
            $parts[] = $part;
            break;
        }
      }
      return implode('/', $parts);
    }

    /**
     * Exception thrown if a bad path is passed as path parameter somewhere
     * @var string
     */
    const BUCKET_EXCEPTION_BAD_PATH = 'BUCKET_EXCEPTION_BAD_PATH';

    /**
     * Exception thrown if there was a (possibly malicious) path traversal
     * in a given path parameter
     * @var string
     */
    const BUCKET_EXCEPTION_FORBIDDEN_PATH_TRAVERSAL = 'BUCKET_EXCEPTION_FORBIDDEN_PATH_TRAVERSAL';

    /**
     * Returns the errorstack of the bucket
     * @return \codename\core\errorstack
     */
    public function getErrorstack() : \codename\core\errorstack {
        return $this->errorstack;
    }

    /**
     *
     * {@inheritDoc}
     * @see \codename\core\bucket\bucketInterface::downloadToClient()
     * @todo errorhandling
     */
    public function downloadToClient(\codename\core\value\text\filerelative $remotefile, \codename\core\value\text\filename $filename, array $option = array()) {
        if(!$this->fileAvailable($remotefile->get())) {
            // app::writeActivity('BUCKET_FILE_DOWNLOAD_FAIL', $remotefile->get());
            throw new exception('BUCKET_FILE_DOWNLOAD_UNAVAILABLE', exception::$ERRORLEVEL_ERROR, $remotefile->get());
        }

        $tempfile = '/tmp/' . md5($remotefile->get() . microtime() . $filename->get());

        // evaluate return value from ::filePull()
        if(!$this->filePull($remotefile->get(), $tempfile)) {
          throw new exception('BUCKET_FILE_DOWNLOAD_FAIL', exception::$ERRORLEVEL_ERROR, $remotefile->get());
        }

        app::writeActivity('BUCKET_FILE_DOWNLOAD', $remotefile->get());

        if(array_key_exists('inline', $option) === TRUE && $option['inline'] === TRUE) {

          // Determine Mime Type by extension. I know it's bad.
          $path_parts = pathinfo($remotefile->get());
          $ext = strtolower($path_parts["extension"]);

          // Determine Content Type (only for inlining)
          switch ($ext) {
              case "pdf": $ctype="application/pdf"; break;
              case "gif": $ctype="image/gif"; break;
              case "png": $ctype="image/png"; break;
              case "jpeg":
              case "jpg": $ctype="image/jpg"; break;
              default: $ctype="application/force-download";
          }

          app::getResponse()->setHeader('Content-Type: ' . $ctype);
          app::getResponse()->setHeader("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
          app::getResponse()->setHeader("Cache-Control: post-check=0, pre-check=0", false);
          app::getResponse()->setHeader("Pragma: no-cache");
          app::getResponse()->setHeader('Content-Disposition: inline; filename="' . $filename->get() . '"');
          app::getResponse()->setHeader('Content-Length: ' . filesize($tempfile));
          app::getResponse()->setHeader('Content-Transfer-Encoding: binary');

        } else {

          app::getResponse()->setHeader('Content-Description: File Transfer');
          app::getResponse()->setHeader('Content-Type: application/octet-stream');
          app::getResponse()->setHeader('Content-Transfer-Encoding: binary');
          app::getResponse()->setHeader('Pragma: public');
          app::getResponse()->setHeader('Content-Length: ' . filesize($tempfile));
          app::getResponse()->setHeader('Content-Disposition: attachment; filename="' . $filename->get() . '"');
        }
        if (ob_get_contents()) ob_clean();
        flush();
        readfile($tempfile);
        unlink($tempfile); // delete the tempfile afterwards
        exit;
    }

    /**
     * Normalizes a file name by it's given $filename
     * @param string $filename
     * @todo CENTRAL METHOD STORAGE
     * @todo one day move to factory class structure
     * @return \codename\core\value\text\filename
     */
    final public static function factoryFilename(string $filename) : \codename\core\value\text\filename {
        $text = $filename;
        $text = preg_replace("/[∂άαáàâãªä]/u",      "a", $text);
        $text = preg_replace("/[∆лДΛдАÁÀÂÃÄ]/u",     "A", $text);
        $text = preg_replace("/[ЂЪЬБъь]/u",           "b", $text);
        $text = preg_replace("/[βвВ]/u",            "B", $text);
        $text = preg_replace("/[çς©с]/u",            "c", $text);
        $text = preg_replace("/[ÇС]/u",              "C", $text);
        $text = preg_replace("/[δ]/u",             "d", $text);
        $text = preg_replace("/[éèêëέëèεе℮ёєэЭ]/u", "e", $text);
        $text = preg_replace("/[ÉÈÊË€ξЄ€Е∑]/u",     "E", $text);
        $text = preg_replace("/[₣]/u",               "F", $text);
        $text = preg_replace("/[НнЊњ]/u",           "H", $text);
        $text = preg_replace("/[ђћЋ]/u",            "h", $text);
        $text = preg_replace("/[ÍÌÎÏ]/u",           "I", $text);
        $text = preg_replace("/[íìîïιίϊі]/u",       "i", $text);
        $text = preg_replace("/[Јј]/u",             "j", $text);
        $text = preg_replace("/[ΚЌК]/u",            'K', $text);
        $text = preg_replace("/[ќк]/u",             'k', $text);
        $text = preg_replace("/[ℓ∟]/u",             'l', $text);
        $text = preg_replace("/[Мм]/u",             "M", $text);
        $text = preg_replace("/[ñηήηπⁿ]/u",            "n", $text);
        $text = preg_replace("/[Ñ∏пПИЙийΝЛ]/u",       "N", $text);
        $text = preg_replace("/[óòôõºöοФσόо]/u", "o", $text);
        $text = preg_replace("/[ÓÒÔÕÖθΩθОΩ]/u",     "O", $text);
        $text = preg_replace("/[ρφрРф]/u",          "p", $text);
        $text = preg_replace("/[®яЯ]/u",              "R", $text);
        $text = preg_replace("/[ГЃгѓ]/u",              "r", $text);
        $text = preg_replace("/[Ѕ]/u",              "S", $text);
        $text = preg_replace("/[ѕ]/u",              "s", $text);
        $text = preg_replace("/[Тт]/u",              "T", $text);
        $text = preg_replace("/[τ†‡]/u",              "t", $text);
        $text = preg_replace("/[úùûüџμΰµυϋύ]/u",     "u", $text);
        $text = preg_replace("/[√]/u",               "v", $text);
        $text = preg_replace("/[ÚÙÛÜЏЦц]/u",         "U", $text);
        $text = preg_replace("/[Ψψωώẅẃẁщш]/u",      "w", $text);
        $text = preg_replace("/[ẀẄẂШЩ]/u",          "W", $text);
        $text = preg_replace("/[ΧχЖХж]/u",          "x", $text);
        $text = preg_replace("/[ỲΫ¥]/u",           "Y", $text);
        $text = preg_replace("/[ỳγўЎУуч]/u",       "y", $text);
        $text = preg_replace("/[ζ]/u",              "Z", $text);

        $text = preg_replace("/[‚‚]/u", ",", $text);
        $text = preg_replace("/[`‛′’‘]/u", "'", $text);
        $text = preg_replace("/[″“”«»„]/u", '"', $text);
        $text = preg_replace("/[—–―−–‾⌐─↔→←]/u", '-', $text);
        $text = preg_replace("/[  ]/u", ' ', $text);

        $text = str_replace("…", "...", $text);
        $text = str_replace("≠", "!=", $text);
        $text = str_replace("≤", "<=", $text);
        $text = str_replace("≥", ">=", $text);
        $text = preg_replace("/[‗≈≡]/u", "=", $text);
        $text = str_replace("ыЫ", "bl", $text);
        $text = str_replace("℅", "c/o", $text);
        $text = str_replace("₧", "Pts", $text);
        $text = str_replace("™", "tm", $text);
        $text = str_replace("№", "No", $text);
        $text = str_replace("Ч", "4", $text);
        $text = str_replace("‰", "%", $text);
        $text = preg_replace("/[∙•]/u", "*", $text);
        $text = str_replace("‹", "<", $text);
        $text = str_replace("›", ">", $text);
        $text = str_replace("‼", "!!", $text);
        $text = str_replace("⁄", "/", $text);
        $text = str_replace("∕", "/", $text);
        $text = str_replace("⅞", "7/8", $text);
        $text = str_replace("⅝", "5/8", $text);
        $text = str_replace("⅜", "3/8", $text);
        $text = str_replace("⅛", "1/8", $text);
        $text = preg_replace("/[‰]/u", "%", $text);
        $text = preg_replace("/[Љљ]/u", "Ab", $text);
        $text = preg_replace("/[Юю]/u", "IO", $text);
        $text = preg_replace("/[ﬁﬂ]/u", "fi", $text);
        $text = preg_replace("/[зЗ]/u", "3", $text);
        $text = str_replace("£", "(pounds)", $text);
        $text = str_replace("₤", "(lira)", $text);
        $text = preg_replace("/[‰]/u", "%", $text);
        $text = preg_replace("/[↨↕↓↑│]/u", "|", $text);
        $text = preg_replace("/[∞∩∫⌂⌠⌡]/u", "", $text);
        $text = str_replace(',', '', $text);
        $text = str_replace('_', '', $text);
        $text = str_replace('/', '', $text);
        $text = str_replace('\\/', '', $text);
        $text = str_replace(' ', '', $text);
        return new \codename\core\value\text\filename($text);
    }

}
