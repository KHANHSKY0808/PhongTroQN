<?php

class HinhAnh
{
    public function add($postID, $url)
    {
        $sql = "INSERT INTO hinhanh (PostID, URLAnh) VALUES (?, ?)";
        return query($sql, [$postID, $url]);
    }
}
