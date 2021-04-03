<?php

// namespace Classes;
class File{
    protected $folder ="./files/";
    
    public function create($file, $text = null)
    {
        file_put_contents($this->folder . $file, $text);
    }
}