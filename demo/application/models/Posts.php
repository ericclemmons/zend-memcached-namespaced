<?php

use Posts\Post;

class Posts
{
    
    public static function findAll()
    {
        return array(
            new Post('foo'),
            new Post('bar'),
            new Post('baz')
        );
    }
}