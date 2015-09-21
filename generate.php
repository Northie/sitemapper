<?php

require_once 'curl.php';

class sitemap {
    
    private $start;
    private $urls = array();
    private $httpClient;
    private $pageCount = 0;
    private $ignoreLiteral = false;
    private $ignorePattern = false;
    
    const PENDING = 1;
    const COMPLETE = 2;
    const IGNORE = 4;
    const ERROR = 8;


    public function __construct($url) {
        $this->start = $url;
        $this->urls[$url] = self::PENDING;
        
        $this->httpClient = new curl;
    }
    
    public function ignoreLiteral($ignore) {
        
        if(!is_array($this->ignoreLiteral)) {
            $this->ignoreLiteral = array();
        }
        
        if(is_array($ignore)) {
            $this->ignoreLiteral = array_merge($this->ignoreLiteral,$ignore);
        } else {
            $this->ignoreLiteral[] = $ignore;
        }
    }
    
    public function ignorePattern($ignore) {
        if(!is_array($this->ignorePattern)) {
            $this->ignorePattern = array();
        }
        
        if(is_array($ignore)) {
            $this->ignorePattern = array_merge($this->ignorePattern,$ignore);
        } else {
            $this->ignorePattern[] = $ignore;
        }
    }


    public function Execute() {
        $this->findLinks();
    }
    
    private function findLinks() {
        
        while($links = $this->getQueue()) {
        
            foreach($links as $url => $crawl) {
                
                if($crawl == self::PENDING) {
                    $this->crawl($url);
                    $this->urls[$url] = self::COMPLETE;
                }
            }
            
            if(count($this->urls) > 1000) {
                print_r($this->urls);
                break;
            }
            
        }
        
        
        
    }
    
    private function crawl($url) {
        
        $this->log("Crawling ".$url." ...");
        /*
        $headers = get_headers($url);
        $t = preg_match("/[1-5][0-9]{2}/", $headers[0],$match);
        
        if($match[0] >= 400) {
            $this->urls[$url] = self::ERROR;
            $this->log("   ... ERROR ".$match[0]);
            return;
        }
        //*/
        set_time_limit(10);
        
        $c = $this->httpClient->get($url);
        
        $doc = new DOMDocument;
        @$doc->loadHTML($c);
        
        $xpath = new DOMXPath($doc);
        
        $links = $xpath->query("//a");
        
        $linkCounter = 0;
        $newLinks = 0;
        
        foreach($links as $link) {
            foreach($link->attributes as $a => $b) {
                if($a == 'href') {
                    $linkCounter++;
                    $link = $b->value;
                    if($this->addLink($link,$url)) {
                        $newLinks++;
                    }
                    break;
                }
            }
        }
        
        $this->log("   ...found ".$linkCounter." Links");
        $this->log("   ...found ".$newLinks." New Links");
        
    }
    
    public function getQueue() {
        $r = array();
        foreach($this->urls as $url => $status) {
            set_time_limit(10);
            if($status == self::PENDING) {
                $r[$url] = $status;
            }
        }
        
        if($r) {
            return $r;
        }
        
        return false;
        
    }
    
    public function addLink($link,$base) {
        
        set_time_limit(10);
        
        switch(true) {
        
            case (strpos($link,"/") === 0):
                $link = rtrim($this->start,"/").$link;
                break;
            case (strpos($link, $this->start) === 0):
                $link = $link;
                break;
            case (strpos($link, "http") === 0 && strpos($link, $this->start) === false):
                //ignore
                $link = false;
                break;
            default:
                $link = $base.$link;
            
        }

        $fragments = explode("#",$link);
        
        $link = $fragments[0];
        
        if($link) {
            
            $ignoreLink = false;
            
            if($this->ignoreLiteral) {
                foreach($this->ignoreLiteral as $ignore) {
                    if(strpos($link,$ignore) > -1 ) {
                        $ignoreLink = true;
                        break;
                    }
                }
            }
            
            if($this->ignorePattern) {
                foreach($this->ignorePattern as $ignore) {
                    if(preg_match($ignore, $link)) {
                        $ignoreLink = true;
                        break;                    
                    }
                }
            }
            
            
            if(!isset($this->urls[$link])) {
                if($ignoreLink) {
                    $this->urls[$link] = self::IGNORE;
                } else {
                    $this->urls[$link] = self::PENDING;
                    return true;
                }
            }
        }
        return false;
    }
    
    public function build() {
        
        $urls = array();
        
        foreach($this->urls as $link => $status) {
            
            set_time_limit(10);
            
            if($status ==  self::COMPLETE) {
                $parts = explode("/",trim(parse_url($link,PHP_URL_PATH),"/"));
                if(trim($parts[0]) == '') {
                    $level = 1;
                } else {
                    $level = count($parts) + 1;
                }
                
                $priority = round((1/$level),4);
                
                $urls[] = array(
                    'loc'=>$link,
                    'lastmod'=>date('Y-m-d H:i:s'),
                    'changefreq'=>'daily',
                    'priority'=> $priority
                );
                
            }
        }
        
        print_r($urls);
    }
    
    private function log($msg) {
        echo "<pre>".$msg."</pre>";
        flush();
        ob_flush();
        flush();
        ob_flush();
    }
}

$sitemap = new sitemap('http://example.com/');

$sitemap->ignoreLiteral(".pdf");
$sitemap->ignoreLiteral("mailto");
$sitemap->ignoreLiteral("javascript");
$sitemap->ignoreLiteral("()");

$sitemap->Execute();
$sitemap->build();
