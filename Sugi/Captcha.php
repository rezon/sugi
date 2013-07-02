<?php namespace Sugi;
/**
 * @package Sugi
 * @author  Plamen Popov <tzappa@gmail.com>
 * @license http://opensource.org/licenses/mit-license.php (MIT License)
 */

/**
 * Captcha - Completely Automated Public Turing test to tell Computers and Humans Apart
 */
class Captcha
{
	/**
	 * Configuration settings
	 * @var array
	 */
	protected $config = array();

	/**
	 * Generated captcha string
	 * @var string
	 */
	protected $value = "";

	public function __construct(array $config = null)
	{
		// default settings
		$this->setDefaultConfig();

		// overwrite default settings
		if (!is_null($config)) {
			$this->setConfig($config);
		}
	}

	public function setDefaultConfig()
	{
		$this->config = array(
			"width"      => 90, // The width of the captcha image in px
			"height"     => 40, // The height of the captcha image in px
			"length"     => 4, // The length of the code
			"background" => array(255, 255, 255), // Background color in RGB-array
			"color"      => array(64, 64, 64), // Font color in RGB-array
			"font"       => __DIR__."/fonts/Ubuntu-B.ttf",
			"minfont"    => "auto", // The minimum of the font in pixels. "auto" will try to calculate it based on the width of the image, and the length of the code 
			"maxfont"    => "auto", // .. and maximum size 
			"chars"      => "2345678abcdefhkmnprstuvwxyz", // several chars are removed for readability - 0 and O, 1 and i/I, g/q and 9
			// "chars"      => "12345789авгдежийклмнпрстуфхцчшъюя", // Cyrillic - 0 and O, б and 6, щ, ь are removed
			"noise"      => 3, // The noise in percent - should be less that 20, otherwise the image will be very hard to read
			"blur"       => true, // add blur effect
			"sessionvar" => "captcha", // set to false if you want class to NOT store the value in the session
			"format"     => "jpg",
		);
	}

	public function setConfig(array $config)
	{
		foreach ($this->config as $k => $value) {
			if (isset($config[$k])) {
				$this->config[$k] = $config[$k];
			}
		}
	}

	public function genImage()
	{
		// text to be printed
		if (!$this->value) {
			$this->value = $this->genValue();
		}
		$chars = mb_strlen($this->value, "utf8");

		// create canvas
		$image = imagecreatetruecolor($this->config["width"], $this->config["height"]);
		$bg = imagecolorallocate($image, $this->config["background"][0], $this->config["background"][1], $this->config["background"][2]);
		$fg = imagecolorallocate($image, $this->config["color"][0], $this->config["color"][1], $this->config["color"][2]);
		imagefill($image, $this->config["width"] / 2, $this->config["height"] / 2, $bg);

		// font size
		if ($this->config["minfont"] == "auto") {
			$fsmin = min($this->config["width"] / ($chars + 1), $this->config["height"]);
		} else {
			$fsmin =$this->config["minfont"];
		}
		if ($this->config["maxfont"] == "auto") {
			$fsmax = min($this->config["width"] / ($chars + 1), $this->config["height"]);
		} else {
			$fsmax = $this->config["maxfont"];
		}
		// Padding - offset of the text
		$xStart = -$fsmax / 2;
		$xEnd = $this->config["width"];
		$yStart = $this->config["height"] * 0.6;
		$yEnd = $this->config["height"] * 0.8;
		// angle
		$angle = mt_rand(-15, 15);

		// print chars
		for ($i = 0; $i < $chars; $i++) {
			$angle += mt_rand(-3, 3);
			$xStart += $fsmax;
			imagefttext(
				$image, 
				mt_rand($fsmin, $fsmax), 
				$angle,
				$xStart, 
				mt_rand($yStart, $yEnd), 
				$fg, 
				$this->config["font"],
				mb_substr($this->value, $i, 1, "utf8")
			);
		}

		// Add some noise to the image.
		if ($this->config["noise"]) {
			for ($i = 0; $i < $this->config["noise"]; $i++) {
				$color = imagecolorallocate($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
				for ($j = 0; $j < $this->config["width"] * $this->config["height"] / 100; $j++) {
					imagesetpixel($image, mt_rand(0, $this->config["width"]), mt_rand(0, $this->config["height"]), $color);
				}
			}
		}
		// blur image
		if ($this->config["blur"]) {
			imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);
		}
		
		// send image
		if ($this->config["format"] == "png") {
			header("Content-type: image/png");
			imagepng($image);
		}
		else {
			header("Content-type: image/jpeg");
			imagejpeg($image, null, 80);
		}
		imagedestroy($image);

		//return $image;
	}

	public function check($enteredValue)
	{
		if ($this->config["sessionvar"]) {
			if (!empty($_SESSION[$this->config["sessionvar"]])) {
				if (mb_strtolower($_SESSION[$this->config["sessionvar"]], "utf8") == mb_strtolower($enteredValue, "utf8")) {
					return true;
				}
				unset($_SESSION[$this->config["sessionvar"]]);
			}
		}

		return false;
	}

	public function getValue()
	{
		return $this->value;		
	}

	public function setValue($value)
	{
		$this->value = $value;
	}

	public function genValue()
	{
		$val = "";
		$cnt = mb_strlen($this->config["chars"], "utf8") - 1;
		for ($i = 0; $i < $this->config["length"]; $i++) {
			$char = mb_substr($this->config["chars"], mt_rand(0, $cnt), 1, "utf8");
			$val .= $char;
		}

		if ($this->config["sessionvar"]) {
			$_SESSION[$this->config["sessionvar"]] = $val;
		}

		return $val;
	}
}
