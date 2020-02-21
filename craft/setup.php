<?php
echo "Install Helper\n";

function askWithDefault($prompt, $default){
    $line = trim(readline($prompt.' ('.$default.'): '));
    return empty($line) ? $default : $line;
}
function ask($prompt, $allowNull = false){
    $answer = null;
    if($allowNull){
        return trim(readline($prompt.': '));
    }
    while(empty($answer)){
        $answer = trim(readline($prompt.': '));
    }
    return $answer;
}
function camelCase($string, $dontStrip = []){
    return lcfirst(str_replace(' ', '', ucwords(preg_replace('/^a-z0-9'.implode('',$dontStrip).']+/', ' ',$string))));
}
function snakeCase($string, $dontStrip = []){
    return lcfirst(str_replace(' ', '-', strtolower(preg_replace('/^a-z0-9'.implode('',$dontStrip).']+/', ' ',$string))));
}
function makeDirectories($path, $basePath){
    $path_parts = pathinfo($path);
    if($path_parts['dirname'] !== '.'){
        $dirs = explode(DIRECTORY_SEPARATOR, $path_parts['dirname']);
        do{
            $dir = array_shift($dirs);
            $basePath .= $dir.DIRECTORY_SEPARATOR;
            if(!is_dir($basePath)){
                mkdir($basePath);
            }
        }while(count($dirs) > 0);
    }
}

$vendorName = askWithDefault('Vendor Name', 'ecce');
$pluginName = ask('Package Name e.g. Hello World');
$description = ask('Package Description', true);

$composerName = camelCase($vendorName).'/'.camelCase($pluginName);
$craftHandle =  snakeCase($pluginName);
$namespace = camelCase($vendorName).'\\'.camelCase($pluginName);
$className = ucfirst(camelCase($pluginName));

$rootPath = realpath(__DIR__.'/../');
$basePath = $rootPath.'/';
$stubDir = $rootPath.'/stubs/';

$replacements = [
    'composer.stub'=>[
        'data'=>[
            'name'=>$composerName,
            'description'=>$description,
            'namespace'=>addslashes($namespace),
            'craftName'=>$pluginName,
            'craftHandle'=>$craftHandle,
            'className'=>$className
        ],
        'path'=>'composer.json'
    ],
    'docker-compose.stub'=>[
        'data'=>[
            'name'=>$composerName
        ],
        'path'=>'docker-compose.yaml'
    ],
    'plugin.stub' => [
        'data'=>[
            'name'=>$pluginName,
            'description'=>$description,
            'vendor'=>$vendorName,
            'namespace'=>$namespace,
            'handle'=>$craftHandle,
            'className'=>$className,
        ],
        'path'=>'src/'.$className.'.php'
    ],
    'translation.stub'=>[
        'data'=>[
            'name'=>$pluginName,
            'description'=>$description,
            'vendor'=>$vendorName,
            'className'=>$className,
        ],
        'path'=>'src/translations/en/'.$craftHandle.'.php'
    ],
    'changelog.stub'=>[
        'data'=>[
            'name'=>$pluginName
        ],
        'path'=>'CHANGELOG.md'
    ],
    'readme.stub'=>[
        'data'=>[
            'name'=>$pluginName,
            'description'=>$description,
            'composerName'=>$composerName
        ],
        'path'=>'README.md'
    ],
    'license.stub'=>[
        'data'=>[],
        'path'=>'LICENSE.md'
    ],
    'env.stub'=>[
        'data'=>[],
        'path'=>'.env'
    ]
];

foreach($replacements as $stubFile=>$replacement){
    $contents = file_get_contents($stubDir.$stubFile);
    foreach($replacement['data'] as $key=>$value){
        $contents = str_replace('{{'.$key.'}}',$value, $contents);
    }
    makeDirectories($replacement['path'],$basePath);
    file_put_contents($basePath.$replacement['path'],$contents);
}

