<?php
use PMP\Command;
use PMP\CommandAction;
use PMP\CommandShell;

// sample command
$command_action = (new CommandAction());
$command_action
    ->setCallback(function(CommandAction $command){
        global $db;
        //if(Command::checkCallOption('d')){
        $change_column_num = $db->upgradeManagementModel();
        if($change_column_num > 0){
            $command->outputLine('success upgrade management model. change `'.$change_column_num."`");
        }else{
            $command->outputLine('no upgrade management model.');
        }
        return true;
        //}else{
        //Command::setHelp('please set option -d.');
        //}
        //return false;
    })->setDescription('migration database');
Command::addAction('update',$command_action);

$command_action = (new CommandAction());
$command_action
    ->addShell((new CommandShell('directory name[YYYYmmddHHiiss] : ',function($input){ return true; })))
    ->setCallback(function(CommandAction $command){
        global $db;

        $dirname = \PMP\Application::getWebPath('/backup');
        if(!is_dir($dirname)){
            if(!mkdir($dirname)){
                $command->exitLine('can not make dir backup directory '.$dirname.'.');
            }
        }
        $file = $command->getShell(0)->getResult();
        if(!$file){
            $file = date('Ymdhis');
        }
        $backup_filename = $dirname.'/'.$file;
        if(!is_dir($backup_filename)){
            if(!mkdir($backup_filename)){
                $command->exitLine('can not make dir backup directory '.$backup_filename.'.');
            }
        }else{
            $command->exitLine('directory is exists '.$backup_filename.'.');
        }

        // DB保存
        $filename = $backup_filename."/db.sql";
        $shell_text = sprintf(
            "/usr/local/bin/mysqldump --default-character-set=binary %s --host=%s --user=%s --password=%s  > \"%s\" ",
            $db->getDbName(),$db->getHost(),$db->getUsername(),$db->getPassword(),$command->escape($filename));
        // バッファ
        $command->outputLine('command : '.$shell_text);
        $command->exec($shell_text,$output);
        $command->outputLine('result : '.implode("\n",$output));

        // ファイル圧縮
        $current = getcwd();

        $filename = $backup_filename."/uploads.tar.gz";
        $from = "uploads";
        if(chdir(\PMP\Application::getWebPath())){

            $shell_text = sprintf(
                "tar cvzf %s %s",
                $command->escape($filename),$command->escape($from));
            // バッファ
            $command->outputLine('command : '.$shell_text);
            exec($shell_text,$output);
            $command->outputLine('result : '.implode("\n",$output));

            chdir($current);
        }else{
            $command->outputLine('failure current directory change.');
        }

        $command->outputLine('success upgrade management model.');
        return true;
    })->setDescription('backup database');
Command::addAction('backup',$command_action);

$command_action = (new CommandAction());
$command_action
    ->addShell((new CommandShell('input database name : ')))
    ->addShell((new CommandShell('input database user : ')))
    ->addShell((new CommandShell('input database password : ')))
    ->setCallback(function(CommandAction $command){
        $command->outputLine('this function is not under development.');
        return true;
    })->setDescription('database install table.');
Command::addAction('install',$command_action);

$command_action = (new CommandAction());
$command_action
    ->setCallback(function(CommandAction $command){
        set_time_limit(0);

        $php_path = 'php';
        $root = \PMP\Application::getRootDir();
        $router_path = \PMP\Application::getRootDir('/app/core/router.php');
        $host_name = $command->getCallParam(0);

        $cm = sprintf('%s %s -t %s %s',$php_path,($host_name ? '-S '.$host_name : ''),$root,$router_path);
        $command->outputLine($cm);
        exec($cm);
        return true;
    })->setDescription('run server.(run localhost:1000)');
Command::addAction('run',$command_action);

$command_action = (new CommandAction());
$command_action
    ->setCallback(function(CommandAction $command){
        $cmlist = array();
        foreach(Command::getActions() as $key => $com){
            $cmlist[] = $key.' - '.$com->getDescription();
        }
        $command->outputLine(implode("\r\n",$cmlist));
        return true;
    })->setDescription('action list.');
Command::addAction('list',$command_action);

$command_action = (new CommandAction());
$command_action
    ->setCallback(function(CommandAction $command){
        $command->outputLine('Please wait minute.Next version update.');
        return true;
    })->setDescription('install PMP sample project.');
Command::addAction('pmp',$command_action);
