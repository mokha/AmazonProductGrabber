<?php

require_once 'config.php';
require_once 'simple_html_dom.php';

Class Grabber {

    public static function curl_grab($Url) {
	
		$cookiefile = tempnam("/tmp", "CURLCookies");  
		
        //list of browsers
        $agentBrowser = array(
            'Firefox',
            'Safari',
            'Opera',
            'Flock',
            'Internet Explorer',
            'Seamonkey',
            'Konqueror',
            'GoogleBot'
        );
        //list of operating systems
        $agentOS = array(
            'Windows 3.1',
            'Windows 95',
            'Windows 98',
            'Windows 2000',
            'Windows NT',
            'Windows XP',
            'Windows Vista',
            'Redhat Linux',
            'Ubuntu',
            'Fedora',
            'AmigaOS',
            'OS 10.5'
        );

        // is cURL installed yet?
        if (!function_exists('curl_init')) {
            die('Sorry cURL is not installed!');
        }

        // OK cool - then let's create a new cURL resource handle
        $ch = curl_init();

        // Now set some options (most are optional)
        // Set URL to download
        curl_setopt($ch, CURLOPT_URL, $Url);

        //randomly generate UserAgent
        $userAgent = $agentBrowser[rand(0, 7)] . '/' . rand(1, 8) . '.' . rand(0, 9) . ' (' . $agentOS[rand(0, 11)] . ' ' . rand(1, 7) . '.' . rand(0, 9) . '; en-US;)';

		// User agent
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);

        // Include header in result? (0 = yes, 1 = no)
        curl_setopt($ch, CURLOPT_HEADER, 0);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // follow any redirects
        // Should cURL return or print out the data? (true = return, false = print)
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Timeout in seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);  
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);  
		
        // Download the given URL, and return output
        $output = curl_exec($ch);

        // Close the cURL resource, and free system resources
        curl_close($ch);

        return $output;
    }

}

Class Product {

    private $title = '';
    private $technicalDetails = array();
    private $picture;
    private $remotePic = '';
    private $success = false;
    private $url = '';

    public function initlizeData($title, $picture, $details, $url) {

        if ($title != '' && $picture != '' && $url != '') {
            $this->remotePic = $picture;
            $this->title = $title;
            $this->technicalDetails = $details;
            $this->url = $url;

            $this->success = true;
        }

        return $this->success;
    }

    public function initlizeUrl($url) {
        $this->url = $url;
        $html = Grabber::curl_grab($url);
        if ($html !== false) {
            $html = str_get_html($html); //Parse HTML
            if (isSet($html)) {
                //title=>#btAsinTitle
                if (isSet($html->find('#btAsinTitle', 0)->plaintext)) {
                    $this->title = $html->find('#btAsinTitle', 0)->plaintext;
                }else
                    return;

                //image=>#main-image or #prodImage
                $img = $html->find('#prodImage', 0);
                if (!$img) {
                    $img = $html->find('#main-image', 0);
                }
                if ($img) {
                    $this->remotePic = $img->src;
                }

                //details=>
                $buckets = $html->find('.bucket');
                if (!empty($buckets)) {
                    foreach ($buckets as $details) {
                        if (isSet($details->find('h2', 0)->plaintext)
                                && $details->find('h2', 0)->plaintext == 'Technical Details') {
                            $this->technicalDetails = $details->find('.content', 0)->plaintext;
                        }
                    }
                }
                $this->success = true;
            }
        }
    }

    public function isSuccess() {
        return $this->success;
    }

    public function store() {

        if ($this->success
                && $this->title != ''
                && $this->remotePic != ''
                && $this->url != '') {
            //Get Picture
            $remoteContent = file_get_contents($this->remotePic);


            $filename = uniqid(time(), true).'.jpg';
            $this->picture = $filename ;
            //Store it locally
            $fp = fopen("img/" . $filename, "w");
            fwrite($fp, $remoteContent);
            fclose($fp);


            $title = mysql_real_escape_string($this->title);
            if (is_array($this->technicalDetails))
                $details = mysql_real_escape_string(json_encode($this->technicalDetails));
            else
                $details = mysql_real_escape_string($this->technicalDetails);
            $url = mysql_real_escape_string($this->url);

            $query = "INSERT INTO `products`(`title`,`technicalDetails`,`picture`,`url`) VALUES 
                                    ('$title','$details','$filename','$url');";
            $query = mysql_query($query);
            if ($query) {
                return true;
            }
        }
        return false;
    }

    public function toArray() {
        return array('title' => $this->title, 'picture' => $this->picture);
    }

    public static function count() {
        $data = mysql_query('SELECT COUNT(`id`) AS num FROM `products`');
        $row = mysql_fetch_assoc($data);
        return $row['num'];
    }

    public static function getAll() {
        $result = mysql_query("SELECT * FROM `products`");

        $products = array();
        while ($row = mysql_fetch_object($result)) {
            $products[] = $row;
        }
        mysql_free_result($result);
        return $products;
    }

}

Class Amazon {

    private $baseAmazon = 'http://www.amazon.com';
    private $searchUrl = '';
    private $products = array();
    private $next = null;

    public function initlizeNew($url, $keywords) {
        $this->searchUrl = $this->baseAmazon . '/s/?url=' . $url . '&field-keywords=' . urlencode($keywords);
		$this->products = array();
        $this->next = null;
    }

    public function initlizeNext($url) {
        $this->searchUrl = $url;
		$this->products = array();
        $this->next = null;
    }

    public function search() {

        $success = false;
        if ($this->searchUrl != '') {
			grabContent:
            $html = Grabber::curl_grab($this->searchUrl);
            if ($html !== false) {
                $html = str_get_html($html); //Parse HTML

                if (isSet($html)) {
                    $products = $html->find('div[class*=prod]'); //class=newaps for product link&title
                    if (!empty($products)) {
                        foreach ($products as $product) {
                            $newaps = $product->find('.newaps', 0);

                            if (!isSet($newaps)) {//if not grabbed well
                                sleep(2); 
                                goto grabContent; //regrab it
                            }
                            $title = $newaps->plaintext;
                            $url = $newaps->find('a', 0)->href;
							
							$img = $product->find('.productImage', 0)->src;
							
                            $lis = $product->find('ul.rsltGridList li');
                            $techDetails = array();
                            if (!empty($lis)) {
                                $found = false;
                                foreach ($lis as $li) {
                                    if ($li->plaintext == 'Product Details') {
                                        $found = true;
                                    } else if ($found) {
                                        $techDetails[] = $li->innertext;
                                    }
                                }
                            }
                            //echo $title . ' ' . $url . ' ' . $img . ' ' . $techDetails . '<br>';
                            $prod = new Product();
                            if ($prod->initlizeData($title, $img, $techDetails, $url)) {
                                if ($prod->store()) {
                                    $this->products[] = $prod->toArray();
                                }
                            }
                        }
                    }
                    $nextLink = $html->find('#pagnNextLink', 0);
                    if (isSet($nextLink->href)) {
                        $this->next = $this->baseAmazon . $nextLink->href;
						$this->next = html_entity_decode( html_entity_decode($this->next));
                    }else $this->next = null;

                    $success = true;
                }
            }
        }
        return array(
            'success' => $success,
            'products' => $this->products,
            'next' => $this->next
        );
    }

}

?>
