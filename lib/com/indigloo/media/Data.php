<?php


namespace com\indigloo\media {

    class Data {
        
        public $originalName;
        public $storeName;
        public $mime ;
        public $size ;
        public $height;
        public $width ;
        public $bucket ;
        public $id ;
        public $store ;
        public $thumbnail ;
        public $thumbnailName ;

        static function create($row) {
            $view = new \com\indigloo\media\Data();

            $view->originalName = $row["original_name"];
            $view->storeName  = $row["stored_name"];
            $view->mime = $row["mime"];
            $view->size = $row["size"];
            $view->height  = $row["original_height"];
            $view->width  = $row["original_width"];
            $view->bucket = $row["bucket"];
            $view->id = $row["id"];
            $view->store  = $row["store"];
            $view->thumbnail = $row["thumbnail"];
            $view->thumbnailName = $row["thumbnail_name"];

            return $view ;

        }

    }
}
?>
