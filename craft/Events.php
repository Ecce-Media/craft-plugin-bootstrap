<?php
namespace Setup;

use Composer\Script\Event;

class Events
{
    public static function postCreateProject(Event $event)
    {
        echo "Craft Plugin Helper Setup\n";
        $vendorName = $event->getIO()->ask('Vendor Name (ecce-media): ', 'ecce-media');
        $pluginName = $event->getIO()->ask('Package Name (Plugin): ','Plugin');
        $description = $event->getIO()->ask('Package Description: ', '');
        $craftVersion = $event->getIO()->ask('Craft Version (latest): ', 'latest');

        $composerName = self::snakeCase($vendorName).'/'.self::snakeCase($pluginName);
        $craftHandle =  self::snakeCase($pluginName);
        $namespace = ucfirst(self::camelCase($vendorName)).'\\'.ucfirst(self::camelCase($pluginName));
        $className = ucfirst(self::camelCase($pluginName));

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
                    'tag'=>$craftVersion,
                    'name'=>$composerName,
                    'dbVolume'=>str_replace('/','',$composerName).'-db'
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
                'path'=>['.env','.env.example']
            ],
        ];

        foreach($replacements as $stubFile=>$replacement){
            $contents = file_get_contents($stubDir.$stubFile);
            foreach($replacement['data'] as $key=>$value){
                $contents = str_replace('{{'.$key.'}}',$value, $contents);
            }
            if(is_string($replacement['path'])){
                $replacement['path'] = [$replacement['path']];
            }
            foreach($replacement['path'] as $path){
                self::makeDirectories($path,$basePath);
                file_put_contents($basePath.$path,$contents);
            }
        }

        self::removeDirectory($stubDir);
        unlink(__FILE__);
    }


    protected static function camelCase($string, $dontStrip = []){
        return lcfirst(str_replace(' ', '', ucfirst(preg_replace('/[^a-z0-9\w'.implode('',$dontStrip).']+/i', ' ',$string))));
    }
    protected static function snakeCase($string, $dontStrip = []){
        return lcfirst(str_replace(' ', '-', strtolower(preg_replace('/[^a-z0-9\w'.implode('',$dontStrip).']+/i', ' ',$string))));
    }
    protected static function makeDirectories($path, $basePath){
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

    protected static function removeDirectory($path) {
        $files = glob($path . '/*');
        foreach ($files as $file) {
            is_dir($file) ? self::removeDirectory($file) : unlink($file);
        }
        rmdir($path);
        return;
    }
}

