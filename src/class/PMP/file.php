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

    /**
     * @param $ext
     * @param string $default
     * @return string
     */
    static public function getMIMEtoExtention($ext,$default='application/octet-stream'){
        $ext = strtolower($ext);
        if(isset(self::$MIME_TYPE[$ext])){
            return self::$MIME_TYPE[$ext];
        }
        return $default;
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

    private $filename;
    private $mode;
    private $fp;
    private $contents;

    /**
     * @param $message
     * @throws \Exception
     */
    private function error($message)
    {
        throw new \Exception($message);
    }

    function __construct($filename=null,$mode='a+')
    {
        if($filename){
            $this->open($filename,$mode);
        }
    }

    /**
     * @param $filename
     * @param string $mode
     * @return bool
     */
    public function open($filename,$mode='a+')
    {
        if(!$mode){
            $this->error('must be paramater "mode".');
        }
        if($this->fp = @fopen($filename,$mode)){
            $this->mode = $mode;
            $this->contents = '';
            if(flock($this->fp,LOCK_EX)){
                return true;
            }else{
                $this->fp = null;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function read()
    {
        if($this->fp){
            while (!feof($this->fp)) {
                $this->contents .= fread($this->fp,1024);
            }
        }else{
            $this->error(sprintf('not open file.(filename is %s)',$this->filename));
        }
        return $this->contents;
    }

    /**
     * @return mixed
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * @param $message
     */
    public function addLine($message)
    {
        $this->add($message.PHP_EOL);
    }

    /**
     * @param $message
     */
    public function add($message)
    {
        if($this->fp){
            fwrite($this->fp,$message);
        }else{
            $this->error(sprintf('not open file.(filename is %s)',$this->filename));
        }
    }

    /**
     * @param $offset
     * @param int $where
     */
    public function seek($offset,$where=SEEK_SET)
    {
        if($this->fp){
            fseek($this->fp,$offset,$where);
        }else{
            $this->error(sprintf('not open file.(filename is %s)',$this->filename));
        }
    }

    /**
     *
     */
    public function close()
    {
        if($this->fp){
            flock($this->fp,LOCK_UN);
            fclose($this->fp);
            $this->fp = null;
            $this->mode = null;
        }
    }

    /**
     * @param callable $output_callable
     * @return bool
     */
    public function ob_start(callable $output_callable=null)
    {
        if(ob_start($output_callable)){
            return true;
        }
        return false;
    }

    /**
     * @param bool $output
     */
    public function ob_end($output=false)
    {
        $this->add(ob_get_contents());
        if($output){
            ob_end_flush();
        }else{
            ob_end_clean();
        }
    }
}