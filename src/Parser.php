<?php
namespace FileExport;

class Parser {

    public static function toXMind ( $source, $baseDir) {
        return ParserXMind::parse( htmlspecialchars( $source, ENT_NOQUOTES ), $baseDir);
    }

    public static function toFreeMind ( $source, $baseDir) {
        return ParserFreeMind::parse( htmlspecialchars( $source, ENT_NOQUOTES ), $baseDir);
    }

}