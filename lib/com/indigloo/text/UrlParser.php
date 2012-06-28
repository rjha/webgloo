<?php
namespace com\indigloo\text{
    
    use com\indigloo\Logger;
    use com\indigloo\Util;
    use com\indigloo\Configuration as Config;
    
    class UrlParser {
        
        /*
         * copied from http://www.geekality.net/2011/05/12/php-dealing-with-absolute-and-relative-urls/ 
         * @see also http://publicmind.in/blog/urltoabsolute/ 
         *
         */
    
        function createAbsoluteUrl($url, $base) {
            //check input 
            if( ! $url) return NULL;
            if(parse_url($url, PHP_URL_SCHEME) != '') return $url;
            
            // Urls only containing query or anchor
            if($url[0] == '#' || $url[0] == '?') return $base.$url;
            
            // Parse base URL and convert to local variables: $scheme, $host, $path
            extract(parse_url($base));

            // If no path, use /
            if( ! isset($path)) $path = '/';
         
            // Remove non-directory element from path
            $path = preg_replace('#/[^/]*$#', '', $path);
         
            // Destroy path if relative url points to root
            if($url[0] == '/') $path = '';
        
            // Dirty absolute URL
            $abs = "$host$path/$url";
         
            // Replace '//' or '/./' or '/foo/../' with '/'
            $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
            for($n = 1; $n > 0; $abs = preg_replace($re, '/', $abs, -1, $n)) {}
            
            // Absolute URL is ready!
            return $scheme.'://'.$abs;
        }

        function addScheme($url){
            $scheme = \parse_url($url,PHP_URL_SCHEME);
            if(empty($scheme)) {
                $url = "http://".$url ;
            } 

            return $url ;
        }

        function extractUsingDom($url) {

            if(empty($url)) { return ; }
            $url = $this->addScheme($url);

            $title = "" ;
            $description = "" ;

            $html = @file_get_contents($url);
            $doc = new \DOMDocument();
            @$doc->loadHTML($html);

            $nodes = $doc->getElementsByTagName("title");
            $length = $nodes->length ;
            if($length > 0 ){ 
                $title = $nodes->item(0)->nodeValue;
            }

            $metas = $doc->getElementsByTagName("meta");
            $length = $metas->length ;
            for ($i = 0; $i < $length; $i++) {
                $meta = $metas->item($i);
                if($meta->getAttribute("name") == "description") {
                    $description = $meta->getAttribute("content");
                }
            }

            $nodes = $doc->getElementsByTagName("img");

            $srcImages = array();
            $length = $nodes->length ;

            for($i = 0 ; ($i < $length && $i < 10); $i++) {
                $node = $nodes->item($i);
                array_push($srcImages,$node->getAttribute("src"));
            }

             $images = array();

            //create absolute urls
            foreach($srcImages as $srcImage) {
                $absUrl = $this->createAbsoluteUrl($srcImage,$url);
                if(!is_null($absUrl)) {
                    array_push($images,$absUrl);
                }
            }

            $response = new \stdClass;
            $response->title = $title ;
            $response->description = $description ;
            $response->images = $images ;
            return $response ;


        }

        /*
         * Given a choice between quick & Dirty vs. correct, always do the
         * quick (dirty!) thing in extract function. We want this to be quick
         * so do not do DOM parsing or try to do proper word breaking etc!
         * 
         */
        
        function extract($url) {
            // clean the Url 
            // last slash is not required 
            // figure out relative vs. full URL here
            // file_get_contents will fail for something like www.3mik.com
            // scheme is required

            if(empty($url)) { return ; }
            $url = $this->addScheme($url);

            $title = "" ;
            $description = "" ;

            $html = file_get_contents($url);
            
            $regex = "/<title>(.+)<\/title>/i";
            preg_match($regex, $html, $matches);
            $title = $matches[1];
            
            $tags = get_meta_tags($url);
            
            if(!empty($tags) && array_key_exists("description",$tags)) {
                $description = $tags["description"];
            }
            
            if(empty($description)) {
                /*
                 * forget all the cute heuristics  - does not add anything!
                 * The only sane way would be to run such horrible messes through
                 * an actual renderer like webkit.
                 * @try parsing http://48etikay.com for fun!
                 * 
                 */
                
            }
            
            $srcImages = array();
            
            // fetch images
            $regex = '/<img[^>]*'.'src=[\"|\'](.*)[\"|\']/Ui';
            preg_match_all($regex, $html, $matches, PREG_PATTERN_ORDER);
            $srcImages = $matches[1];
            
            if(sizeof($srcImages) > 10 ) {
                $srcImages = array_splice($srcImages,0,10);
            }
            

            $images = array();

            //create absolute urls
            foreach($srcImages as $srcImage) {
                $absUrl = $this->createAbsoluteUrl($srcImage,$url);
                if(!is_null($absUrl)) {
                    array_push($images,$absUrl);
                }
            }

            $response = new \stdClass;
            $response->title = $title ;
            $response->description = $description ;
            $response->images = $images ;
            return $response ;
        }
       
    }
}
?>
