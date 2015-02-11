<?php
namespace PMP;

class FileManager
{
    private $dir;
    private $files;

    function __construct($dir=null)
    {
        $this->dir = $dir;
        $this->files = array();
    }

    /**
     * @param string $dir
     */
    public function setDir($dir)
    {
        $this->dir = $dir;
    }

    /**
     * @return string
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * @param $path
     * @return int
     */
    public function setFilesForExtentionImages($path){
        return $this->setFilesForExtention($path,array('jpeg','jpg','png','gif'));
    }

    /**
     * @param $path
     * @return null|string
     */
    private function getDirFilePath($path){
        $p = $this->dir;
        if($p && (substr($p,strlen($p) - 1,1) != '/') && $path){
            $p .= '/';
        }
        $p .= $path;
        return $p;
    }

    /**
     * @param $path
     * @param null $ext
     * @return int
     */
    public function setFilesForExtention($path,$ext=null){
        $extensions = null;
        if(is_array($ext)){
            $extensions = $ext;
        }else if($ext){
            $extensions = array($ext);
        }
        $path = $this->getDirFilePath($path);
        if($extensions){
            $files = array();
            foreach($extensions as $e){
                $p = null;
                if(file_exists($path.".".strtolower($e))){
                    $files[] = $path.".".strtolower($e);
                }else if(file_exists($path.".".strtoupper($e))){
                    $files[] = $path.".".strtoupper($e);
                }
                if($p){
                    $files[$p] = $p;
                    $this->files[$p] = $p;
                }
            }
            return count($files);
        }
        $files = glob($path.".*");
        foreach($files as $p){
            $this->files[$p] = $p;
        }
        return count($files);
    }

    /**
     * @param $dir
     * @param int $perm
     * @param bool $recursive
     * @return bool
     */
    public function mkDirPath($dir,$perm=0777,$recursive=false)
    {
        return self::mkDir($this->getDirFilePath($dir));
    }

    /**
     * @param $dir
     * @param int $perm
     * @param bool $recursive
     * @return bool
     */
    static public function mkDir($dir,$perm=0777,$recursive=false)
    {
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
    public function copyPath($source,$dest,$rewrite=true){
        $source = $this->getDirFilePath($source);
        $dest = $this->getDirFilePath($dest);
        return self::copy($source,$dest,$rewrite);
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
     * @param $path
     * @param $callback
     * @return bool
     */
    public function callFiles($path,$callback)
    {
        $path = $this->getDirFilePath($path);
        return $this->callbackDir($path,$callback);
    }

    /**
     * @param $filename
     * @param $callback
     * @param bool $child
     * @return bool
     */
    private function callbackDir($filename,$callback,$child=false)
    {
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
                if($this->callbackDir($path,$callback)){
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
     * @return int
     */
    public function loadFiles($filename)
    {
        return $this->load($this->getDirFilePath($filename));
    }

    /**
     * @param $filename
     * @return int
     */
    private function load($filename)
    {
        if(is_dir($filename)){
            $dh = opendir($filename);
            while ($file = readdir($dh)) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $path = $filename . "/" . $file;
                $this->files[$path] = $path;
                $this->load($path);
            }
            closedir($dh);
        }else if(file_exists($filename)){
            $this->files[$filename] = $filename;
        }
        return count($this->files);
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }
}