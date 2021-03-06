<?php

namespace Metaclassing\EnterpriseAuth\Console;

use Illuminate\Console\Command;

class GenerateUIPreload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'enterpriseauth:uipreload {--destination=*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate UI auth preload javascript';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $path = $this->getPath();
        $this->generatePreloadJS($path);
        $this->info('Generated UI auth preload javascript to '.$path);
    }

    public function getPath()
    {
        // start in APPDIR/public
        $base = base_path('public');
        // see if the user gave us a specific path
        $path = $this->option('destination');
        $path = reset($path);
        // otherwise use the default path
        if (! $path) {
            $path = 'ui/preload.js';
        }
        // fully calculated path is APPDIR/public/ui/preload.js
        return $base.'/'.$path;
    }

    public function generatePreloadJS($path)
    {
        $app_url = config('app.url');
        $client_id = config('enterpriseauth.credentials.client_id');
        $callback_uri = config('enterpriseauth.credentials.callback_url');

        $msauthjs = file_get_contents(__DIR__.'/msauth.js');

        // generate javascript file contents
        $contents = <<<EOF
console.log('inside preload.js');

// app url
var globalUrl = "$app_url";

// list of scopes we need to request a token for
var APIScopes = [
    // IF we are getting an access token for ourselves, just send the client id
    "$client_id"
    // IF we are getting an access token for ANOTHER app id, it needs to look like this
    //"api://$client_id/.default"
];

// client id and redirect uri for logging people in
var msalconfig = {
    clientID: "$client_id",
    redirectUri: "$callback_uri",
    cacheLocation: "localStorage"
};

$msauthjs
EOF;
        // finally write the file out
        file_put_contents($path, $contents);
    }
}
