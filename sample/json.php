#!/usr/bin/php
<?php
ini_set('date.timezone', 'America/New_York');
include dirname(__FILE__) . '/../../profiler/src/Codon/Profiler.php';
include dirname(__FILE__) . '/../src/Codon/Config.php';

$json = json_decode('{ "global" : { "environment" : "dev", "Base.Hostname" : "liveclips.net", "Orion.Root" : "/opt/odrt_video", "Orion.LogsPath" : "/opt/odrt_video/logs", "Orion.ArchiveBasePath" : "/data2/archive", "Orion.ScriptsPath" : "/opt/odrt_video/scripts/perl" }, "php" : { "error_reporting" : "E_WARNING", "ini_set" : { "display_errors" : "on" } }, "app_mom" : { "baseURL" : "http://mom.liveclips.net", "prefix" : "app_mom_", "debug" : "1", "Cache.disable" : false, "Site.Cookie.Name" : "app_mom", "mysql" : { "host" : "localhost", "login" : "root", "password" : "sp0rt.333", "database" : "data_dictionary" } }, "app_vma" : { }, "app_shared" : { "baseURL": "http://shared.liveclips.net" }, "app_streaming": { "baseURL" : "http://admin.liveclips.net", "servers": { "orion": "${app_streaming.baseURL}", "stream_001": "stream-001.liveclips.net:1935" }, "formats": ["flv"], "streams": { "live": { "app": "live", "name": "orb${ORB_NUM}ch${CHANNEL_NUM}.stream", "flv": { "protocol": "rtmp", "suffix": "", "template": "flowplayer.tpl" } }, "dvr": { "_": "The DVR appplication", "app": "live", "name": "orb${ORB_NUM}ch${CHANNEL_NUM}.stream", "flv": { "protocol": "http", "suffix": "manifest.f4m?DVR", "template": "flowplayer.tpl" } }, "vod": { }, "file" : { "_": "This application is for playing files from the localhost. App points to preview folder", "app": "preview", "name": "", "flv": { "protocol": "http", "suffix" : "", "template": "flowplayer.tpl" } } } }, "app_admin_legacy" : { "baseURL" : "http://admin.liveclips.net", "prefix" : "app_zadmin_", "debug" : 0, "Site.Cookie.Name" : "app_zadmin", "mysql" : { "host" : "localhost", "login" : "odrt_mysql", "password" : "light4428", "database" : "odrt_video" }, "Server.POE" : { "ip" : "127.0.0.1", "port" : 3308, "password" : "odrt" } }, "memcache" : { "prefix" : "", "servers" : ["127.0.0.1:11211"] } }');

$instance = \Codon\Config::i($json);

$profiler = new \Codon\Profiler();
$profiler
    ->set('showOutput', true)
    ->add([
        'name' => 'Config_Get',
        'iterations' => 100,
        'function' => function() use (&$instance, &$profiler) { 
        
            $profiler->startTimer('Recursive');
            $value = $instance->get('app_streaming', 'streams', 'live');
            $profiler->endTimer('Recursive');
        }    
    ])
    ->add([
        'name' => 'Nothing',
        'iterations' => 1,
        'function' => function() use (&$profiler) {
            
           
        }
    ]);
    
$profiler->run()->showResults();