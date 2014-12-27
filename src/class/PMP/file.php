<?php
namespace PMP;

/**
 * Class File
 * @package PMP
 */
class File{
    // MIME Type
    static $MIME_TYPE = array(
        // text/***
        'txt' => 'text/plain',
        'csv' => 'text/csv',
        'tsv' => 'text/tab-separated-values',
        'doc' => 'application/msword',
        'docx' => 'application/msword',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pdf' => 'application/pdf',
        'xdw' => 'application/vnd.fujixerox.docuworks',
        'htm' => 'text/html',
        'html' => 'text/html',
        'css' => 'text/css',
        'js' => 'text/javascript',
        'hdml' => 'text/x-hdml',
        // image/***
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        //'jpeg' => 'image/pjpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ai' => 'application/postscript',
        // audio/***
        'mp3' => 'audio/mpeg',
        'mp4' => 'audio/mp4',
        'wav' => 'audio//x-wav',
        'mid' => 'audio/midi',
        'midi' => 'audio/midi',
        'mmf' => 'application/x-smaf',
        // movie
        'mpg' => 'video/mpeg',
        'mpeg' => 'video/mpeg',
        'wmv' => 'video/x-ms-wmv',
        'swf' => 'application/x-shockwave-flash',
        '3g2' => 'video/3gpp2',
        // application/***
        'zip' => 'application/zip',
        'lha' => 'application/x-lzh',
        'lzh' => 'application/x-lzh',
        'tar' => 'application/x-tar',
        'tgz' => 'application/x-tar',
        // other
        //'tar' => 'application/octet-stream',
        //'tgz' => 'application/octet-stream',
    );
    const PATHINFO_DIRNAME = 1;
    const PATHINFO_BASENAME = 2;
    const PATHINFO_EXTENSION = 4;
    const PATHINFO_FILENAME = 8;

    private $files;

    function __construct()
    {
        $this->files = array();
    }

    /**
     * @param $ext
     * @param string $default
     * @return string
     */
    static public function getMIMEtoExtention($ext,$default=''){
        $ext = strtolower($ext);
        if(isset(self::$MIME_TYPE[$ext])){
            return self::$MIME_TYPE[$ext];
        }
        if($default){
            return $default;
        }
        return 'application/octet-stream';
    }

    /**
     * @param $filename
     * @return string
     */
    static public function getPerms($filename)
    {
        $perms = fileperms($filename);
        if (($perms & 0xC000) == 0xC000) {
            // ソケット
            $info = 's';
        } elseif (($perms & 0xA000) == 0xA000) {
            // シンボリックリンク
            $info = 'l';
        } elseif (($perms & 0x8000) == 0x8000) {
            // 通常のファイル
            $info = '-';
        } elseif (($perms & 0x6000) == 0x6000) {
            // ブロックスペシャルファイル
            $info = 'b';
        } elseif (($perms & 0x4000) == 0x4000) {
            // ディレクトリ
            $info = 'd';
        } elseif (($perms & 0x2000) == 0x2000) {
            // キャラクタスペシャルファイル
            $info = 'c';
        } elseif (($perms & 0x1000) == 0x1000) {
            // FIFO パイプ
            $info = 'p';
        } else {
            // 不明
            $info = 'u';
        }

        // 所有者
        $info .= (($perms & 0x0100) ? 'r' : '-');
        $info .= (($perms & 0x0080) ? 'w' : '-');
        $info .= (($perms & 0x0040) ?
            (($perms & 0x0800) ? 's' : 'x') :
            (($perms & 0x0800) ? 'S' : '-'));
        // グループ
        $info .= (($perms & 0x0020) ? 'r' : '-');
        $info .= (($perms & 0x0010) ? 'w' : '-');
        $info .= (($perms & 0x0008) ?
            (($perms & 0x0400) ? 's' : 'x') :
            (($perms & 0x0400) ? 'S' : '-'));
        // 全体
        $info .= (($perms & 0x0004) ? 'r' : '-');
        $info .= (($perms & 0x0002) ? 'w' : '-');
        $info .= (($perms & 0x0001) ?
            (($perms & 0x0200) ? 't' : 'x') :
            (($perms & 0x0200) ? 'T' : '-'));
        return $info;
    }

    /**
     * @param $path
     * @param $option
     * @return string
     */
    static public function pathinfo($path,$option){
        $path_parts = pathinfo($path);
        if($option == self::PATHINFO_DIRNAME){
            if(isset($path_parts["dirname"])){
                return $path_parts["dirname"];
            }
        }else if($option == self::PATHINFO_BASENAME){
            if(isset($path_parts["basename"])){
                return $path_parts["basename"];
            }
        }else if($option == self::PATHINFO_EXTENSION){
            if(isset($path_parts["extension"])){
                return $path_parts["extension"];
            }
        }else if($option == self::PATHINFO_FILENAME){
            if(defined('PATHINFO_FILENAME')){
                if(isset($path_parts["filename"])){
                    return $path_parts["filename"];
                }
            }else if(isset($path_parts["basename"])){
                if(strstr($path_parts["basename"], '.')){
                    return substr($path_parts["basename"],0,strrpos($path_parts["basename"],'.'));
                }
            }
        }
        return "";
    }

    /**
     * @param $path
     * @return array
     */
    static public function searchImages($path){
        return self::searchExtention($path,array("jpeg","jpg","png","gif"));
    }

    /**
     * @param $path
     * @param null $ext
     */
    static public function searchExtention($path,$ext=null){
        if(is_array($ext)){
            $files = array();
            foreach($ext as $e){
                if(file_exists($path.".".strtolower($e))){
                    $files[] = $path.".".strtolower($e);
                }
                if(file_exists($path.".".strtoupper($e))){
                    $files[] = $path.".".strtoupper($e);
                }
            }
            return $files;
        }
        $filename = $path.".*";
        return glob($filename);
    }

    /**
     * @param $dir
     * @param int $perm
     * @param bool $recursive
     * @return bool
     */
    static public function mkDir($dir,$perm=0777,$recursive=false){
        if(!is_dir($dir)){
            mkdir($dir,$perm,$recursive);
            return true;
        }
        return false;
    }

    /**
     * @param $source
     * @param $dest
     * @param bool $rewrite
     * @return bool
     */
    static public function copy($source,$dest,$rewrite=true){
        if(!$rewrite){
            if(@file_exists($source)){
                return false;
            }
        }
        if(@copy($source,$dest)){
            return true;
        }
        return false;
    }

    /**
     * @param $filename
     * @param $callback
     * @param bool $child
     * @return bool
     */
    static public function callbackDir($filename,$callback,$child=false){
        if(is_dir($filename)){
            if($callback($filename)){
                return true;
            }
            $files = array();
            $dh = opendir($filename);
            while ($file = readdir($dh)) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $path = $filename . "/" . $file;
                if(self::callbackDir($path,$callback)){
                    return true;
                }
            }
            closedir($dh);
        }else{
            if($callback($filename)){
                return true;
            }
        }
        return false;
    }

    /**
     * @param $filename
     * @return bool
     */
    static public function delete($filename){
        if(is_string($filename)){
            if(is_dir($filename)){
                self::deleteDir($filename,true);
            }else if(is_file($filename)){
                @unlink($filename);
            }
        }else if(is_array($filename)){
            foreach($filename as $file){
                self::delete($file);
            }
        }
        return true;
    }

    /**
     * @param $filename
     * @param bool $delete
     * @return bool
     */
    static public function deleteDir($filename, $delete = true)
    {
        if(is_dir($filename)){
            $dh = opendir($filename);
            while ($file = readdir($dh)) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $path = $filename . "/" . $file;
                self::delete($path);
            }
            if ($delete) {
                @rmdir($filename);
            }
            closedir($dh);
            return true;
        }
        return false;
    }

    /**
     * @param $filename
     */
    public function load($filename)
    {
        if(is_dir($filename)){
            $dh = opendir($filename);
            while ($file = readdir($dh)) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $path = $filename . "/" . $file;
                $this->files[] = $path;
                $this->load($path);
            }
            closedir($dh);
        }else if(file_exists($filename)){
            $this->files[] = $filename;
        }
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }
}