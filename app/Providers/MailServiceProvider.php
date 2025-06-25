<?php

namespace App\Providers;

use Illuminate\Mail\MailServiceProvider as BaseMailServiceProvider;
use Illuminate\Support\Facades\Log;
use Swift_Plugins_LoggerPlugin;
use Swift_Plugins_Logger;

class MailServiceProvider extends BaseMailServiceProvider
{
    public function register()
    {
        parent::register();

        $this->app->singleton('swift.plugins', function () {
            return [
                new Swift_Plugins_LoggerPlugin(
                    new class implements Swift_Plugins_Logger {
                        public function add($entry)
                        {
                            Log::channel('mail')->debug($entry);
                        }

                        public function clear()
                        {
                            // Not needed
                        }

                        public function dump()
                        {
                            // Not needed
                        }
                    }
                ),
            ];
        });
    }
} 