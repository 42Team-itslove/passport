<?php
/**
 * Securimage CAPTCHA Class.
 *
 * A class for creating and validating secure CAPTCHA images and audio.
 *
 * The class contains many options regarding appearance, security, storage of
 * captcha data and image/audio generation options.
 *
 * @version    3.5.2
 * @package    Securimage
 * @subpackage classes
 * @author     Drew Phillips <drew@drew-phillips.com>
 *
 */
class Securimage
{
    // All of the public variables below are securimage options
    // They can be passed as an array to the Securimage constructor, set below,
    // or set from securimage_show.php and securimage_play.php

    /**
     * Constant for rendering captcha as a JPEG image
     * @var int
     */
    const SI_IMAGE_JPEG = 1;

    /**
     * Constant for rendering captcha as a PNG image (default)
     * @var int
     */

    const SI_IMAGE_PNG  = 2;
    /**
     * Constant for rendering captcha as a GIF image
     * @var int
     */
    const SI_IMAGE_GIF  = 3;

    /**
     * Constant for generating a normal alphanumeric captcha based on the
     * character set
     *
     * @see Securimage::$charset charset property
     * @var int
     */
    const SI_CAPTCHA_STRING     = 0;

    /**
     * Constant for generating a captcha consisting of a simple math problem
     *
     * @var int
     */
    const SI_CAPTCHA_MATHEMATIC = 1;

    /**
     * Constant for generating a word based captcha using 2 words from a list
     *
     * @var int
     */
    const SI_CAPTCHA_WORDS      = 2;

    /*%*********************************************************************%*/
    // Properties

    /**
     * The width of the captcha image
     * @var int
     */
    public $image_width = 215;

    /**
     * The height of the captcha image
     * @var int
     */
    public $image_height = 80;

    /**
     * Font size is calculated by image height and this ratio.  Leave blank for
     * default ratio of 0.4.
     *
     * Valid range: 0.1 - 0.99.
     *
     * Depending on image_width, values > 0.6 are probably too large and
     * values < 0.3 are too small.
     *
     * @var float
     */
    public $font_ratio;

    /**
     * The type of the image, default = png
     *
     * @see Securimage::SI_IMAGE_PNG SI_IMAGE_PNG
     * @see Securimage::SI_IMAGE_JPEG SI_IMAGE_JPEG
     * @see Securimage::SI_IMAGE_GIF SI_IMAGE_GIF
     * @var int
     */
    public $image_type   = self::SI_IMAGE_PNG;

    /**
     * The background color of the captcha
     * @var Securimage_Color
     */
    public $image_bg_color = '#ffffff';

    /**
     * The color of the captcha text
     * @var Securimage_Color
     */
    public $text_color     = '#707070';

    /**
     * The color of the lines over the captcha
     * @var Securimage_Color
     */
    public $line_color     = '#707070';

    /**
     * The color of the noise that is drawn
     * @var Securimage_Color
     */
    public $noise_color    = '#707070';

    /**
     * How transparent to make the text.
     *
     * 0 = completely opaque, 100 = invisible
     *
     * @var int
     */
    public $text_transparency_percentage = 20;

    /**
     * Whether or not to draw the text transparently.
     *
     * true = use transparency, false = no transparency
     *
     * @var bool
     */
    public $use_transparent_text         = true;

    /**
     * The length of the captcha code
     * @var int
     */
    public $code_length    = 6;

    /**
     * Whether the captcha should be case sensitive or not.
     *
     * Not recommended, use only for maximum protection.
     *
     * @var bool
     */
    public $case_sensitive = false;

    /**
     * The character set to use for generating the captcha code
     * @var string
     */
    public $charset        = 'ABCDEFGHKLMNPRSTUVWYZabcdefghklmnprstuvwyz23456789';

    /**
     * How long in seconds a captcha remains valid, after this time it will be
     * considered incorrect.
     *
     * @var int
     */
    public $expiry_time    = 900;

    /**
     * The session name securimage should use.
     *
     * Only use if your application uses a custom session name (e.g. Joomla).
     * It is recommended to set this value here so it is used by all securimage
     * scripts (i.e. securimage_show.php)
     *
     * @var string
     */
    public $session_name   = null;

    /**
     * true to use the wordlist file, false to generate random captcha codes
     * @var bool
     */
    public $use_wordlist   = false;

    /**
     * The level of distortion.
     *
     * 0.75 = normal, 1.0 = very high distortion
     *
     * @var double
     */
    public $perturbation = 0.85;

    /**
     * How many lines to draw over the captcha code to increase security
     * @var int
     */
    public $num_lines    = 5;

    /**
     * The level of noise (random dots) to place on the image, 0-10
     * @var int
     */
    public $noise_level  = 2;

    /**
     * The signature text to draw on the bottom corner of the image
     * @var string
     */
    public $image_signature = '';

    /**
     * The color of the signature text
     * @var Securimage_Color
     */
    public $signature_color = '#707070';

    /**
     * The path to the ttf font file to use for the signature text.
     * Defaults to $ttf_file (AHGBold.ttf)
     *
     * @see Securimage::$ttf_file
     * @var string
     */
    public $signature_font;

    /**
     * The type of captcha to create.
     *
     * Either alphanumeric based on *charset*, a simple math problem, or an
     * image consisting of 2 words from the word list.
     *
     * @see Securimage::SI_CAPTCHA_STRING SI_CAPTCHA_STRING
     * @see Securimage::SI_CAPTCHA_MATHEMATIC SI_CAPTCHA_MATHEMATIC
     * @see Securimage::SI_CAPTCHA_WORDS SI_CAPTCHA_WORDS
     * @see Securimage::$charset charset property
     * @see Securimage::$wordlist_file wordlist_file property
     * @var int
     */
    public $captcha_type  = self::SI_CAPTCHA_STRING; // or self::SI_CAPTCHA_MATHEMATIC, or self::SI_CAPTCHA_WORDS;

    /**
     * The captcha namespace used for having multiple captchas on a page or
     * to separate captchas from differen forms on your site.
     * Example:
     *
     *     <?php
     *     // use <img src="securimage_show.php?namespace=contact_form">
     *     // or manually in securimage_show.php
     *     $img->setNamespace('contact_form');
     *
     *     // in form validator
     *     $img->setNamespace('contact_form');
     *     if ($img->check($code) == true) {
     *         echo "Valid!";
     *     }
     *
     * @var string
     */
    public $namespace;

    /**
     * The TTF font file to use to draw the captcha code.
     *
     * Leave blank for default font AHGBold.ttf
     *
     * @var string
     */
    public $ttf_file;

    /**
     * The path to the wordlist file to use.
     *
     * Leave blank for default words/words.txt
     *
     * @var string
     */
    public $wordlist_file;

    /**
     * The directory to scan for background images, if set a random background
     * will be chosen from this folder
     *
     * @var string
     */
    public $background_directory;

    /**
     * Captcha ID if using static captcha
     * @var string Unique captcha id
     */
    protected static $_captchaId = null;

    /**
     * The GD image resource of the captcha image
     *
     * @var resource
     */
    protected $im;

    /**
     * A temporary GD image resource of the captcha image for distortion
     *
     * @var resource
     */
    protected $tmpimg;

    /**
     * The background image GD resource
     * @var resource
     */
    protected $bgimg;

    /**
     * Scale factor for magnification of distorted captcha image
     *
     * @var int
     */
    protected $iscale = 5;

    /**
     * Absolute path to securimage directory.
     *
     * This is calculated at runtime
     *
     * @var string
     */
    public $securimage_path = null;

    /**
     * The captcha challenge value.
     *
     * Either the case-sensitive/insensitive word captcha, or the solution to
     * the math captcha.
     *
     * @var string Captcha challenge value
     */
    protected $code;

    /**
     * The display value of the captcha to draw on the image
     *
     * Either the word captcha or the math equation to present to the user
     *
     * @var string Captcha display value to draw on the image
     */
    protected $code_display;

    /**
     * Alternate text to draw as the captcha image text
     *
     * A value that can be passed to the constructor that can be used to
     * generate a captcha image with a given value.
     *
     * This value does not get stored in the session or database and is only
     * used when calling Securimage::show().
     *
     * If a display_value was passed to the constructor and the captcha image
     * is generated, the display_value will be used as the string to draw on
     * the captcha image.
     *
     * Used only if captcha codes are generated and managed by a 3rd party
     * app/library
     *
     * @var string Captcha code value to display on the image
     */
    public $display_value;

    /**
     * Captcha code supplied by user [set from Securimage::check()]
     *
     * @var string
     */
    protected $captcha_code;

    /**
     * Time (in seconds) that the captcha was solved in (correctly or incorrectly).
     *
     * This is from the time of code creation, to when validation was attempted.
     *
     * @var int
     */
    protected $_timeToSolve = 0;

    /**
     * Flag that can be specified telling securimage not to call exit after
     * generating a captcha image or audio file
     *
     * @var bool If true, script will not terminate; if false script will terminate (default)
     */
    protected $no_exit;


    /**
     * Flag indicating whether or not HTTP headers will be sent when outputting
     * captcha image/audio
     *
     * @var bool If true (default) headers will be sent, if false, no headers are sent
     */
    protected $send_headers;


    /**
     * The GD color resource for the background color
     *
     * @var resource
     */
    protected $gdbgcolor;

    /**
     * The GD color resource for the text color
     *
     * @var resource
     */
    protected $gdtextcolor;

    /**
     * The GD color resource for the line color
     *
     * @var resource
     */
    protected $gdlinecolor;

    /**
     * The GD color resource for the signature text color
     *
     * @var resource
     */
    protected $gdsignaturecolor;

	/**
	 * The Placon Session
	 * @var
	 */
	protected $session;

    /**
     * Create a new securimage object, pass options to set in the constructor.
     *
     * The object can then be used to display a captcha, play an audible captcha, or validate a submission.
     *
     * @param array $options  Options to initialize the class.  May be any class property.
     *
     *     $options = array(
     *         'text_color' => new Securimage_Color('#013020'),
     *         'code_length' => 5,
     *         'num_lines' => 5,
     *         'noise_level' => 3,
     *         'font_file' => Securimage::getPath() . '/custom.ttf'
     *     );
     *
     *     $img = new Securimage($options);
     *
     */
    public function __construct($options = array())
    {
        $this->securimage_path = dirname(__FILE__);

        if (is_array($options) && sizeof($options) > 0) {
            foreach($options as $prop => $val) {
                if ($prop == 'captchaId') {
                    Securimage::$_captchaId = $val;
                    $this->use_database     = true;
                } else if ($prop == 'use_sqlite_db') {
                    trigger_error("The use_sqlite_db option is deprecated, use 'use_database' instead", E_USER_NOTICE);
                } else {
                    $this->$prop = $val;
                }
            }
        }

        $this->image_bg_color  = $this->initColor($this->image_bg_color,  '#ffffff');
        $this->text_color      = $this->initColor($this->text_color,      '#616161');
        $this->line_color      = $this->initColor($this->line_color,      '#616161');
        $this->noise_color     = $this->initColor($this->noise_color,     '#616161');
        $this->signature_color = $this->initColor($this->signature_color, '#616161');

        if (is_null($this->ttf_file)) {
            $this->ttf_file = $this->securimage_path . '/AHGBold.ttf';
        }

        $this->signature_font = $this->ttf_file;

        if (is_null($this->wordlist_file)) {
            $this->wordlist_file = $this->securimage_path . '/words/words.txt';
        }


        if (is_null($this->code_length) || (int)$this->code_length < 1) {
            $this->code_length = 6;
        }

        if (is_null($this->perturbation) || !is_numeric($this->perturbation)) {
            $this->perturbation = 0.75;
        }

        if (is_null($this->namespace) || !is_string($this->namespace)) {
            $this->namespace = 'default';
        }

        if (is_null($this->no_exit)) {
            $this->no_exit = false;
        }

        if (is_null($this->send_headers)) {
            $this->send_headers = true;
        }

    }

    /**
     * Return the absolute path to the Securimage directory.
     *
     * @return string The path to the securimage base directory
     */
    public static function getPath()
    {
        return dirname(__FILE__);
    }

    /**
     * Generate a new captcha ID or retrieve the current ID (if exists).
     *
     * @param bool $new If true, generates a new challenge and returns and ID.  If false, the existing captcha ID is returned, or null if none exists.
     * @param array $options Additional options to be passed to Securimage.
     *   $options must include database settings if they are not set directly in securimage.php
     *
     * @return null|string Returns null if no captcha id set and new was false, or the captcha ID
     */
    public static function getCaptchaId($new = true, array $options = array())
    {
        if (is_null($new) || (bool)$new == true) {
            $id = sha1(uniqid($_SERVER['REMOTE_ADDR'], true));
            $opts = array('no_session'    => true,
                          'use_database'  => true);
            if (sizeof($options) > 0) $opts = array_merge($options, $opts);
            $si = new self($opts);
            Securimage::$_captchaId = $id;
            $si->createCode();

            return $id;
        } else {
            return Securimage::$_captchaId;
        }
    }

    /**
     * Validate a captcha code input against a captcha ID
     *
     * @param string $id       The captcha ID to check
     * @param string $value    The captcha value supplied by the user
     * @param array  $options  Array of options to construct Securimage with.
     *   Options must include database options if they are not set in securimage.php
     *
     * @see Securimage::$database_driver
     * @return bool true if the code was valid for the given captcha ID, false if not or if database failed to open
     */
    public static function checkByCaptchaId($id, $value, array $options = array())
    {
        $opts = array('captchaId'    => $id,
                      'no_session'   => true,
                      'use_database' => true);

        if (sizeof($options) > 0) $opts = array_merge($options, $opts);

        $si = new self($opts);
    }


    /**
     * Generates a new challenge and serves a captcha image.
     *
     * Appropriate headers will be sent to the browser unless the *send_headers* option is false.
     *
     * @param string $background_image The absolute or relative path to the background image to use as the background of the captcha image.
     *
     *     $img = new Securimage();
     *     $img->code_length = 6;
     *     $img->num_lines   = 5;
     *     $img->noise_level = 5;
     *
     *     $img->show(); // sends the image and appropriate headers to browser
     *     exit;
     */
    public function show($background_image = '')
    {
        set_error_handler(array(&$this, 'errorHandler'));

        if($background_image != '' && is_readable($background_image)) {
            $this->bgimg = $background_image;
        }

        $this->doImage();
    }

    /**
     * Checks a given code against the correct value from the session and/or database.
     *
     * @param string $code  The captcha code to check
     *
     *     $code = $_POST['code'];
     *     $img  = new Securimage();
     *     if ($img->check($code) == true) {
     *         $captcha_valid = true;
     *     } else {
     *         $captcha_valid = false;
     *     }
     *
     * @return bool true if the given code was correct, false if not.
     */
    public function check($code)
    {
        $this->code_entered = $code;
        $this->validate();
        return $this->correct_code;
    }


    /**
     * Get the time in seconds that it took to solve the captcha.
     *
     * @return int The time in seconds from when the code was created, to when it was solved
     */
    public function getTimeToSolve()
    {
        return $this->_timeToSolve;
    }

    /**
     * Set the namespace for the captcha being stored in the session or database.
     *
     * Namespaces are useful when multiple captchas need to be displayed on a single page.
     *
     * @param string $namespace  Namespace value, String consisting of characters "a-zA-Z0-9_-"
     */
    public function setNamespace($namespace)
    {
        $namespace = preg_replace('/[^a-z0-9-_]/i', '', $namespace);
        $namespace = substr($namespace, 0, 64);

        if (!empty($namespace)) {
            $this->namespace = $namespace;
        } else {
            $this->namespace = 'default';
        }
    }

    /**
     * Return the code from the session or database (if configured).  If none exists or was found, an empty string is returned.
     *
     * @param bool $array  true to receive an array containing the code and properties, false to receive just the code.
     * @param bool $returnExisting If true, and the class property *code* is set, it will be returned instead of getting the code from the session or database.
     * @return array|string Return is an array if $array = true, otherwise a string containing the code
     */
    public function getCode($array = false, $returnExisting = false)
    {
        $code = array();
        $time = 0;
        $disp = 'error';

        if ($returnExisting && strlen($this->code) > 0) {
            if ($array) {
                return array(
                    'code'         => $this->code,
                    'display'      => $this->code_display,
                    'code_display' => $this->code_display,
                    'time'         => 0);
            } else {
                return $this->code;
            }
        }

        if ($this->session->has('securimage') && isset($this->session->securimage[$this->namespace]) &&
                trim($this->session->securimage[$this->namespace]['code_value']) != '') {
            if ($this->isCodeExpired(
                    $this->session->securimage[$this->namespace]['code_ctime'] == false)) {
                $code['code'] = $this->session->securimage[$this->namespace]['code_value'];
                $code['time'] = $this->session->securimage[$this->namespace]['code_ctime'];
                $code['display'] = $this->session->securimage[$this->namespace]['code_disp'];
            }
        }

        if ($array == true) {
            return $code;
        } else {
            return isset($code['code']) ? $code['code'] : false;
        }
    }

    /**
     * The main image drawing routing, responsible for constructing the entire image and serving it
     */
    protected function doImage()
    {
        if( ($this->use_transparent_text == true || $this->bgimg != '') && function_exists('imagecreatetruecolor')) {
            $imagecreate = 'imagecreatetruecolor';
        } else {
            $imagecreate = 'imagecreate';
        }

        $this->im     = $imagecreate($this->image_width, $this->image_height);
        $this->tmpimg = $imagecreate($this->image_width * $this->iscale, $this->image_height * $this->iscale);

        $this->allocateColors();
        imagepalettecopy($this->tmpimg, $this->im);

        $this->setBackground();

        $code = '';

        if ($this->getCaptchaId(false) !== null) {
            // a captcha Id was supplied

            // check to see if a display_value for the captcha image was set
            if (is_string($this->display_value) && strlen($this->display_value) > 0) {
                $this->code_display = $this->display_value;
                $this->code         = ($this->case_sensitive) ?
                                       $this->display_value   :
                                       strtolower($this->display_value);
                $code = $this->code;
            }
        }

        if ($code == '') {
            // if the code was not set using display_value or was not found in
            // the database, create a new code
            $this->createCode();
        }

        if ($this->noise_level > 0) {
            $this->drawNoise();
        }

        $this->drawWord();

        if ($this->perturbation > 0 && is_readable($this->ttf_file)) {
            $this->distortedCopy();
        }

        if ($this->num_lines > 0) {
            $this->drawLines();
        }

        if (trim($this->image_signature) != '') {
            $this->addSignature();
        }

        $this->output();
    }

    /**
     * Allocate the colors to be used for the image
     */
    protected function allocateColors()
    {
        // allocate bg color first for imagecreate
        $this->gdbgcolor = imagecolorallocate($this->im,
                                              $this->image_bg_color->r,
                                              $this->image_bg_color->g,
                                              $this->image_bg_color->b);

        $alpha = intval($this->text_transparency_percentage / 100 * 127);

        if ($this->use_transparent_text == true) {
            $this->gdtextcolor = imagecolorallocatealpha($this->im,
                                                         $this->text_color->r,
                                                         $this->text_color->g,
                                                         $this->text_color->b,
                                                         $alpha);
            $this->gdlinecolor = imagecolorallocatealpha($this->im,
                                                         $this->line_color->r,
                                                         $this->line_color->g,
                                                         $this->line_color->b,
                                                         $alpha);
            $this->gdnoisecolor = imagecolorallocatealpha($this->im,
                                                          $this->noise_color->r,
                                                          $this->noise_color->g,
                                                          $this->noise_color->b,
                                                          $alpha);
        } else {
            $this->gdtextcolor = imagecolorallocate($this->im,
                                                    $this->text_color->r,
                                                    $this->text_color->g,
                                                    $this->text_color->b);
            $this->gdlinecolor = imagecolorallocate($this->im,
                                                    $this->line_color->r,
                                                    $this->line_color->g,
                                                    $this->line_color->b);
            $this->gdnoisecolor = imagecolorallocate($this->im,
                                                          $this->noise_color->r,
                                                          $this->noise_color->g,
                                                          $this->noise_color->b);
        }

        $this->gdsignaturecolor = imagecolorallocate($this->im,
                                                     $this->signature_color->r,
                                                     $this->signature_color->g,
                                                     $this->signature_color->b);

    }

    /**
     * The the background color, or background image to be used
     */
    protected function setBackground()
    {
        // set background color of image by drawing a rectangle since imagecreatetruecolor doesn't set a bg color
        imagefilledrectangle($this->im, 0, 0,
                             $this->image_width, $this->image_height,
                             $this->gdbgcolor);
        imagefilledrectangle($this->tmpimg, 0, 0,
                             $this->image_width * $this->iscale, $this->image_height * $this->iscale,
                             $this->gdbgcolor);

        if ($this->bgimg == '') {
            if ($this->background_directory != null &&
                is_dir($this->background_directory) &&
                is_readable($this->background_directory))
            {
                $img = $this->getBackgroundFromDirectory();
                if ($img != false) {
                    $this->bgimg = $img;
                }
            }
        }

        if ($this->bgimg == '') {
            return;
        }

        $dat = @getimagesize($this->bgimg);
        if($dat == false) {
            return;
        }

        switch($dat[2]) {
            case 1:  $newim = @imagecreatefromgif($this->bgimg); break;
            case 2:  $newim = @imagecreatefromjpeg($this->bgimg); break;
            case 3:  $newim = @imagecreatefrompng($this->bgimg); break;
            default: return;
        }

        if(!$newim) return;

        imagecopyresized($this->im, $newim, 0, 0, 0, 0,
                         $this->image_width, $this->image_height,
                         imagesx($newim), imagesy($newim));
    }

    /**
     * Scan the directory for a background image to use
     */
    protected function getBackgroundFromDirectory()
    {
        $images = array();

        if ( ($dh = opendir($this->background_directory)) !== false) {
            while (($file = readdir($dh)) !== false) {
                if (preg_match('/(jpg|gif|png)$/i', $file)) $images[] = $file;
            }

            closedir($dh);

            if (sizeof($images) > 0) {
                return rtrim($this->background_directory, '/') . '/' . $images[mt_rand(0, sizeof($images)-1)];
            }
        }

        return false;
    }

    /**
     * This method generates a new captcha code.
     *
     * Generates a random captcha code based on *charset*, math problem, or captcha from the wordlist and saves the value to the session and/or database.
     */
    public function createCode()
    {
        $this->code = false;

        switch($this->captcha_type) {
            case self::SI_CAPTCHA_MATHEMATIC:
            {
                do {
                    $signs = array('+', '-', 'x');
                    $left  = mt_rand(1, 10);
                    $right = mt_rand(1, 5);
                    $sign  = $signs[mt_rand(0, 2)];

                    switch($sign) {
                        case 'x': $c = $left * $right; break;
                        case '-': $c = $left - $right; break;
                        default:  $c = $left + $right; break;
                    }
                } while ($c <= 0); // no negative #'s or 0

                $this->code         = $c;
                $this->code_display = "$left $sign $right";
                break;
            }

            case self::SI_CAPTCHA_WORDS:
                $words = $this->readCodeFromFile(2);
                $this->code = implode(' ', $words);
                $this->code_display = $this->code;
                break;

            default:
            {
                if ($this->use_wordlist && is_readable($this->wordlist_file)) {
                    $this->code = $this->readCodeFromFile();
                }

                if ($this->code == false) {
                    $this->code = $this->generateCode($this->code_length);
                }

                $this->code_display = $this->code;
                $this->code         = ($this->case_sensitive) ? $this->code : strtolower($this->code);
            } // default
        }

        $this->saveData();
    }

    /**
     * Draws the captcha code on the image
     */
    protected function drawWord()
    {
        $width2  = $this->image_width * $this->iscale;
        $height2 = $this->image_height * $this->iscale;
        $ratio   = ($this->font_ratio) ? $this->font_ratio : 0.4;

        if ((float)$ratio < 0.1 || (float)$ratio >= 1) {
            $ratio = 0.4;
        }

        if (!is_readable($this->ttf_file)) {
            imagestring($this->im, 4, 10, ($this->image_height / 2) - 5, 'Failed to load TTF font file!', $this->gdtextcolor);
        } else {
            if ($this->perturbation > 0) {
                $font_size = $height2 * $ratio;
                $bb = imageftbbox($font_size, 0, $this->ttf_file, $this->code_display);
                $tx = $bb[4] - $bb[0];
                $ty = $bb[5] - $bb[1];
                $x  = floor($width2 / 2 - $tx / 2 - $bb[0]);
                $y  = round($height2 / 2 - $ty / 2 - $bb[1]);

                imagettftext($this->tmpimg, $font_size, 0, $x, $y, $this->gdtextcolor, $this->ttf_file, $this->code_display);
            } else {
                $font_size = $this->image_height * $ratio;
                $bb = imageftbbox($font_size, 0, $this->ttf_file, $this->code_display);
                $tx = $bb[4] - $bb[0];
                $ty = $bb[5] - $bb[1];
                $x  = floor($this->image_width / 2 - $tx / 2 - $bb[0]);
                $y  = round($this->image_height / 2 - $ty / 2 - $bb[1]);

                imagettftext($this->im, $font_size, 0, $x, $y, $this->gdtextcolor, $this->ttf_file, $this->code_display);
            }
        }

        // DEBUG
        //$this->im = $this->tmpimg;
        //$this->output();

    }

    /**
     * Copies the captcha image to the final image with distortion applied
     */
    protected function distortedCopy()
    {
        $numpoles = 3; // distortion factor
        // make array of poles AKA attractor points
        for ($i = 0; $i < $numpoles; ++ $i) {
            $px[$i]  = mt_rand($this->image_width  * 0.2, $this->image_width  * 0.8);
            $py[$i]  = mt_rand($this->image_height * 0.2, $this->image_height * 0.8);
            $rad[$i] = mt_rand($this->image_height * 0.2, $this->image_height * 0.8);
            $tmp     = ((- $this->frand()) * 0.15) - .15;
            $amp[$i] = $this->perturbation * $tmp;
        }

        $bgCol = imagecolorat($this->tmpimg, 0, 0);
        $width2 = $this->iscale * $this->image_width;
        $height2 = $this->iscale * $this->image_height;
        imagepalettecopy($this->im, $this->tmpimg); // copy palette to final image so text colors come across
        // loop over $img pixels, take pixels from $tmpimg with distortion field
        for ($ix = 0; $ix < $this->image_width; ++ $ix) {
            for ($iy = 0; $iy < $this->image_height; ++ $iy) {
                $x = $ix;
                $y = $iy;
                for ($i = 0; $i < $numpoles; ++ $i) {
                    $dx = $ix - $px[$i];
                    $dy = $iy - $py[$i];
                    if ($dx == 0 && $dy == 0) {
                        continue;
                    }
                    $r = sqrt($dx * $dx + $dy * $dy);
                    if ($r > $rad[$i]) {
                        continue;
                    }
                    $rscale = $amp[$i] * sin(3.14 * $r / $rad[$i]);
                    $x += $dx * $rscale;
                    $y += $dy * $rscale;
                }
                $c = $bgCol;
                $x *= $this->iscale;
                $y *= $this->iscale;
                if ($x >= 0 && $x < $width2 && $y >= 0 && $y < $height2) {
                    $c = imagecolorat($this->tmpimg, $x, $y);
                }
                if ($c != $bgCol) { // only copy pixels of letters to preserve any background image
                    imagesetpixel($this->im, $ix, $iy, $c);
                }
            }
        }
    }

    /**
     * Draws distorted lines on the image
     */
    protected function drawLines()
    {
        for ($line = 0; $line < $this->num_lines; ++ $line) {
            $x = $this->image_width * (1 + $line) / ($this->num_lines + 1);
            $x += (0.5 - $this->frand()) * $this->image_width / $this->num_lines;
            $y = mt_rand($this->image_height * 0.1, $this->image_height * 0.9);

            $theta = ($this->frand() - 0.5) * M_PI * 0.7;
            $w = $this->image_width;
            $len = mt_rand($w * 0.4, $w * 0.7);
            $lwid = mt_rand(0, 2);

            $k = $this->frand() * 0.6 + 0.2;
            $k = $k * $k * 0.5;
            $phi = $this->frand() * 6.28;
            $step = 0.5;
            $dx = $step * cos($theta);
            $dy = $step * sin($theta);
            $n = $len / $step;
            $amp = 1.5 * $this->frand() / ($k + 5.0 / $len);
            $x0 = $x - 0.5 * $len * cos($theta);
            $y0 = $y - 0.5 * $len * sin($theta);

            $ldx = round(- $dy * $lwid);
            $ldy = round($dx * $lwid);

            for ($i = 0; $i < $n; ++ $i) {
                $x = $x0 + $i * $dx + $amp * $dy * sin($k * $i * $step + $phi);
                $y = $y0 + $i * $dy - $amp * $dx * sin($k * $i * $step + $phi);
                imagefilledrectangle($this->im, $x, $y, $x + $lwid, $y + $lwid, $this->gdlinecolor);
            }
        }
    }

    /**
     * Draws random noise on the image
     */
    protected function drawNoise()
    {
        if ($this->noise_level > 10) {
            $noise_level = 10;
        } else {
            $noise_level = $this->noise_level;
        }

        $t0 = microtime(true);

        $noise_level *= 125; // an arbitrary number that works well on a 1-10 scale

        $points = $this->image_width * $this->image_height * $this->iscale;
        $height = $this->image_height * $this->iscale;
        $width  = $this->image_width * $this->iscale;
        for ($i = 0; $i < $noise_level; ++$i) {
            $x = mt_rand(10, $width);
            $y = mt_rand(10, $height);
            $size = mt_rand(7, 10);
            if ($x - $size <= 0 && $y - $size <= 0) continue; // dont cover 0,0 since it is used by imagedistortedcopy
            imagefilledarc($this->tmpimg, $x, $y, $size, $size, 0, 360, $this->gdnoisecolor, IMG_ARC_PIE);
        }

        $t1 = microtime(true);

        $t = $t1 - $t0;

        /*
        // DEBUG
        imagestring($this->tmpimg, 5, 25, 30, "$t", $this->gdnoisecolor);
        header('content-type: image/png');
        imagepng($this->tmpimg);
        exit;
        */
    }

    /**
    * Print signature text on image
    */
    protected function addSignature()
    {
        $bbox = imagettfbbox(10, 0, $this->signature_font, $this->image_signature);
        $textlen = $bbox[2] - $bbox[0];
        $x = $this->image_width - $textlen - 5;
        $y = $this->image_height - 3;

        imagettftext($this->im, 10, 0, $x, $y, $this->gdsignaturecolor, $this->signature_font, $this->image_signature);
    }

    /**
     * Sends the appropriate image and cache headers and outputs image to the browser
     */
    protected function output()
    {
        if ($this->canSendHeaders() || $this->send_headers == false) {
            if ($this->send_headers) {
                // only send the content-type headers if no headers have been output
                // this will ease debugging on misconfigured servers where warnings
                // may have been output which break the image and prevent easily viewing
                // source to see the error.
                header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
                header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT");
                header("Cache-Control: no-store, no-cache, must-revalidate");
                header("Cache-Control: post-check=0, pre-check=0", false);
                header("Pragma: no-cache");
            }

            switch ($this->image_type) {
                case self::SI_IMAGE_JPEG:
                    if ($this->send_headers) header("Content-Type: image/jpeg");
                    imagejpeg($this->im, null, 90);
                    break;
                case self::SI_IMAGE_GIF:
                    if ($this->send_headers) header("Content-Type: image/gif");
                    imagegif($this->im);
                    break;
                default:
                    if ($this->send_headers) header("Content-Type: image/png");
                    imagepng($this->im);
                    break;
            }
        } else {
            echo '<hr /><strong>'
                .'Failed to generate captcha image, content has already been '
                .'output.<br />This is most likely due to misconfiguration or '
                .'a PHP error was sent to the browser.</strong>';
        }

        imagedestroy($this->im);
        restore_error_handler();

        if (!$this->no_exit) exit;
    }


    /**
     * Gets a captcha code from a file containing a list of words.
     *
     * Seek to a random offset in the file and reads a block of data and returns a line from the file.
     *
     * @param int $numWords Number of words (lines) to read from the file
     * @return string|array  Returns a string if only one word is to be read, or an array of words
     */
    protected function readCodeFromFile($numWords = 1)
    {
        $fp = fopen($this->wordlist_file, 'rb');
        if (!$fp) return false;

        $fsize = filesize($this->wordlist_file);
        if ($fsize < 128) return false; // too small of a list to be effective

        if ((int)$numWords < 1 || (int)$numWords > 5) $numWords = 1;

        $words = array();
        $i = 0;
        do {
            fseek($fp, mt_rand(0, $fsize - 64), SEEK_SET); // seek to a random position of file from 0 to filesize-64
            $data = fread($fp, 64); // read a chunk from our random position
            $data = preg_replace("/\r?\n/", "\n", $data);

            $start = @strpos($data, "\n", mt_rand(0, 56)) + 1; // random start position
            $end   = @strpos($data, "\n", $start);          // find end of word

            if ($start === false) {
                // picked start position at end of file
                continue;
            } else if ($end === false) {
                $end = strlen($data);
            }

            $word = strtolower(substr($data, $start, $end - $start)); // return a line of the file
            $words[] = $word;
        } while (++$i < $numWords);

        fclose($fp);

        if ($numWords < 2) {
            return $words[0];
        } else {
            return $words;
        }
    }

    /**
     * Generates a random captcha code from the set character set
     *
     * @see Securimage::$charset  Charset option
     * @return string A randomly generated CAPTCHA code
     */
    protected function generateCode()
    {
        $code = '';

        if (function_exists('mb_strlen')) {
            for($i = 1, $cslen = mb_strlen($this->charset); $i <= $this->code_length; ++$i) {
                $code .= mb_substr($this->charset, mt_rand(0, $cslen - 1), 1, 'UTF-8');
            }
        } else {
            for($i = 1, $cslen = strlen($this->charset); $i <= $this->code_length; ++$i) {
                $code .= substr($this->charset, mt_rand(0, $cslen - 1), 1);
            }
        }

        return $code;
    }

    /**
     * Validate a code supplied by the user
     *
     * Checks the entered code against the value stored in the session and/or database (if configured).  Handles case sensitivity.
     * Also removes the code from session/database if the code was entered correctly to prevent re-use attack.
     *
     * This function does not return a value.
     *
     * @see Securimage::$correct_code 'correct_code' property
     */
    protected function validate()
    {
        if (!is_string($this->code) || strlen($this->code) == 0) {
            $code = $this->getCode(true);
            // returns stored code, or an empty string if no stored code was found
            // checks the session and database if enabled
        } else {
            $code = $this->code;
        }

        if (is_array($code)) {
            if (!empty($code)) {
                $ctime = $code['time'];
                $code  = $code['code'];

                $this->_timeToSolve = time() - $ctime;
            } else {
                $code = '';
            }
        }

        if ($this->case_sensitive == false && preg_match('/[A-Z]/', $code)) {
            // case sensitive was set from securimage_show.php but not in class
            // the code saved in the session has capitals so set case sensitive to true
            $this->case_sensitive = true;
        }

        $code_entered = trim( (($this->case_sensitive) ? $this->code_entered
                                                       : strtolower($this->code_entered))
                        );
        $this->correct_code = false;

        if ($code != '') {
            if (strpos($code, ' ') !== false) {
                // for multi word captchas, remove more than once space from input
                $code_entered = preg_replace('/\s+/', ' ', $code_entered);
                $code_entered = strtolower($code_entered);
            }

            if ((string)$code === (string)$code_entered) {
                $this->correct_code = true;
	            if ($this->session->has('securimage')) {
		            $session_data = $this->session->securimage;
	            } else {
		            $session_data = array();
	            }

	            $session_data[$this->namespace] = array(
		            'code_disp' => '',
		            'code_value' => '',
		            'code_ctime' => '',
	            );

                $this->session->set('securimage', $session_data);
            }
        }
    }

    /**
     * Save CAPTCHA data to session and database (if configured)
     */
    protected function saveData()
    {
        if ($this->session->has('securimage')) {
            // fix for migration from v2 - v3
            $this->session->remove('securimage');
        }

	    if ($this->session->has('securimage')) {
		    $session_data = $this->session->securimage;
	    } else {
		    $session_data = array();
	    }

	    $session_data[$this->namespace] = array(
		    'code_disp' => $this->code_display,
		    'code_value' => $this->code,
		    'code_ctime' => time(),
	    );

		    $this->session->set('securimage', $session_data);
    }

    /**
     * Checks to see if the captcha code has expired and can no longer be used.
     *
     * @see Securimage::$expiry_time expiry_time
     * @param int $creation_time  The Unix timestamp of when the captcha code was created
     * @return bool true if the code is expired, false if it is still valid
     */
    protected function isCodeExpired($creation_time)
    {
        $expired = true;

        if (!is_numeric($this->expiry_time) || $this->expiry_time < 1) {
            $expired = false;
        } else if (time() - $creation_time < $this->expiry_time) {
            $expired = false;
        }

        return $expired;
    }

    /**
     * Get a random effect or chain of effects to apply to a segment of the
     * audio file.
     *
     * These effects should increase the randomness of the audio for
     * a particular letter/number by modulating the signal.  The SoX effects
     * used are *bend*, *chorus*, *overdrive*, *pitch*, *reverb*, *tempo*, and
     * *tremolo*.
     *
     * For each effect selected, random parameters are supplied to the effect.
     *
     * @param int $numEffects  How many effects to chain together
     * @return string  A string of valid SoX effects and their respective options.
     */
    protected function getSoxEffectChain($numEffects = 2)
    {
        $effectsList = array('bend', 'chorus', 'overdrive', 'pitch', 'reverb', 'tempo', 'tremolo');
        $effects     = array_rand($effectsList, $numEffects);
        $outEffects  = array();

        if (!is_array($effects)) $effects = array($effects);

        foreach($effects as $effect) {
            $effect = $effectsList[$effect];

            switch($effect)
            {
                case 'bend':
                    $delay = mt_rand(0, 15) / 100.0;
                    $cents = mt_rand(-120, 120);
                    $dur   = mt_rand(75, 400) / 100.0;
                    $outEffects[] = "$effect $delay,$cents,$dur";
                    break;

                case 'chorus':
                    $gainIn  = mt_rand(75, 90) / 100.0;
                    $gainOut = mt_rand(70, 95) / 100.0;
                    $chorStr = "$effect $gainIn $gainOut";

                    for ($i = 0; $i < mt_rand(2, 3); ++$i) {
                        $delay = mt_rand(20, 100);
                        $decay = mt_rand(10, 100) / 100.0;
                        $speed = mt_rand(20, 50) / 100.0;
                        $depth = mt_rand(150, 250) / 100.0;

                        $chorStr .= " $delay $decay $speed $depth -s";
                    }

                    $outEffects[] = $chorStr;
                    break;

                case 'overdrive':
                    $gain = mt_rand(5, 25);
                    $color = mt_rand(20, 70);
                    $outEffects[] = "$effect $gain $color";
                    break;

                case 'pitch':
                    $cents = mt_rand(-300, 300);
                    $outEffects[] = "$effect $cents";
                    break;

                case 'reverb':
                    $reverberance = mt_rand(20, 80);
                    $damping      = mt_rand(10, 80);
                    $scale        = mt_rand(85, 100);
                    $depth        = mt_rand(90, 100);
                    $predelay     = mt_rand(0, 5);
                    $outEffects[] = "$effect $reverberance $damping $scale $depth $predelay";
                    break;

                case 'tempo':
                    $factor = mt_rand(65, 135) / 100.0;
                    $outEffects[] = "$effect -s $factor";
                    break;

                case 'tremolo':
                    $hz    = mt_rand(10, 30);
                    $depth = mt_rand(40, 85);
                    $outEffects[] = "$effect $hz $depth";
                    break;
            }
        }

        return implode(' ', $outEffects);
    }

    /**
     * This function is not yet used.
     *
     * Generate random background noise from sweeping oscillators
     *
     * @param float $duration  How long in seconds the generated sound will be
     * @param int $numChannels Number of channels in output wav
     * @param int $sampleRate  Sample rate of output wav
     * @param int $bitRate     Bits per sample (8, 16, 24)
     * @return string          Audio data in wav format
     */
    protected function getSoxNoiseData($duration, $numChannels, $sampleRate, $bitRate)
    {
        $shapes = array('sine', 'square', 'triangle', 'sawtooth', 'trapezium');
        $steps  = array(':', '+', '/', '-');
        $selShapes = array_rand($shapes, 2);
        $selSteps  = array_rand($steps, 2);
        $sweep0    = array();
        $sweep0[0] = mt_rand(100, 700);
        $sweep0[1] = mt_rand(1500, 2500);
        $sweep1    = array();
        $sweep1[0] = mt_rand(500, 1000);
        $sweep1[1] = mt_rand(1200, 2000);

        if (mt_rand(0, 10) % 2 == 0)
            $sweep0 = array_reverse($sweep0);

        if (mt_rand(0, 10) % 2 == 0)
            $sweep1 = array_reverse($sweep1);

        $cmd = sprintf("%s -c %d -r %d -b %d -n -t wav - synth noise create vol 0.3 synth %.2f %s mix %d%s%d vol 0.3 synth %.2f %s fmod %d%s%d vol 0.3",
                       $numChannels,
                       $sampleRate,
                       $bitRate,
                       $duration,
                       $shapes[$selShapes[0]],
                       $sweep0[0],
                       $steps[$selSteps[0]],
                       $sweep0[1],
                       $duration,
                       $shapes[$selShapes[1]],
                       $sweep1[0],
                       $steps[$selSteps[1]],
                       $sweep1[1]
                       );
        $data = `$cmd`;

        return $data;
    }


    /**
     * Checks to see if headers can be sent and if any error has been output
     * to the browser
     *
     * @return bool true if it is safe to send headers, false if not
     */
    protected function canSendHeaders()
    {
        if (headers_sent()) {
            // output has been flushed and headers have already been sent
            return false;
        } else if (strlen((string)ob_get_contents()) > 0) {
            // headers haven't been sent, but there is data in the buffer that will break image and audio data
            return false;
        }

        return true;
    }

    /**
     * Return a random float between 0 and 0.9999
     *
     * @return float Random float between 0 and 0.9999
     */
    function frand()
    {
        return 0.0001 * mt_rand(0,9999);
    }

    /**
     * Convert an html color code to a Securimage_Color
     * @param string $color
     * @param Securimage_Color $default The defalt color to use if $color is invalid
     */
    protected function initColor($color, $default)
    {
        if ($color == null) {
            return new Securimage_Color($default);
        } else if (is_string($color)) {
            try {
                return new Securimage_Color($color);
            } catch(Exception $e) {
                return new Securimage_Color($default);
            }
        } else if (is_array($color) && sizeof($color) == 3) {
            return new Securimage_Color($color[0], $color[1], $color[2]);
        } else {
            return new Securimage_Color($default);
        }
    }

    /**
     * The error handling function used when outputting captcha image or audio.
     *
     * This error handler helps determine if any errors raised would
     * prevent captcha image or audio from displaying.  If they have
     * no effect on the output buffer or headers, true is returned so
     * the script can continue processing.
     *
     * See https://github.com/dapphp/securimage/issues/15
     *
     * @param int $errno  PHP error number
     * @param string $errstr  String description of the error
     * @param string $errfile  File error occurred in
     * @param int $errline  Line the error occurred on in file
     * @param array $errcontext  Additional context information
     * @return boolean true if the error was handled, false if PHP should handle the error
     */
    public function errorHandler($errno, $errstr, $errfile = '', $errline = 0, $errcontext = array())
    {
        // get the current error reporting level
        $level = error_reporting();

        // if error was supressed or $errno not set in current error level
        if ($level == 0 || ($level & $errno) == 0) {
            return true;
        }

        return false;
    }
}


/**
 * Color object for Securimage CAPTCHA
 *
 * @version 3.0
 * @since 2.0
 * @package Securimage
 * @subpackage classes
 *
 */
class Securimage_Color
{
    /**
     * Red value (0-255)
     * @var int
     */
    public $r;

    /**
     * Gree value (0-255)
     * @var int
     */
    public $g;

    /**
     * Blue value (0-255)
     * @var int
     */
    public $b;

    /**
     * Create a new Securimage_Color object.
     *
     * Constructor expects 1 or 3 arguments.
     *
     * When passing a single argument, specify the color using HTML hex format.
     *
     * When passing 3 arguments, specify each RGB component (from 0-255)
     * individually.
     *
     * Examples:
     *
     *     $color = new Securimage_Color('#0080FF');
     *     $color = new Securimage_Color(0, 128, 255);
     *
     * @param string $color  The html color code to use
     * @throws Exception  If any color value is not valid
     */
    public function __construct($color = '#ffffff')
    {
        $args = func_get_args();

        if (sizeof($args) == 0) {
            $this->r = 255;
            $this->g = 255;
            $this->b = 255;
        } else if (sizeof($args) == 1) {
            // set based on html code
            if (substr($color, 0, 1) == '#') {
                $color = substr($color, 1);
            }

            if (strlen($color) != 3 && strlen($color) != 6) {
                throw new InvalidArgumentException(
                  'Invalid HTML color code passed to Securimage_Color'
                );
            }

            $this->constructHTML($color);
        } else if (sizeof($args) == 3) {
            $this->constructRGB($args[0], $args[1], $args[2]);
        } else {
            throw new InvalidArgumentException(
              'Securimage_Color constructor expects 0, 1 or 3 arguments; ' . sizeof($args) . ' given'
            );
        }
    }

    /**
     * Construct from an rgb triplet
     *
     * @param int $red The red component, 0-255
     * @param int $green The green component, 0-255
     * @param int $blue The blue component, 0-255
     */
    protected function constructRGB($red, $green, $blue)
    {
        if ($red < 0)     $red   = 0;
        if ($red > 255)   $red   = 255;
        if ($green < 0)   $green = 0;
        if ($green > 255) $green = 255;
        if ($blue < 0)    $blue  = 0;
        if ($blue > 255)  $blue  = 255;

        $this->r = $red;
        $this->g = $green;
        $this->b = $blue;
    }

    /**
     * Construct from an html hex color code
     *
     * @param string $color
     */
    protected function constructHTML($color)
    {
        if (strlen($color) == 3) {
            $red   = str_repeat(substr($color, 0, 1), 2);
            $green = str_repeat(substr($color, 1, 1), 2);
            $blue  = str_repeat(substr($color, 2, 1), 2);
        } else {
            $red   = substr($color, 0, 2);
            $green = substr($color, 2, 2);
            $blue  = substr($color, 4, 2);
        }

        $this->r = hexdec($red);
        $this->g = hexdec($green);
        $this->b = hexdec($blue);
    }
}
