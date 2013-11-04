<?php

namespace Youtube;

use Oil\Exception;

class Utils
{
    public static function request($url){
        try{
            $xml = simplexml_load_file($url);
            return self::xml2array($xml);
        }catch(\Exception $e){
            \Log::error($e->getMessage(), __METHOD__);
            return false;
        }
    }

    public static function xml2array ( $xmlObject, $out = array () ){
        foreach ( (array) $xmlObject as $index => $node ){
            $index = is_string( $index ) ? str_replace( '@', '', $index ) : $index ;
            $out[$index] = ( is_object ( $node ) ||  is_array ( $node ) ) ? self::xml2array ( $node ) : $node;
        }
        return $out;
    }

    public static function array2Object( $array ){
        foreach( $array as $key => $value ){
            if( is_array( $value ) ) $array[ $key ] = self::array2Object( $value );
        }
        return (object) $array;
    }
}