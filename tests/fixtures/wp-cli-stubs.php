<?php

namespace WP_CLI
{
    
    trait FooTrait
    {
    
    }
    
    class Autoloader
    {
        
        public function foo()
        {
        }
        
    }
    
    function foo_func()
    {
    }
    
    const BAZ = 'BIZ';
    
    define('FOO', 'BAR');
    
    const A = 'B';
}

namespace WP_CLI\Utils
{
    
    function wp_not_installed()
    {
    }
    
    const BAM = 'boom';
}

namespace WP_CLI\Bootstrap
{
    
    interface BootstrapInterface
    {
    
    }
    
    trait BarTrait
    {
    
    }
    
    abstract class AutoloaderStep
    {
    
    }
}
