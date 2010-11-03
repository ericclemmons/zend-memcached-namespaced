<?php

namespace Posts;

class Post
{
    
    protected $title;
    
    public function __construct($title)
    {
        $this->title = $title;
    }
    
    public function __toString()
    {
        return $this->title;
    }
    
}